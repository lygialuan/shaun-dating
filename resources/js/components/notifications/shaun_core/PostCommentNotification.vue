<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'post', params: {id: notificationItem.extra.post_id, comment_id: notificationItem.extra.comment_id}}">
        <template v-slot:text>
            {{notificationItem.message}}
        </template>
    </NotificationContent>
</template>
<script>
import NotificationContent from '../NotificationContent.vue';
export default {
    props: ["notificationItem"],
    components: { NotificationContent }
}
</script>