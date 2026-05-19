<template>
    <NotificationContent :notificationItem="notificationItem" @click="handleChatRequestClick">    
        <template v-slot:text>
            {{notificationItem.message}}
        </template>
    </NotificationContent>
</template>
<script>
import { mapActions } from 'pinia';
import { useUtilitiesStore } from '@/store/utilities';
import NotificationContent from '../NotificationContent.vue';

export default {
    props: ["notificationItem"],
    components: { NotificationContent },
    methods: {
        ...mapActions(useUtilitiesStore, ['setShowChatBoxBottom']),
        handleChatRequestClick(){
            let permission = 'chat.allow'
			if(this.checkPermission(permission)){
                if(this.showMiniChatBox()){
                    if (this.notificationItem.params) {
                        this.setShowChatBoxBottom(true, this.notificationItem.params.room_id, 'chat_requests');
                    } else {
                        this.setShowChatBoxBottom(true, null, 'chat_requests');
                    }
                } else {
                    if (this.notificationItem.params) {
                        this.$router.push({ name: 'chat_requests', params: {room_id: this.notificationItem.params.room_id}});
                    } else {
                        this.$router.push({ name: 'chat_requests'});
                    }
                }
			}
        }
    }
}
</script>