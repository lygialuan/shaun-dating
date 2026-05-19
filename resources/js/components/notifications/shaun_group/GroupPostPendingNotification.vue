<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'groups_manage', params: { id: notificationItem.extra.group_id, tab: 'groups_manage_posts' }}">    
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