<template>
    <div class="px-base-2 md:px-4">
        <div v-if="post" class="relative pb-[100%] cursor-pointer" role="button" @click="handleOpenVibbModal">
            <div class="absolute inset-0" :id="`vibbItem-${post.id}`">
                <ContentWarningWrapper :content-warning-list="post.content_warning_categories" :post="post" class="h-full rounded-md overflow-hidden">
                    <VideoPlayerShort ref="feedVibbRef" :video="vibbItem.subject" reload autoPlay :allow-toggle-play="false" :allow-full-screen="false" :allow-pip="false" :show-progress-bar="false" :is-content-warning="isContentWarning" class="w-full h-full overflow-hidden" />
                </ContentWarningWrapper>
            </div>
            <BaseIcon name="video_solid" class="absolute top-3 end-3 text-white" />
        </div>
    </div>
</template>

<script>
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { 
        VideoPlayerShort, 
        ContentWarningWrapper,
        BaseIcon
    },
    props: {
        post: {
            type: Object,
            default: null
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    data(){
        return{
            isContentWarning: Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
        }
    },
    computed: {
        vibbItem(){
            return this.post.items[0]
        }
    },
    watch:{
        post: {
            handler: function() {
                this.isContentWarning = Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
            },
            deep: true
        }
    },
    methods:{
        handleOpenVibbModal(){
            this.openVibb({
                vibb: this.post
            })
        }
    }
}
</script>