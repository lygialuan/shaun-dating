<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'group_profile', params: { id: notificationItem.extra.group_id, slug : notificationItem.extra.slug }}">    
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