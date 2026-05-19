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
    </div>
</template>

<script>
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'

export default {
    components: { UserName, ContentHtml },
    props:{
        comment:{
            type: Object,
            default: null
        },
        subjectType: {
            type: String,
            default: 'comments'
        }
    }
}
</script>