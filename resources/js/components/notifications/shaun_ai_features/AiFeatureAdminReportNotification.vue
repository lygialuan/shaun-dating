<template>
    <NotificationContent :notificationItem="notificationItem" :notificationLink="notificationItem.href" :href="true">
        <template v-slot:text>
            {{notificationItem.message}}
        </template>
    </NotificationContent>
</template>

<script>
import { mapState} from 'pinia'
import { useAuthStore } from '@/store/auth'
import NotificationContent from '../NotificationContent.vue';

export default {
    props: ["notificationItem"],
    components: { NotificationContent },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
}
</script>