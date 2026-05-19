<template>
    <ContentWarningWrapper :content-warning-list="post.content_warning_categories" :post="post">
        <PaidContentWrapper :item="post">
            <div v-for="postItem in post.items" :key="postItem.id" class="activity_content video_feed_activity_content">
                <div v-if="postItem.subject.thumb" class="activity_content_thumb w-full bg-black relative">
                    <VideoPlayer ref="videoRef" :video="postItem.subject" autoPlay :is-content-warning="isContentWarning" class="w-full" :class="(aspectRatioVideo(post.items[0].subject.thumb.params) == 'horizontal') ? '' : 'max-w-[16rem] mx-auto'"  />
                </div>
            </div>
        </PaidContentWrapper>
    </ContentWarningWrapper>
</template>

<script>
import {uuidv4} from '@/utility/index'
import VideoPlayer from '@/components/utilities/VideoPlayer.vue';
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import PaidContentWrapper from '@/components/paid_content/PaidContentWrapper.vue';

export default {
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
            videoKey: uuidv4(),
            isContentWarning: Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
        }
    },
    components: { VideoPlayer, ContentWarningWrapper, PaidContentWrapper },
    watch:{
        post: {
            handler: function() {
                this.isContentWarning = Boolean(this.post.content_warning_categories.length && this.post.showContentWarning)
            },
            deep: true
        }
    }
}
</script>
