<template>
    <Component v-if="chatReplyTypeComponent" :is="chatReplyTypeComponent" :message="message" :owner="owner"/>
</template>

<script>
import ChatReplyPhotos from './reply_types/ChatReplyPhotos.vue'
import ChatReplyShareLink from './reply_types/ChatReplyShareLink.vue'
import ChatReplyText from './reply_types/ChatReplyText.vue'
import ChatReplyStories from './reply_types/ChatReplyStories.vue'
import ChatReplyFile from './reply_types/ChatReplyFile.vue'
import ChatReplySendFund from './reply_types/ChatReplySendFund.vue'
import ChatReplyShareStory from './reply_types/ChatReplyShareStory.vue'
import ChatReplyAudio from './reply_types/ChatReplyAudio.vue'

export default {
    props: ['message', 'owner'],
    computed: {
        chatReplyTypeComponent(){
            switch(this.message.type) {
                case 'photo':
                    return ChatReplyPhotos
                case 'link':
                    return ChatReplyShareLink
                case 'story_reply':
                    return ChatReplyStories
                case 'file':
                    return ChatReplyFile
                case 'send_fund':
                    return ChatReplySendFund
                case 'story_share':
                    return ChatReplyShareStory
                case 'audio':
                    return ChatReplyAudio
                default:
                    return ChatReplyText;
            }
        }
    }
}
</script>