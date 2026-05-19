<template>
    <div
        @click="showRoom"
        class="room_items flex items-center px-2 py-base-2 rounded-xl"
        :class="[
            room_id == chatRoom.id ? 'room_items_active bg-light-blue dark:bg-dark-message' : '',
            !showIconChat ? 'cursor-pointer md:hover:bg-gray-6 md:dark:hover:bg-dark-body' : ''
        ]"
    >
        <div class="relative">
            <Avatar v-for="member in getOthersMemberInRoom(chatRoom.members)" :key="member.id" :user="member" :activePopover="false"  :size="45"/>
            <div v-if="chatRoom.is_online" class="absolute bottom-0 right-1 border border-white block w-base-2 h-base-2 bg-base-green rounded-full"></div>
        </div>
        <div class="flex-1 ms-base-2 me-4 min-w-0">
            <div class="flex flex-row items-center">
                <UserName v-for="member in getOthersMemberInRoom(chatRoom.members)" :key="member.id" :user="member" :activePopover="false" class="room_items_title_text" :class="{'font-bold': chatRoom.message_count > 0 }"/>
                <BaseIcon name="dots" class="ml-1" :size="3"></BaseIcon>
                <span v-if="chatRoom.last_message" class="ml-1 text-xs text-[#B3B3B3]">{{chatRoom.last_message.created_at_short}}</span>
            </div>
            <div v-if="chatRoom.last_message" class="room_items_date_text flex text-xs text-sub-color whitespace-nowrap dark:text-dark-text-base" :class="{'font-bold': chatRoom.message_count > 0 }">
                <span class="truncate">{{chatRoom.last_message.is_delete ? $t('Message has been unsent') : chatRoom.last_message.short_content}}</span>
            </div>          
        </div>
        <div v-if="showAction" class="flex items-center gap-base-1">
            <template v-if="showIconChat">     
                <button @click="clickChat(chatRoom.id)" class="flex items-center gap-2 px-3 sm:px-4 py-2 text-xs sm:text-sm border rounded-xl hover:bg-gray-300 bg-gray-100 dark:hover:bg-slate-800 dark:border-dark-form-surface dark:bg-dark-form-surface flex-shrink-0">
                    <BaseIcon name="message" class="align-middle" :size="16"></BaseIcon>
                    <span class="hidden sm:inline font-bold">{{ $t("Chat") }}</span>
                </button>               
            </template>
            <template v-else>                    
                <template v-if="chatRoom.enable && room_id != chatRoom.id">                    
                    <DropdownMenu class="flex items-center justify-center" :offsetY="0">
                        <template v-slot:dropdown-button>
                            <button class="flex items-center justify-center w-8 h-8" name="chatOptions">                     
                                <BaseIcon name="more_horiz_outlined" size="20" class="room_items_dropdown_icon chat-room-options text-sub-color dark:text-slate-400" />
                            </button>
                        </template>
                        <template v-slot:dropdown-content>
                            <ul class="text-sm min-w-[150px]">
                                <li v-if="chatRoom.message_count > 0" class="rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                    <button @click="this.markSeen(chatRoom.id)" class="block w-full text-start p-2">{{$t('Mark as Read')}}</button>
                                </li>
                                <li v-else class="rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                    <button @click="this.markUnseen(chatRoom.id)" class="block w-full text-start p-2">{{$t('Mark as Unread')}}</button>
                                </li>                                
                            </ul>
                        </template>
                    </DropdownMenu>
                </template>
                <BaseIcon v-if="!chatRoom.enable_notify" name="bell_slash" size="16" class="align-middle" />
                <span v-if="chatRoom.message_count > 0" class="room_items_dot inline-block h-base-2 w-base-2 rounded-full bg-primary-color dark:bg-dark-primary-color"></span>
            </template>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { changeUrl } from '@/utility';
import { useChatStore } from '@/store/chat';
import { useAuthStore } from '@/store/auth';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { BaseIcon, DropdownMenu, Avatar, UserName },
    props: {
		chatRoom: {
			type: Object,
			default: null
		},
        room_id: {
			type: Number,
			default: null
		},
        showAction: {
			type: Boolean,
			default: true
		},
        hasRouter: {
			type: Boolean,
			default: true
		},
        showIconChat: {
			type: Boolean,
			default: false
		},
    },
    computed:{
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        ...mapActions(useChatStore, ['markSeenRoom', 'markUnseenRoom']),
        showRoom(e){
            if(e.target.getAttribute('name') == 'chatOptions'){
                return 
            }
            var name = 'chat'
            if (this.$route.name == 'chat_requests')  {
                name = 'chat_requests';
            }
            let roomUrl = this.$router.resolve({
                name: name,
                params: {'room_id': this.chatRoom.id}
            });
            if(this.hasRouter){
                changeUrl(roomUrl.fullPath)
            }
            this.$emit('updateRoomId', this.chatRoom.id)
        },
        async markSeen(roomId){
            try {
                await this.markSeenRoom(roomId)
            } catch (error) {
                this.showError(error.error)
            }
        },
        async markUnseen(roomId){
            try {
                await this.markUnseenRoom(roomId)
            } catch (error) {
                this.showError(error.error)
            }
        },
        clickChat(roomId) {
			let permission = 'chat.allow'
			if(this.checkPermission(permission)){
                this.$router.push({name: 'chat', params: { 'room_id': roomId }});
			}
		},
    },
    emits: ['updateRoomId']
}
</script>