<template>
    <BaseButton type="outlined" @click="sendMessage()">{{$t('Message')}}</BaseButton>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { useAuthStore } from '@/store/auth';
import { useUtilitiesStore } from '@/store/utilities';
import { useChatStore } from '@/store/chat';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: {
		BaseButton
	},
    name: 'MessageButton',
    props: ['user_id'],
    computed: {
		...mapState(useAuthStore, ['user', 'authenticated'])
	},
    methods: {
        ...mapActions(useUtilitiesStore, ['setShowChatBoxBottom']),
        ...mapActions(useChatStore, ['createNewRoom']),
        async sendMessage(){
            if(this.authenticated){
                let permission = 'chat.allow'
                if(this.checkPermission(permission)){
                    try {
                        const response = await this.createNewRoom(this.user_id);
                        if(this.showMiniChatBox()){
                            this.setShowChatBoxBottom(true, response.id)
                        } else {
                            this.$router.push({name: 'chat', params: { 'room_id': response.id }});
                        }
                    }
                    catch (error) {
                        this.showError(error.error)
                    }
                }
            }else{
                this.showRequireLogin()
            }
		}
    }
}
</script>