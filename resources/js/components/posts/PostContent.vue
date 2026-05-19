<template>
    <div v-if="post">
        <div class="feed-item-header">
            <div class="feed-item-header-img">
                <Avatar :user="post.user"/>
            </div>
            <div class="feed-item-header-info flex-grow ps-base-2">
                <div class="feed-item-header-info-title flex justify-between items-start gap-base-2">
                    <div class="whitespace-normal">
                        <UserName :user="post.user" :truncate="false" />
                        <template v-if="post.type === 'user_page_review'">
                            <span>{{ ' ' + (post.items[0].subject?.is_recommend ? $t('recommended') : $t("doesn't recommend")) + ' ' }}</span>
                            <UserName :user="post.items[0].subject?.page" :truncate="false" />
                        </template>
                        <template v-if="post.source && !post.inSource">
                            <BaseIcon name="caret_right" size="16" class="mx-base-1 h-5" />
                            <BaseIcon name="user_group" size="16" class="me-base-1 h-5" />
                            <GroupName :group="post.source" class="break-word" />
                        </template>
                    </div>
                    <button v-if="showMenuAction && authenticated" @click="openDropdownMenu()" class="feed-item-header-button leading-none p-[2px]">
                        <BaseIcon size="20" name="more_horiz_outlined" class="feed-item-dropdown text-sub-color dark:text-slate-400"/>
                    </button>
                </div>
                <div class="feed-item-header-info-date flex flex-wrap gap-x-base-1 gap-y-1 items-center text-sub-color text-xs dark:text-slate-400">
                    <span v-if="post.type_label">{{ post.type_label }} - </span>
                    <router-link :to="{name: 'post', params: {id: post.id}}" class="text-inherit">{{post.created_at}}</router-link>
                    <span v-if="post.is_ads" class="feed-item-header-info-ads-badge bg-web-wash text-main-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-slate-400">{{ $t('Sponsored') }}</span>
                    <span v-if="post.type_box_label" class="feed-item-header-info-label-badge bg-badge-color text-primary-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-white">{{ post.type_box_label }}</span>
                    <span v-if="post.is_pin" class="feed-item-header-info-pinned-badge bg-badge-color text-main-color leading-none px-base-1 py-1 rounded-md dark:bg-dark-web-wash dark:text-white">{{ $t('Pinned') }}</span>
                </div>
            </div>
        </div>
        <div class="feed-item-content">
            <ContentHtml class="feed-item-content-message px-base-2 md:px-4 mb-base-2" 
                :content="post.content" 
                :mentions="post.mentions"
                :can-translate="post.canContentTranslate"
                :subject-id="post.id"
                subject-type="posts"
            />
            <div class="activity_feed_content_item">
                <PostContentType :post="post" />
            </div>
        </div>
        <div v-if="showCommentAction" class="feed-item-footer">
            <div class="feed-item-footer-top flex flex-wrap gap-x-base-2 gap-y-base-1 items-center">
                <div class="feed-item-like flex-1 whitespace-nowrap">
                    <button class="feed-item-like-text inline-block text-main-color dark:text-white" @click="openLikersModal('posts', post.id)">{{ $filters.numberShortener(post.like_count, $t('[number] like'), $t('[number] likes')) }}</button> &middot; 
                    <button class="feed-item-like-text inline-block text-main-color dark:text-white" @click="handleCommentCountClick()">{{ $filters.numberShortener(post.comment_count, $t('[number] comment'), $t('[number] comments')) }}</button> &middot;
                    <button class="feed-item-like-text inline-block text-main-color dark:text-white" @click="handleOpenPostAnalytics()">{{ $filters.numberShortener(post.view_count, $t('[number] view'), $t('[number] views')) }}</button>
                </div>
                <div class="feed-item-footer-top-buttons flex gap-base-1">
                    <BaseButton v-if="post.canShareEarn" @click="openShareModal('posts', post)" size="xs">{{ $t('Share to Earn') }}</BaseButton>
                    <BaseButton v-if="post.canBoot" @click="boostPost" size="xs">{{ $t('Boost Post') }}</BaseButton>
                </div>
            </div>
            <div class="feed-item-footer-bottom feed-main-action flex gap-base-2 justify-between py-base-1 border-t border-divider dark:border-white/10">
                <ReactionButton 
                    :subject="post" 
                    :class="['feed-main-action-like', postActionItemClass]"
                >
                    <template v-slot:text><div class="hidden md:block">{{ $t('Like') }}</div></template>
                </ReactionButton>
                <button v-if="post.canComment" @click="handleCommentClick()" :class="postActionItemClass">
                    <BaseIcon name="comment" />
                    <div class="hidden md:block">{{ $t('Comment') }}</div>
                </button>
                <button v-if="post.canShare" @click="openShareModal('posts', post)" :class="postActionItemClass">
                    <BaseIcon name="share" />
                    <div class="hidden md:block">{{ $t('Share') }}</div>
                </button>
                <BookmarkButton
                    :subject="post" 
                    :class="['feed-main-action-bookmark', postActionItemClass]"
                >
                    <template v-slot:text><div class="hidden md:block">{{ $t('Bookmark') }}</div></template>
                </BookmarkButton>
            </div>
        </div>  
    </div>
    <Error v-else class="!mb-0">{{$t('Post is not found')}}</Error>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import PostContentType from '@/components/posts/PostContentType.vue'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Error from '@/components/utilities/Error.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import AnalyticsModal from '@/components/modals/AnalyticsModal.vue'
import GroupName from '@/components/group/GroupName.vue'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '../../store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { switchPage } from '@/api/page'
import ReactionButton from '@/components/utilities/ReactionButton.vue'
import BookmarkButton from '@/components/utilities/BookmarkButton.vue'

export default {
    components: { ContentHtml, PostContentType, BaseIcon, Error, UserName, Avatar, BaseButton, GroupName, ReactionButton, BookmarkButton },
    props: {
        post: {
            type: Object,
            default: null
        },
        showCommentAction: {
            type: Boolean,
            default: true
        },
        showMenuAction: {
            type: Boolean,
            default: true
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated', 'user']),
        ...mapState(useAppStore, ['config', 'setOpenedModalCount']),
        postActionItemClass(){
            return 'feed-main-action-item flex-1 flex gap-2 items-center justify-center px-2 py-base-1 rounded-md transition outline-none hover:bg-light-web-wash dark:hover:bg-dark-web-wash'
        }
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        openShareModal(type, subject){
            if(this.authenticated){
                this.setOpenedModalCount()
                this.$dialog.open(ShareOptionsMenu, {
                    data: {
                        subjectType: type,
                        subject: subject
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
                        this.setOpenedModalCount(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
        },
        openDropdownMenu(){
            this.setOpenedModalCount()
            this.$dialog.open(PostOptionsMenu, {
                data: {
                    post: this.post
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
                    this.setOpenedModalCount(false)
                }
            });
        },
        handleCommentClick(){
            if(this.authenticated){
                this.$emit('comment_click')
            }else{
                this.showRequireLogin()
            }
        },
        boostPost() {
            if (this.user.id == this.post.user.id) {
                this.$router.push({ 'name': 'advertising_boost_post' ,params: {id : this.post.id}})
            } else {
                this.setSelectedPage(this.post.user)
                setTimeout(async() => {
                    try {
                        await switchPage(this.post.user.id)
                        let url = this.$router.resolve({
                            name: 'advertising_boost_post',
                            params: {id : this.post.id}
                        });
                        window.location.href = window.siteConfig.siteUrl + url.fullPath
                        
                    } catch (error) {
                        this.showError(error.error)
                        this.setSelectedPage(null)
                    }
                }, 1500);
            }
        },
        handleCommentCountClick(){
            if(this.authenticated){
                this.$emit('comment_count_click')
            }else{
                this.showRequireLogin()
            }
        },
        handleOpenPostAnalytics(){
            if(this.authenticated){
                this.setOpenedModalCount()
                this.$dialog.open(AnalyticsModal, {
                    data: {
                        post: this.post
                    },
                    props:{
                        header: this.$t('Post Analytics'),
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    onClose: () => {
                        checkPopupBodyClass();
                        this.setOpenedModalCount(false)
                    }
                })
            }else{
                this.showRequireLogin()
            }
        }
    },
    emits: ['comment_click', 'comment_count_click']
}
</script>