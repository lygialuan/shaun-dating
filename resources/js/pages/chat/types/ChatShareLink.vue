<template>
    <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
        <div class="relative max-w-2/3 lg:max-w-5/12 w-full text-sm border rounded-xl" :class="chatShareLinkClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
            <div v-if="message.content" class="block p-base-2 break-word">
                <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
            </div>
            <template v-if="message.items.length">
                <div v-for="chatItem in message.items" :key="chatItem.id" class="mx-auto">
                    <div v-if="chatItem.subject.photo" class="relative pb-[56.25%]">
                        <a :href="chatItem.subject.url" target="_blank" class="flex items-center justify-center bg-gray-6 w-full h-full absolute inset-0 overflow-hidden dark:bg-dark-message">
                            <img class="max-w-full max-h-full" :src=chatItem.subject.photo.url>
                        </a>
                    </div>
                    <div class="p-base-2">
                        <div class="font-bold w-full truncate mb-1">
                            <a :href=chatItem.subject.url target="_blank" class="text-inherit">{{chatItem.subject.title}}</a>
                        </div>                    
                        <div v-if="chatItem.subject.description" class="truncate w-full">{{chatItem.subject.description}}</div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'

export default {
    components: {
		ChatMessageContent, ChatMessageAction
	},
    props: ['message', 'owner', 'room_info'],
    computed: {
        chatShareLinkClass() {
            return {
                'owner-message-item bg-primary-color border-primary-color text-white dark:text-white dark:bg-dark-primary-color dark:border-dark-primary-color': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white': !this.owner
            }
        }
    }
}
</script>