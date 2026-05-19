<template>
    <Component 
        v-if="postTypeComponent" 
        :is="postTypeComponent" 
        :post="post"
    />
</template>

<script>
import FeedPhotos from '@/components/posts/types/FeedPhotos.vue'
import FeedShareLink from '@/components/posts/types/FeedShareLink.vue'
import FeedSharePost from '@/components/posts/types/FeedSharePost.vue'
import FeedVideo from '@/components/posts/types/FeedVideo.vue'
import FeedFiles from '@/components/posts/types/FeedFiles.vue'
import FeedPolls from '@/components/posts/types/FeedPolls.vue'
import FeedVibb from '@/components/posts/types/FeedVibb.vue'
import FeedShareItem from '@/components/posts/types/FeedShareItem.vue'

export default {
    props: {
        post: {
            type: Object,
            default: null
        }
    },
    computed: {
        postTypeComponent(){
            switch(this.post.type) {
                case 'photo':
                    return FeedPhotos
                case 'link':
                    return FeedShareLink
                case 'share':
                    return FeedSharePost
                case 'video':
                    return FeedVideo
                case 'file':
                    return FeedFiles
                case 'share_item':
                    return FeedShareItem
                case 'poll':
                    return FeedPolls
                case 'vibb':
                    return FeedVibb
            }  
            return null
        }
    },
}
</script>