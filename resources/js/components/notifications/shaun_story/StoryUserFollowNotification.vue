<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="{name: 'story_view_item', params: {storyItemId: notificationItem.extra.story_item_id}}">    
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