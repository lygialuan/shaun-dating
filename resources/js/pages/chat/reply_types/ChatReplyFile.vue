<template>
    <div class="flex flex-col gap-1 w-full" :class="{'items-end': owner}">
        <div v-if="message.content" class="whitespace-pre-wrap break-word text-xs border p-base-2 rounded-xl w-[fit-content]" :class="replyFileClass" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
            <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
        </div>
        <div v-if="message.items" class="flex flex-col gap-1 max-w-2/3 lg:max-w-1/2 w-max -mb-2" :class="owner ? 'items-end' : 'items-start'">
            <a v-for="chatItem in message.items" :key="chatItem.subject.id" :href="chatItem.subject.url" :download="chatItem.subject.name" class="flex items-center gap-base-1 bg-reply-color border border-reply-color text-inherit text-xs p-base-2 rounded-xl break-word dark:bg-slate-700 dark:border-white/10" v-tooltip="{value: message.created_at_full, showDelay: 1500}">
                <BaseIcon name="file" size="18" />
                {{ chatItem.subject.name }}
            </a>
        </div>
    </div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';
import ChatMessageContent from '../ChatMessageContent.vue'

export default {
    props: ['message', 'owner'],
    components: {
		BaseIcon, ChatMessageContent
	},
    computed: {
        replyFileClass() {
            return {
                'owner-message-item bg-reply-color border-reply-color text-main-color dark:bg-slate-700 dark:border-white/10 dark:text-white max-w-3/4': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white max-w-[calc(75%+37.5px)]': !this.owner
            }
        }
    }
}
</script>