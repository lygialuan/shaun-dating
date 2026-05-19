<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'advertising_detail', params: {id: notificationItem.extra.id}}">
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