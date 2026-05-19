<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="notificationItem.extra.url" :href="true">    
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