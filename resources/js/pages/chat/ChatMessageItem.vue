<template>
    <div v-if="isOwner" class="group flex gap-base-2"> <!-- Messages of current user -->
        <div class="w-10 h-10 min-w-10"></div>
        <div class="w-full min-w-0">
            <ChatMessageUnsent v-if="message.is_delete" :owner="true" />
            <template v-else>
                <template v-if="message.parent_message">
                    <ChatReplyMessageUnsent v-if="message.parent_message.is_delete" :owner="true" />
                    <ChatReplyContentType v-else :message="message.parent_message" :owner="true" />
                </template>
                <ChatContentType :message="message" :owner="true" :room_info="room_info" />
            </template>
        </div>
    </div>
    <div v-else class="group flex gap-base-2"> <!-- Messages of others -->
        <Avatar v-if="showAvatar" :user="message.user" :activePopover="false"/>
        <div v-else class="w-10 h-10 min-w-10"></div>
        <div class="w-full min-w-0">                                
            <ChatMessageUnsent v-if="message.is_delete" />
            <template v-else>
                <template v-if="message.parent_message">
                    <ChatReplyMessageUnsent v-if="message.parent_message.is_delete" />
                    <ChatReplyContentType v-else :message="message.parent_message" />
                </template>
                <ChatContentType :message="message" :room_info="room_info" />
            </template>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import Avatar from '@/components/user/Avatar.vue'
import ChatContentType from '@/pages/chat/ChatContentType.vue'
import ChatReplyContentType from '@/pages/chat/ChatReplyContentType.vue';
import ChatMessageUnsent from '@/pages/chat/ChatMessageUnsent.vue';
import ChatReplyMessageUnsent from '@/pages/chat/ChatReplyMessageUnsent.vue';
import { useAuthStore } from '../../store/auth';

export default {
    components: { Avatar, ChatContentType, ChatReplyContentType, ChatMessageUnsent, ChatReplyMessageUnsent },
    props: ['message', 'showAvatar', 'room_info'],
    computed: {
        ...mapState(useAuthStore, ['user']),
        isOwner(){
            return this.message.user_id === this.user.id
        }
    }
}
</script>