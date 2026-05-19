<template>
    <template v-if="item" >
        <ContentWarningWrapper :content-warning-list="item.content_warning_categories" :post="item" class="h-full rounded-none md:rounded-base-lg">
            <div class="relative pb-[100%] cursor-pointer" @click="handleOpenVibbModal">
                <div class="absolute inset-0" :id="`vibbItem-${item.id}`">
                    <VideoPlayerShort ref="vibbFeedRef" :video="vibbItem.subject" autoPlay :allow-toggle-play="false" :allow-full-screen="false" :show-progress-bar="false" :is-content-warning="isContentWarning" class="w-full h-full rounded-none md:rounded-base-lg overflow-hidden z-20" />
                    <div class="bg-footer-linear ps-3 py-3 pe-3 md:pe-20 absolute inset-x-0 bottom-0 text-white max-h-1/3 overflow-y-auto scrollbar-hidden z-20 rounded-b-none md:rounded-b-base-lg" @click.stop>
                        <div class="flex gap-base-2 items-center mb-base-1">
                            <Avatar :user="item.user" :border="false" :activePopover="false" tab="vibbs" />
                            <UserName :user="item.user" :activePopover="false" tab="vibbs" />
                        </div>
                        <ContentHtml 
                            :content="item.content" 
                            :mentions="item.mentions" 
                            :limit="100"
                            :can-translate="item.canContentTranslate"
                            :subject-id="item.id"
                            subject-type="posts"
                        />
                        <span v-if="songItem" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 mt-base-1">
                            <BaseIcon name="music_note" size="16" />
                            {{ songItem.subject.name }}
                        </span>
                    </div>
                    <div v-if="!screen.md" class="absolute bottom-3 end-3 flex flex-col gap-5 z-20">
                        <div class="flex flex-col items-center gap-1">
                            <ReactionButton 
                                :subject="item" 
                                :class="['feed-main-action-like', buttonActionStyle]"
                            />
                            <button @click.stop="openLikersModal('posts', item.id)" class="text-base-xs text-shadow-base font-semibold text-white">{{ item.like_count }}</button>
                        </div>
                        <div class="flex flex-col items-center gap-1" @click.stop="handleCommentVibb()">
                            <button :class="buttonActionStyle">
                                <BaseIcon name="message"/>
                            </button>
                            <span class="text-base-xs text-shadow-base font-semibold text-white">{{ item.comment_count }}</span>
                        </div>
                        <button class="flex flex-col items-center gap-1" @click.stop="handleOpenPostAnalytics">
                            <div :class="buttonActionStyle">
                                <BaseIcon name="eye"/>
                            </div>
                            <span class="text-base-xs text-shadow-base font-semibold text-white">{{ item.view_count }}</span>
                        </button>
                        <BookmarkButton 
                            :subject="item" 
                            :class="['feed-main-action-bookmark', buttonActionStyle]"
                        />
                        <button @click.stop="openShareModal('posts', item)" :class="buttonActionStyle">
                            <BaseIcon name="share"/>
                        </button>
                        <button v-if="authenticated" @click.stop="openDropdownMenu()" :class="buttonActionStyle">
                            <BaseIcon name="more_horiz_outlined"/>
                        </button>
                    </div>
                </div>
            </div>
        </ContentWarningWrapper>
    </template>
    <Error v-else class="mb-0">{{$t('Vibb is not found')}}</Error>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import Error from '@/components/utilities/Error.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { useVibbStore } from '@/store/vibb'
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import AnalyticsModal from '@/components/modals/AnalyticsModal.vue'
import ReactionButton from '@/components/utilities/ReactionButton.vue'
import BookmarkButton from '@/components/utilities/BookmarkButton.vue'

export default {
    components: { Error, VideoPlayerShort, ContentWarningWrapper, BaseIcon, Avatar, UserName, ContentHtml, ReactionButton, BookmarkButton },
    props: {
        item: {
            type: Object,
            default: null
        },
        showMenuAction: {
            type: Boolean,
            default: true
        }
    },
    data(){
        return{
            isContentWarning: Boolean(this.item.content_warning_categories.length && this.item.showContentWarning)
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated', 'user']),
        ...mapState(useAppStore, ['setOpenedModalCount', 'screen']),
        ...mapState(useVibbStore, ['showVibbComment']),
        buttonActionStyle(){
            return 'w-11 h-11 p-base-2 bg-white shadow-lg rounded-full dark:bg-dark-web-wash'
        },
        songItem(){
            return this.item.items[1]
        },
        vibbItem(){
            return this.item.items[0]
        }
    },
    watch:{
        item: {
            handler: function() {
                this.isContentWarning = Boolean(this.item.content_warning_categories.length && this.item.showContentWarning)
            },
            deep: true
        }
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setSelectedPage']),
        ...mapActions(useVibbStore, ['setShowVibbComment']),
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
                    post: this.item
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
        handleOpenVibbModal(){
            this.openVibb({
                vibb: this.item
            })
        },
        handleOpenPostAnalytics(){
            if(this.authenticated){
                this.setOpenedModalCount()
                this.$dialog.open(AnalyticsModal, {
                    data: {
                        post: this.item
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
        },
        handleCommentVibb(){
            this.handleOpenVibbModal()
            this.setShowVibbComment(!this.showVibbComment)
        }
    }
}
</script>