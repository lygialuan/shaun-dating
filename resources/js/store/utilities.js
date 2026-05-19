import { defineStore } from 'pinia'
import { getPingNotification } from '../api/utility'
import { useAuthStore } from '@/store/auth'

export const useUtilitiesStore = defineStore('utilities', {
    // convert to a function
    state: () => ({
        isShownSearchMobile: false,
        pingNotificationCount: 0,
        pingChatCount: 0,
        eventDragDrop: null,
        selectedPage: null,
        showChatBoxBottom: false,
        chatBoxBottomRoomActive: null,
        chatBoxBottomRoomType: 'chat',
        currentAudioPlaying: null
    }),
    actions: {
        async pingNotification(){
            await getPingNotification()
            .then((response) => {        
                this.pingNotificationCount = response.data.data.notify_count
                this.pingChatCount = response.data.data.chat_count
                
                useAuthStore().updateWalletBalance(response.data.data.wallet_balance)
            });
		},
        setChatCount (chatCount) {
            this.pingChatCount = chatCount
        },
        setNotificationCount (notificationCount) {
            this.pingNotificationCount = notificationCount
        },
        setEventDragDrop(event) {            
            this.eventDragDrop = event
        },
        openSearchMobile(){
            this.isShownSearchMobile = true;
        },
        closeSearchMobile(){
            this.isShownSearchMobile = false
        },
        setSelectedPage(selectedPage) {
            this.selectedPage = selectedPage
        },
        setShowChatBoxBottom(isShow = false, roomId = null, type = 'chat'){
            this.showChatBoxBottom = isShow
            this.chatBoxBottomRoomActive = roomId
            this.chatBoxBottomRoomType = type
        },
        setCurrentAudioPlaying(audio){
            this.currentAudioPlaying = audio
        }
    },
    persist: false
  })