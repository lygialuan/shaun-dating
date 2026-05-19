<template>
    <Component v-if="commentTypeComponent" :is="commentTypeComponent" :comment="comment" :subject-type="subjectType" />
</template>

<script>
import CommentText from '@/components/comments/types/CommentText.vue'
import CommentPhotos from '@/components/comments/types/CommentPhotos.vue'
import CommentLink from '@/components/comments/types/CommentLink.vue'

export default {
    props: {
        comment: {
            type: Object,
            default: null
        },
        subjectType: {
            type: String,
            default: 'comments'
        }
    },
    computed: {
        commentTypeComponent(){
            switch(this.comment.type) {
                case 'photo':
                    return CommentPhotos
                case 'link':
                    return CommentLink
                default:
                    return CommentText
            }
        }
    },
}
</script>