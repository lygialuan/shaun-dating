<template>
    <div class="activity_content share_link_activity_content">
        <YoutubePlayer v-if="postItem.subject.youtube_id" :video-id="postItem.subject.youtube_id" />
        <div v-else :style="{ backgroundColor: `${(postItem.subject.photo && postItem.subject.photo?.params.dominant_color) ? postItem.subject.photo?.params.dominant_color : '#000'}`}">
            <div v-if="postItem.subject.photo" class="activity_content_thumb w-full">
                <a :href=postItem.subject.url target="_blank">
                    <img class="activity-img max-h-[50vh] mx-auto" :src=postItem.subject.photo.url>
                </a>
            </div>
            <div v-if="postItem.subject.title" class="fetched-link activity_content_info bg-primary-color p-base-2 text-white dark:bg-dark-primary-color">
                <div class="font-bold block">
                    <a :href=postItem.subject.url target="_blank" class="text-inherit break-word">{{postItem.subject.title}}</a>
                </div>                    
                <div v-if="postItem.subject.description" class="truncate-3 mt-2">{{postItem.subject.description}}</div>
            </div>
        </div>
    </div>
</template>

<script>
import YoutubePlayer from '@/components/utilities/YoutubePlayer.vue';

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
    components: {
        YoutubePlayer
    },
    computed: {
        postItem(){
            return this.post.items[0]
        }
    }
}
</script>