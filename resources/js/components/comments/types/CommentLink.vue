<template>
    <div>
        <UserName :user="comment.user" class="float-start me-1 reply-item-username max-w-full" />
        <ContentHtml 
            :content="comment.content" 
            :mentions="comment.mentions"
            :can-translate="comment.canContentTranslate"
            :subject-id="comment.id"
            :subject-type="subjectType"
            class="reply-item-content"
        />
        <div v-for="commentItem in comment.items" :key="commentItem.id" class="activity_content share_link_activity_content my-1">
            <div class="inline-flex border border-divider rounded-md dark:border-white/10">
                <div v-if="commentItem.subject.photo" class="activity_content_thumb flex items-center w-28 h-28 border-e border-divider flex-shrink-0 rounded-l-md overflow-hidden dark:border-white/10" :style="{ backgroundColor: `${(commentItem.subject.photo && commentItem.subject.photo?.params.dominant_color) ? commentItem.subject.photo?.params.dominant_color : '#000'}`}">
                    <a :href=commentItem.subject.url target="_blank">
                        <img class="activity-img mx-auto" :src=commentItem.subject.photo.url>
                    </a>
                </div>
                <div v-if="commentItem.subject.title" class="flex items-center p-base-2">
                    <div class="font-bold block">
                        <a :href=commentItem.subject.url target="_blank" class="text-inherit truncate-3">{{commentItem.subject.title}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <PhotoTheater ref="photoTheater" :photos="photoTheaterItems"/>
</template>

<script>
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PhotoTheater from '@/components/modals/PhotoTheater.vue';

export default {
    components: { UserName, ContentHtml, PhotoTheater },
    props:{
        comment:{
            type: Object,
            default: null
        },
        subjectType: {
            type: String,
            default: 'comments'
        }
    },
    data(){
        return{
            photoTheaterItems: []
        }
    },
    methods:{
        handleClickCommentImage(photoItems, photoIndex){
            this.photoTheaterItems = photoItems
            this.$refs.photoTheater.openPhotosTheater(photoIndex)
        }
    }
}
</script>