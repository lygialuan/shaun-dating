<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <div class="flex flex-col gap-2 max-w-2/3 lg:max-w-7/12 relative" :class="owner ? 'items-end' : 'items-start'">
            <div v-if="message.content" class="whitespace-pre-wrap break-word text-sm border p-base-2 rounded-xl w-[fit-content]" :class="chatFileClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
                <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
            </div>
            <template v-if="message.items.length">
                <a v-for="chatItem in message.items" :key="chatItem?.subject?.id" :href="chatItem?.subject?.url" :download="chatItem?.subject?.name" class="flex items-center gap-base-2 bg-web-wash text-inherit text-sm p-base-2 rounded-xl max-w-full break-word dark:bg-dark-web-wash dark:text-white" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
                    <BaseIcon name="file" />
                    {{ chatItem?.subject?.name }}
                </a>
            </template>
        </div>
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'

export default {
    components: {
		BaseIcon, ChatMessageContent, ChatMessageAction
	},
	props: ['message', 'owner', 'room_info'],
	data() {
		return {
			displayPhotosTheater: false,
		}
	},
    computed: {
        chatFileClass() {
            return {
                'owner-message-item bg-primary-color border-primary-color text-white dark:bg-dark-primary-color dark:border-dark-primary-color max-w-3/4': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white max-w-[calc(75%+37.5px)]': !this.owner
            }
        }
    }
}
</script>