<template>
    <div v-if="message" class="relative border whitespace-pre-wrap break-word text-xs p-base-2 rounded-xl max-w-3/4 w-max -mb-2 bg-reply-color border-reply-color dark:bg-slate-700 dark:border-white/10" :class="replyStory">
        <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
    </div>
</template>

<script>
import ChatMessageContent from '../ChatMessageContent.vue'

export default {
    components: { ChatMessageContent },
    props: ['message', 'owner'],
    computed: {
        replyStory() {
            return {
                'ms-auto': this.owner
            }
        }
    }
}
</script>
