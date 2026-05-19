<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <div v-if="message" class="relative border whitespace-pre-wrap break-word text-sm p-base-2 rounded-xl max-w-3/4 w-max" :class="chatTextClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
            <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
        </div>
    </div>
</template>

<script>
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'

export default {
    components: { ChatMessageContent, ChatMessageAction },
    props: ['message', 'owner', 'room_info'],
    computed: {
        chatTextClass() {
            return {
                'bg-white border-divider dark:bg-slate-800 dark:border-slate-700': this.message.is_delete,
                'owner-message-item bg-primary-color border-primary-color text-white dark:bg-dark-primary-color dark:border-dark-primary-color ms-auto': !this.message.is_delete && this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white': !this.message.is_delete && !this.owner
            }
        }
    }
}
</script>
