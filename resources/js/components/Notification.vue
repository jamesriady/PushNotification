<template>
  <li class="notification list-group-item">
    <div class="media">
      <div class="media-left">
        <div class="media-object">
          <img src="/notification-icon.png" width="50" height="50">
        </div>
      </div>
      <div class="media-body">
        <strong class="notification-title">
          <a href="#" @click.prevent="markAsRead">{{ notification.title }}</a>
        </strong>
        <p class="notification-desc">
          {{ notification.body }}
        </p>

        <div class="notification-meta">
          <small class="timestamp">
            <timeago :datetime="notification.created" :auto-update="30" />
          </small>
        </div>
      </div>
    </div>
  </li>
</template>
<script>

export default {
  props: {
    notification: {
      type: Object,
      required: true
    }
  },

  methods: {
    markAsRead () {
      this.$emit('read')
    }
  }
}
</script>
