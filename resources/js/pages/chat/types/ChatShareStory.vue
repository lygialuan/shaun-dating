<template>
    <div :class="{'text-end ms-auto': owner}">
        <template v-if="messageItems[0].subject">
            <div v-if="owner" class="inline-block bg-web-wash text-xs px-2 py-1 rounded-md dark:bg-dark-web-wash">
                {{ $filters.textTranslate( $t("Shared [user_name]'s story"), { user_name: messageItems[0].subject.user.name }) }}
            </div>   
            <div v-else class="inline-block bg-web-wash text-xs px-2 py-1 rounded-md dark:bg-dark-web-wash">
                {{ $filters.textTranslate( $t("Sent [user_name]'s story"), { user_name: messageItems[0].subject.user.name }) }}
            </div>
            <div v-if="messageItems" class="flex flex-wrap gap-2 -mt-base-1" :class="{'justify-end': owner}">
                <div v-for="chatItem in messageItems" :key="chatItem.id">
                    <div v-if="chatItem.subject" class="relative border border-divider rounded-base-lg w-16 h-28 -mb-6 cursor-pointer dark:bg-slate-800 dark:border-slate-800" @click="showStoryDetail(chatItem.subject.id)">
                        <div class="bg-gray-300 h-full text-center text-xs rounded-base-lg">                 
                            <StoryContentPreview :story="chatItem.subject" />
                        </div>
                    </div>
                </div>
            </div>
        </template>
        <div v-else class="-mb-base-1">
            <div class="inline-block bg-web-wash text-xs px-2 py-1 rounded-md dark:bg-dark-web-wash">{{ $t('This content is no longer available') }}</div>
        </div>
        <div class="flex items-center gap-base-2" :class="{'flex-row-reverse': !owner}">
            <div class="flex-1"></div>
            <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message" :owner="owner"/>
            <div v-if="message.content" class="inline-block relative text-sm p-base-2 border rounded-xl whitespace-pre-wrap break-word z-10 max-w-3/4 text-left" :class="chatStoryClass">
                <ChatMessageContent :content="message.content" :can-translate="message.canContentTranslate" :subject-id="message.id" />
            </div>
            <div v-else class="w-16"></div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import StoryItemDetailModal from '@/components/stories/StoryItemDetailModal.vue'
import StoryContentPreview from '@/components/stories/StoryContentPreview.vue'
import ChatMessageContent from '@/pages/chat/ChatMessageContent.vue'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'
import { useStoriesStore } from '@/store/stories'

export default {
    components: { StoryContentPreview , ChatMessageContent, ChatMessageAction },
    props: ['message', 'owner', 'room_info'],
    data(){
        return{
            messageItems: this.message.items
        }
    },
    computed: {
        ...mapState(useStoriesStore, ['deleteStoryItem']),
        chatStoryClass() {
            return {
                'owner-message-item bg-primary-color border-primary-color text-white dark:bg-dark-primary-color dark:border-dark-primary-color': this.owner,
                'message-item bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color dark:bg-dark-message dark:border-dark-message dark:text-white': !this.owner
            }
        }
    },
    watch: {
        deleteStoryItem(){
            this.messageItems = this.messageItems.filter(story => story.subject.id !== this.deleteStoryItem.id)
        }
    },
    methods: {
        showStoryDetail(storyItemId){
            this.$dialog.open(StoryItemDetailModal, {
                data: {
                    storyItemId: storyItemId
                },
                props:{
                    class: 'p-dialog-story p-dialog-story-detail p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        }
    }
}
</script>
