<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'setting_subscription_detail', params: {id: notificationItem.extra.subscription_id}}">
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