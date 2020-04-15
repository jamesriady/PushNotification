<template>
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
            	<span>Notifications ({{total}})</span>
            	<span v-show="total > 0"><a href="" class="btn-clear-all" @click="clearAllNotifications">Clear all</a></span>
            </div>

            <div class="card-body">
            	<ul class="list-group">
            		<notification v-for="notification in notifications" :key="notification.id" :notification="notification" @read="clearNotification(notification)" />
            	</ul>
            </div>    
        </div>
    </div>
</template>
<style type="text/css" scoped>
	.btn-clear-all {
		text-decoration: none;
		color: red;
	}
</style>
<script>
import axios from 'axios'
import Notification from './Notification'
export default {
	components: {
		Notification
	},
	data: () => ({
		notifications: [],
		total: 0,
	}),
	mounted() {
		if(window.Echo) {
			this.listen()
		}
	},
	created() {
		this.getNotifications()
	},
	methods: {
		async getNotifications() {
			var res = await axios.get("/notifications")
			this.notifications = res.data.notifications.map(x => {
				const { action_url, body, title } = x.data
				return {
					id: x.id,
					action_url,
					body,
					title,
					created: x.created
				}
			})
				
			this.total = res.data.total
		},
		listen() {
			var userID = window.Laravel.user.id
			window.Echo.private(`App.User.${userID}`)
				//  receive new notification without needing to refresh page
				.notification(notification => {
					this.total++
	          		this.notifications.unshift(notification)
				})
				.listen('ReadAll', res => {
					const { unread_notifications } = res.user
					this.total = unread_notifications.length
					this.notifications = unread_notifications
				})
				.listen('Read', ({ endpoint }) => {
					this.notifications = this.notifications.filter(x => x.id !== endpoint);
					this.total = this.notifications.length
				})
		},
		clearAllNotifications(e) {
			e.preventDefault()
			axios.get("/notifications/read");
		},
		clearNotification(notification) {
			axios.get("/notifications/read/" + notification.id);
			window.open(notification.action_url, "_blank")
		}
	}
}
</script>