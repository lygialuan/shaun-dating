<template>
    <SkeletonCommentsList v-if="loading_comment" class="flex-1 mx-base-2 md:mx-4 border-t py-base-2 border-divider dark:border-white/10" :style="commentFormSpacing" />
    <div v-else class="flex-1 mx-base-2 md:mx-4" :class="commentsListClass" :style="commentFormSpacing">
        <div v-if="commentsList.length" ref="commentsListRef" class="flex-1 min-h-0 border-t py-base-2 overflow-auto scrollbar-hidden scroll-smooth border-divider dark:border-white/10">
            <div :ref="`topCommentsListRef_${key}`"></div>
            <div class="comment_list flex flex-col gap-base-2">
                <!-- show comment item detail -->
                <div v-if="commentInfo" class="border-t-4 border-b-4 border-divider py-base-2">
                    <CommentItem :item="item" :router_name="router_name" :comment="commentInfo" @reply_comment="replyComment" :reply_id="reply_id"/>
                </div>
                <!-- end show comment item detail -->
                <!-- show rest comments list -->
                <TransitionGroup name="fade">
                    <div v-for="comment in filteredCommentsList" :key="comment.id">
                        <CommentItem :item="item" :comment="comment" :router_name="router_name" @reply_comment="replyComment"/>
                    </div>
                </TransitionGroup>
                <div v-if="showLoadmore" class="text-center">
                    <BaseButton size="sm" @click="loadComments(page)">{{$t('View more')}}</BaseButton>
                </div>
                <!-- end show rest comments list -->
            </div>
        </div>
        <template v-else>
            <div v-if="emptyMessage" class="text-center p-base-2">{{ emptyMessage }}</div>
        </template>
    </div>
    <div :ref="`bottomCommentsListRef_${key}`"></div>
    <div class="comment-form-wrapper p-base-2 bg-white border-t border-divider dark:bg-slate-800 dark:border-white/10 rounded-b-none lg:rounded-b-base-lg" ref="commentFormRef"
        :class="screen.md ? `fixed left-0 right-0 z-[40] transition-all bottom-${hasMenuFooter ? '16' : '0'} documentScrollingDown:bottom-0` : 'relative'">
        <CommentForm ref="commentFormInputRef" @post_comment="handlePostComment" @post_reply="handlePostReply" @cancel_reply="handleCancelReply" :item="item" :type="type" :reply-comment-data="replyData" />
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useCommentStore } from '@/store/comment'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { uuidv4 } from '@/utility';
import BaseButton from '@/components/inputs/BaseButton.vue'
import CommentForm from '@/components/comments/CommentForm.vue'
import CommentItem from '@/components/comments/CommentItem.vue'
import SkeletonCommentsList from '@/components/skeletons/SkeletonCommentsList.vue'

export default {
    components: { BaseButton, CommentForm, CommentItem, SkeletonCommentsList },
    props: {
        item: {
            type: Object,
            default: null
        },
        type: {
            type: String,
            default: 'posts'
        },
        comment_id: {
            type: String,
            default: ''
        },
        reply_id: {
            type: String,
            default: ''
        },
        router_name: {
            type: String,
            default: 'post'
        },
        inModal:{
            type: Boolean,
            default: false
        },
        emptyMessage:{
            type: String,
            default: ''
        },
        hasMenuFooter: {
            type: Boolean,
            default: true
        },
        commentsListClass: {
            type: String,
            default: ''
        }
    },
    data(){
        return{
            loading_comment: true,
            showLoadmore: false,
            commentSingle: null,
            page: 1,
            replyData: {
                comment_id: null,
                user_name: null
            },
            key: uuidv4(),
            commentFormSpacing: null
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useCommentStore, ['commentsList', 'commentInfo']),
        ...mapState(useAppStore, ['screen']),
        filteredCommentsList() {
            return this.commentsList.filter(comment => comment.id != this.comment_id);
        }
    },
    watch: {
        item(){
            this.page = 1
            this.loading_comment = true
            this.loadComments(this.page);
            var replyId = (typeof this.reply_id === 'undefined') ? '' : this.reply_id
            if(this.comment_id){
                this.getCommentDetail({
                    type: this.type, 
                    itemId: this.item.id, 
                    commentId: this.comment_id, 
                    replyId
                })
            }
            this.calculateCommentHeight()
        }
    },
    mounted(){
        this.loadComments(this.page);
        var replyId = (typeof this.reply_id === 'undefined') ? '' : this.reply_id
        if(this.comment_id){
            this.getCommentDetail({
                type: this.type, 
                itemId: this.item.id, 
                commentId: this.comment_id, 
                replyId
            })
        }
        this.calculateCommentHeight()
    },
    unmounted(){
		this.resetCommentsData()
        if (!this.inModal && this.screen.md) {
            document.body.style.paddingBottom = '';
        }
	},
    methods:{
        ...mapActions(useCommentStore, ['getCommentsListByItemId', 'getCommentSingleDetail', 'resetCommentsData']),
        async loadComments(page){
            try {
                const response = await this.getCommentsListByItemId({itemType: this.type, itemId: this.item.id, page: page})
                if(response.has_next_page){
                    this.showLoadmore = true
                    this.page++;
                }else{
                    this.showLoadmore = false
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loading_comment = false
            }
        },
        setReplyData(id = null, name = null){
            this.replyData.comment_id = id
            this.replyData.user_name = name
        },
        replyComment(content, commentId, name) {
            this.$refs.commentFormInputRef.setContent(content)
            this.$refs.commentFormInputRef.setItems()
            this.setReplyData(commentId, name)
        },
        handleCancelReply(){
            this.setReplyData()
        },
        handlePostComment(){
            this.scrollToTop()
        },
        handlePostReply(){
            this.setReplyData()
        },
        async getCommentDetail(type, itemId, commentId, replyId){
            try {
                await this.getCommentSingleDetail(type, itemId, commentId, replyId)
            } catch (error) {
                this.$router.push({name: this.router_name, params: {id : this.item.id}})
            }
        },
        focusForm(){
            if(this.item.canComment){
                this.replyData.comment_id = null
                this.$refs.commentFormRef.scrollIntoView({ block: "end" });
                this.$refs.commentFormInputRef.setContent('')
            }
        },
        calculateCommentHeight(){
            this.$nextTick(() => {
                const commentHeight = this.$refs.commentFormRef.clientHeight + 1
                if(this.inModal){
                    this.commentFormSpacing = { marginBottom: `${commentHeight}px` }
                } else {
                    if(this.screen.md){
                        document.body.style.paddingBottom = `${commentHeight}px`;
                    }
                }
            })
        },
        scrollToTop(){
            this.$refs[`topCommentsListRef_${this.key}`].scrollIntoView({ block: 'center' });
        },
        scrollToBottom(){
            this.$refs[`bottomCommentsListRef_${this.key}`].scrollIntoView({ block: 'center' });
        }
    }
}
</script>