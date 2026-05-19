<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'profile', params: {user_name: notificationItem.extra.user_name}}">
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