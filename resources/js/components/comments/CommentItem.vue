<template>
    <Error v-if="error">{{error}}</Error>
    <div class="comment-item flex gap-x-base-2 group">
        <Avatar :user="comment.user"/>
        <div class="flex-1 min-w-0">
            <CommentContentType :comment="comment" />
            <div class="comment-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color dark:text-slate-400 w-full">
                <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id}}" class="text-sub-color dark:text-slate-400">{{comment.created_at}}</router-link>
                <template v-if="comment.like_count > 0">
                    &middot;
                    <button @click="openLikersModal('comments', comment.id)">{{ $filters.numberShortener(comment.like_count, $t('[number] like'), $t('[number] likes')) }}</button>
                </template>
                <template v-if="item.canComment">
                    &middot;
                    <button @click.prevent="replyComment(comment)">{{  $t('reply') }}</button>
                </template>
                <button v-if="authenticated" @click="openDropdownCommentMenu(comment)" class="visible lg:invisible lg:group-hover:visible">
                    <BaseIcon name="more_horiz_outlined" size="18"/>                          
                </button>
            </div>
            <template v-if="comment.reply_count > 0">
                <button v-if="(comment.reply_count - comment.replies.length - (replyIdData ? 1 : 0)) > 0 && showRepliesBtn" class="comment-item-view_all text-xs text-sub-color dark:text-slate-400" @click="showReplies(comment.id, page)">{{ $filters.numberShortener(comment.reply_count - comment.replies.length - (replyIdData ? 1 : 0), $t('View reply ([number])'), $t('View replies ([number])')) }}</button>
                <button v-else class="comment-item-view_all text-xs text-sub-color dark:text-slate-400" @click="hideReplies(comment.id)">{{$t('Hide replies')}}</button>
            </template>
        </div>
        <div>
            <ReactionButton 
                :subject="comment"
                subjectType="comments"
                class="comment-item-like px-base-1"
                :size="18"
            />
        </div>
    </div>
    <div v-if="comment.replies.length > 0 || (this.replyIdData && comment.reply)" class="replies-list flex flex-col gap-base-2 ps-10 mt-base-1 ms-base-2">
        <!-- At Reply Detail Page -->
        <div v-if="this.replyIdData && comment.reply" class="reply-item flex gap-x-base-2 group">
            <Avatar :user="comment.reply.user"/>
            <div class="flex-1 min-w-0">
                <CommentContentType :comment="comment.reply" subject-type="comment_replies" />
                <div class="reply-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color dark:text-slate-400">
                    <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id, reply_id: comment.reply.id}}" class="text-sub-color dark:text-slate-400">{{ comment.reply.created_at }}</router-link>
                    <template v-if="comment.reply.like_count > 0">
                        &middot;
                        <button @click="openLikersModal('comment_replies', comment.reply.id)">{{ $filters.numberShortener(comment.reply.like_count, $t('[number] like'), $t('[number] likes')) }}</button>
                    </template>
                    <template v-if="item.canComment">
                        &middot;
                        <button @click.prevent="replyComment(comment, comment.reply)">{{ $t('reply') }}</button>
                    </template>
                    <button v-if="authenticated" @click="openDropdownReplyMenu(comment, comment.reply)" class="visible lg:invisible lg:group-hover:visible">
                        <BaseIcon name="more_horiz_outlined" size="18"/>
                    </button>
                </div>
            </div>
            <div>
                <ReactionButton 
                    :subject="comment.reply"
                    subjectType="comment_replies"
                    class="reply-item-like px-base-1"
                    :size="18"
                    :params="{ comment_id: comment.id }"
                />
            </div>
        </div>
        <!-- At Comments Page -->
        <template v-if="comment.replies.length > 0">
            <TransitionGroup name="fade">
                <div v-for="reply in comment.replies" :key="reply.id" class="reply-item flex gap-x-base-2 group">
                    <Avatar :user="reply.user"/>
                    <div class="flex-1 min-w-0">
                        <CommentContentType :comment="reply" subject-type="comment_replies" />
                        <div class="reply-item-date flex flex-wrap items-center gap-base-1 text-xs text-sub-color dark:text-slate-400">
                            <router-link :to="{name: router_name, params: {id : item.id, comment_id: comment.id, reply_id: reply.id}}" class="text-sub-color dark:text-slate-400">{{reply.created_at}}</router-link>
                            <template v-if="reply.like_count > 0">
                                &middot;
                                <button @click="openLikersModal('comment_replies', reply.id)">{{ $filters.numberShortener(reply.like_count, $t('[number] like'), $t('[number] likes')) }}</button>
                            </template>
                            <template v-if="item.canComment">
                                &middot;
                                <button @click.prevent="replyComment(comment, reply)">{{ $t('reply') }}</button>
                            </template>
                            <button v-if="authenticated" @click="openDropdownReplyMenu(comment, reply)" class="visible lg:invisible lg:group-hover:visible">
                                <BaseIcon name="more_horiz_outlined" size="18"/>
                            </button>
                        </div>
                    </div>
                    <div>
                        <ReactionButton 
                            :subject="reply"
                            subjectType="comment_replies"
                            class="reply-item-like px-base-1"
                            :size="18"
                            :params="{ comment_id: comment.id }"
                        />
                    </div>
                </div>
            </TransitionGroup>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import { useAuthStore } from '@/store/auth'
import { useCommentStore } from '@/store/comment'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Error from '@/components/utilities/Error.vue'
import Avatar from '@/components/user/Avatar.vue'
import CommentOptionsMenu from '@/components/comments/CommentOptionsMenu.vue'
import ReplyOptionsMenu from '@/components/comments/ReplyOptionsMenu.vue'
import CommentContentType from '@/components/comments/CommentContentType.vue'
import ReactionButton from '@/components/utilities/ReactionButton.vue'

export default {
    components: { Error, BaseIcon, Avatar, CommentContentType, ReactionButton },
    props: ['comment', 'router_name', 'item', 'reply_id'],
    data(){
        return{           
            error: null,
            page: 1,
            replyIdData: this.reply_id,
            showRepliesBtn: true
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useCommentStore, ['repliesList'])
    },
    methods:{
        ...mapActions(useCommentStore, ['getRepliesByCommentId', 'hideRepliesList']),
        openDropdownCommentMenu(comment){
            this.$dialog.open(CommentOptionsMenu, {
                data: {
                    item: this.item,
                    comment: comment
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
        openDropdownReplyMenu(comment, reply){
            this.$dialog.open(ReplyOptionsMenu, {
                data: {
                    item: this.item,
                    comment,
                    reply
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },      
        async loadReplies(commentId, page, reply_id){
            try {
                const response = await this.getRepliesByCommentId({commentId, page, reply_id})
                if(response.has_next_page){
                    this.page++;
                    this.showRepliesBtn = true
                }else{
                    this.showRepliesBtn = false
                }
            } catch (error) {
                this.error = error
            }
        },
        showReplies(commentId, page){
            if(this.replyIdData){
                this.replyIdData = this.reply_id
            }
            this.loadReplies(commentId, page, this.replyIdData);
        },
        hideReplies(commentId){
            this.hideRepliesList(commentId)
            this.page = 1
            this.showRepliesBtn = true
            if(this.replyIdData){
                this.replyIdData = null
            }
        },
        replyComment(comment, reply = null) {
            if(reply){
                this.$emit('reply_comment', (this.user.id != reply.user.id) ? Constant.MENTION + reply.user.user_name + ' ' : '', comment.id, reply.user.name)
            }else{
                this.$emit('reply_comment', (this.user.id != comment.user.id) ? Constant.MENTION + comment.user.user_name + ' ' : '', comment.id, comment.user.name)
            }
        }
    },
    emits: ['reply_comment']
}
</script>