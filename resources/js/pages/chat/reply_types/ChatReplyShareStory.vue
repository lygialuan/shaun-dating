<template>
    <div v-if="message">
        <div v-if="messageItems[0].subject" class="flex flex-wrap gap-2 -mt-base-1" :class="{'justify-end': owner}">
            <div v-for="chatItem in messageItems" :key="chatItem.id">
                <div v-if="chatItem.subject" class="relative border border-divider rounded-base-lg w-16 h-28 -mb-6 cursor-pointer dark:bg-slate-800 dark:border-slate-800" @click="showStoryDetail(chatItem.subject.id)">
                    <div class="bg-gray-300 h-full text-center text-xs rounded-base-lg">                 
                        <StoryContentPreview :story="chatItem.subject" />
                    </div>
                </div>
            </div>
        </div>
        <div v-else class="-mb-base-1" :class="{'text-end ms-auto': owner}">
            <div class="inline-block bg-web-wash text-xs px-2 py-1 rounded-md dark:bg-dark-web-wash">{{ $t('This content is no longer available') }}</div>
        </div>
    </div>
</template>

<script>
import StoryContentPreview from '@/components/stories/StoryContentPreview.vue'
import StoryItemDetailModal from '@/components/stories/StoryItemDetailModal.vue'

export default {
    components: { StoryContentPreview },
    props: ['message', 'owner'],
    data(){
        return{
            messageItems: this.message.items
        }
    },
    computed: {
        replyTextClass() {
            return {
                'ms-auto': this.owner
            }
        }
    },
    methods:{
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
                }
            });
        }
    }
}
</script>
