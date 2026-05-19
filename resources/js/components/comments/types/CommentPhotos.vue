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
        <template v-if="comment.items.length">
            <div class="clear-both"></div>
            <div class="flex flex-wrap gap-base-1 mb-1">
                <div v-for="(commentItem, index) in comment.items" :key="commentItem.id" class="border border-divider inline-block w-16 h-16 bg-cover bg-center rounded-md dark:border-white/10 cursor-pointer" :style="{ backgroundImage: `url(${commentItem.subject.url})`}" @click="handleClickCommentImage(comment.items, index)"></div> 
            </div>
        </template>
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