<template>
    <template v-if="showMiniChatBox()">
        <div v-if="showChatBoxBottom" class="main-content-section fixed bottom-0 end-0 border-divider p-0 m-0 max-w-3xl w-full h-bubble-chat-box border-e-0 rounded-none rounded-tl-base-lg shadow-lg z-50 dark:border-white/10">
            <div class="flex h-full">
                <div class="flex flex-col w-72 p-base-2 border-e border-divider dark:border-white/10">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <button v-if="chatBoxBottomRoomType === 'chat_requests'" class="text-main-color dark:text-white" @click="backToChat">
                            <BaseIcon name="arrow_left" class="align-middle" />						
                        </button>
                        <h1 class="page-title flex-1">{{chatBoxBottomRoomType === 'chat_requests' ? $t('Requests') : $t('Chat')}}</h1>
                        <button v-if="chatBoxBottomRoomType === 'chat' && requestsCount > 0" @click="handleClickRequestCount" class="text-primary-color font-bold dark:text-dark-primary-color">{{ $filters.numberShortener(requestsCount, $t('[number] request'), $t('[number] requests')) }}</button>
                    </div>
                    <ChatRoomsList :type="chatBoxBottomRoomType" :room_id="chatBoxBottomRoomActive" @updateRoomId="updateRoomId" :has-router="false"/>
                </div>
                <div class="flex-1 flex flex-col min-h-0 min-w-0">
                    <template v-if="chatBoxBottomRoomActive">
                        <div v-if="roomInfo" class="flex items-center pe-4 border-b border-divider dark:border-white/10">
                            <ChatRoomHeader :room-info="roomInfo" class="flex-1 min-w-0"/>
                            <button @click="toggleChatBox" class="text-sub-color dark:text-slate-400">
                                <BaseIcon name="close" />
                            </button>
                        </div>
                        <ChatRoomDetail :key="chatBoxBottomRoomActive" :room_id="chatBoxBottomRoomActive" />
                    </template>
                    <template v-else>
                        <div class="flex items-center justify-end h-[70px] pe-4 border-b border-divider dark:border-white/10">
                            <button @click="toggleChatBox" class="text-sub-color dark:text-slate-400">
                                <BaseIcon name="close" />
                            </button>
                        </div>
                        <div class="flex flex-col gap-base-2 items-center justify-center h-full p-5">
                            <div class="flex items-center justify-center w-24 h-24 rounded-full bg-gray-6 text-main-color dark:bg-dark-form-surface dark:text-slate-300 dark:border-dark-form-surface">
                                <BaseIcon name="chats" size="32"/>
                            </div>
                            <div class="text-xl font-semibold">{{ $t('Select a chat to start messaging') }}</div>
                        </div>
                    </template>
                </div>    
            </div>
        </div>
        <button v-else @click="toggleChatBox" class="chat-bubble fixed bottom-6 end-6 rounded-full bg-primary-color hover:bg-hover text-white shadow-lg p-4 z-50 dark:bg-dark-form-surface">
            <BaseIcon name="chats" />
            <span v-if="pingChatCount > 0" class="absolute -top-1 -end-1 flex items-center justify-center w-6 h-6 bg-base-red rounded-full text-sm text-white">{{pingChatCount > 9 ? '9+' : pingChatCount}}</span>
        </button>
    </template>
</template>
<script>
import { mapActions, mapState } from 'pinia';
import { useUtilitiesStore } from '@/store/utilities';
import { useChatStore } from '@/store/chat';
import { getRequestsCount } from '@/api/chat'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import ChatRoomsList from '@/pages/chat/ChatRoomsList.vue';
import ChatRoomHeader from '@/pages/chat/ChatRoomHeader.vue'
import ChatRoomDetail from '@/pages/chat/ChatRoomDetail.vue';

export default {
	components: { BaseIcon, ChatRoomsList, ChatRoomHeader, ChatRoomDetail },
    data(){
        return{
            requestsCount: 0
        }
    },
    computed:{
		...mapState(useUtilitiesStore, ['pingChatCount', 'showChatBoxBottom', 'chatBoxBottomRoomActive', 'chatBoxBottomRoomType']),
        ...mapState(useChatStore, ['roomInfo'])
	},
    mounted(){
        if (this.showChatBoxBottom && this.chatBoxBottomRoomType === 'chat') {
            this.getRequestsCount()
        }
    },
    unmounted(){
        this.setShowChatBoxBottom(false)
    },
    updated(){
        if (this.showChatBoxBottom && this.chatBoxBottomRoomType === 'chat') {
            this.getRequestsCount()
        }
    },
	methods: {
        ...mapActions(useUtilitiesStore, ['setShowChatBoxBottom']),
        async getRequestsCount(){
            try {
                const response = await getRequestsCount()
                this.requestsCount = response.request_count
            } catch (error) {
                console.log(error)
            }
        },
		toggleChatBox(){
            let permission = 'chat.allow'
			if(this.checkPermission(permission)){
                this.setShowChatBoxBottom(!this.showChatBoxBottom)
			}
        },
        updateRoomId(roomId){
            this.setShowChatBoxBottom(true, roomId, this.chatBoxBottomRoomType)
        },
        backToChat(){
            this.setShowChatBoxBottom(true, null, 'chat')
        },
        handleClickRequestCount(){
            this.setShowChatBoxBottom(true, null, 'chat_requests')
        }
	}
}
</script>