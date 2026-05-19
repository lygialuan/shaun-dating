<template>
    <Component :is="notificationType" :notificationItem="notificationItem"/>
</template>
<script>
import { defineAsyncComponent } from "vue";

export default {
    props: ['notificationItem'],
    computed: {
        notificationType(){
            // convert notification type (ex: post_like to PostLike)
            const notificationType = window._.startCase(this.notificationItem.type).replace(/ /g, '')
            return defineAsyncComponent(() => import(`./${this.notificationItem.package}/${notificationType}Notification.vue`));     
        }
    }
}
</script>