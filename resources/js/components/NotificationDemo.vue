<template>
	<div class="d-flex justify-content-between">
		<button class="btn btn-success" @click="sendNotification">Send Notification</button>
		<button class="btn" 
			:disabled="btnDisabled || loading"
			:class="[ isPNEnabled ? 'btn-danger' : 'btn-primary']" 
			@click="enablePN">
			{{ !isPNEnabled ? 'Enable' : 'Disable' }} Push Notification</button>

	</div>
</template>

<script>
import axios from 'axios'
export default {
	data: () => ({
		loading: false,
		isPNEnabled: false,
		btnDisabled: true
	}),
	mounted() {
		this.regsiterServiceWorker()
	},
	methods: {
		regsiterServiceWorker() {
		    if (!"serviceWorker" in navigator) {
		    	console.log("Service worker isn't supported")
		        return;
		    }

		    //register the service worker
		    navigator.serviceWorker.register('/sw.js')
		        .then(() => {
		            console.log('serviceWorker installed!')
		            this.initServiceWorker();
		        })
		        .catch((err) => {
		            console.log(err)
		        });
		},
		initServiceWorker() {
			if (!'showNotification' in ServiceWorkerRegistration.prototype) {
				console.log("Notification isn't supported")
				return
			}

			if (Notification.permission === 'denied') {
				console.log("the user has blocked notification")
			}

			if (!"PushManager" in window) {
				console.log("Push messaging isn't supported")
				return
			}

			navigator.serviceWorker.ready.then(registration => {
				registration.pushManager.getSubscription()
					.then(subscription => {
						this.btnDisabled = false

						if (!subscription) {
							return
						}

						this.updateSubscription(subscription)
						this.isPNEnabled = true
					})
					.catch(e => {
						console.log("Error during getSubscription", e)
					})
			})
		},
		enablePN() {
			this.isPNEnabled ? this.unsubscribe() : this.subscribe()
		},
		subscribe() {
			navigator.serviceWorker.ready.then(registration => {
				const options = {
					userVisibleOnly: true,
					applicationServerKey: this.urlBase64ToUint8Array(process.env.MIX_VAPID_PUBLIC_KEY)
				}

				registration.pushManager.subscribe(options)
					.then(subscription => {
						this.isPNEnabled = true
						this.btnDisabled = false

						this.updateSubscription(subscription)
					})
					.catch(e => {
						if (Notification.permission === 'denied') {
							console.log("Permission for notification was denied")
							this.btnDisabled = true
						} else {
							console.log("Unable to subscribe to push.", e)
							this.btnDisabled = false
						}
					})

			})
		},
		unsubscribe() {
			navigator.serviceWorker.ready.then(registration => {
				registration.pushManager.getSubscription().then(subscription => {
					if (!subscription) {
						this.isPNEnabled = false
						this.btnDisabled = false
					}

					subscription.unsubscribe().then(() => {
						this.deleteSubscription(subscription)
						this.isPNEnabled = false
					}).catch(e => {
						console.log("Unsubscribe error: ", e)
					}).finally(() => {
						this.btnDisabled = false						
					})
				}).catch(e => {
					console.log("Error thrown while unsubscribing: ", e)
				})
			})
		},
		updateSubscription(subscription) {
			const publicKey = subscription.getKey("p256dh")
			const authToken = subscription.getKey("auth")
			const contentEncoding = (PushManager.supportedContentEncodings || ['aesgcm'])[0]

			const data = {
				endpoint: subscription.endpoint,
				publicKey: publicKey ? btoa(String.fromCharCode.apply(null, new Uint8Array(publicKey))) : null,
				authToken: authToken ? btoa(String.fromCharCode.apply(null, new Uint8Array(authToken))) : null,
				contentEncoding
			}
			this.loading = true
			axios.post("/subscribe", data).then(() => { this.loading = false} )
		},
		deleteSubscription(subscription) {
			this.loading = true
			axios.post("/unsubscribe", { endpoint: subscription.endpoint }).then(() => { this.loading = false} )
		},
		sendNotification() {
			this.loading = true
			axios.get("/push/notification")
				.then(() => { this.loading = false} )
				.catch(e => {
					console.log("error push notif: ", e)
				})
		},
		/**
	     * https://github.com/Minishlink/physbook/blob/02a0d5d7ca0d5d2cc6d308a3a9b81244c63b3f14/app/Resources/public/js/app.js#L177
	     *
	     * @param  {String} base64String
	     * @return {Uint8Array}
	     */
	    urlBase64ToUint8Array (base64String) {
	      const padding = '='.repeat((4 - base64String.length % 4) % 4)
	      const base64 = (base64String + padding)
	        .replace(/\-/g, '+')
	        .replace(/_/g, '/')

	      const rawData = window.atob(base64)
	      const outputArray = new Uint8Array(rawData.length)

	      for (let i = 0; i < rawData.length; ++i) {
	        outputArray[i] = rawData.charCodeAt(i)
	      }

	      return outputArray
	    }
	}
}
</script>