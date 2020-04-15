(() => {
    'use strict'

    const WebPush = {
        init() {
            self.addEventListener('push', this.notificationPush.bind(this))
            self.addEventListener('notificationclick', this.notificationClick.bind(this))
            self.addEventListener('notificationclose', this.notificationClose.bind(this))
        },
        notificationPush(event) {
            if (!(self.Notification && self.Notification.permission === 'granted')) {
                return
            }

            if (event.data) {
                event.waitUntil(this.sendNotification(event.data.json()))
            }
        },
        notificationClick(event) {
            const { notification } = event
            var lenData = notification.actions.length
            if (lenData > 0) {
                for (var i = 0; i < lenData; i++) {
                    self.clients.openWindow(notification.data.urls[notification.actions[i]['action']])
                }
            }
            self.registration.pushManager.getSubscription().then(subscription => {
                if (subscription) {
                    this.dismissNotification(event, subscription)
                }
            })
        },
        notificationClose(event) {
            self.registration.pushManager.getSubscription().then(subscription => {
                if (subscription) {
                    this.dismissNotification(event, subscription)
                }
            })
        },
        sendNotification(data) {
            return self.registration.showNotification(data.title, data)
        },
        dismissNotification({ notification }, { endpoint }) {
            if (!(notification.data || notification.data.id)) {
                return
            }
            fetch(`/notifications/${notification.data.id}/dismiss`, {
                method: 'POST',
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ endpoint })
            })

        }
    }

    WebPush.init()
})()