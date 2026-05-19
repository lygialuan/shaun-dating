<template>
    <template v-if="item">
        <div ref="vibbItemRef" class="vibb-wrapper flex items-center w-full min-h-full md:py-base-2" :id="[ `vibbItem-${item.id}`]">
            <div class="h-full md:h-auto max-h-full w-full relative md:rounded-md mx-auto md:translate-x-8" :style="{ backgroundColor: vibbItem.subject.thumb.params.dominant_color, aspectRatio: screen.md ? 'auto' : '0.5625 / 1', maxWidth: screen.md ? '100%' : `${vibbItemWidth}px` }">
                <ContentWarningWrapper :content-warning-list="item.content_warning_categories" :post="item" class="h-full md:rounded-md">
                    <template #hide-button="{ toggle }">
                        <button 
                            class="flex justify-center items-center p-3 bg-black-trans-6 text-white rounded-full absolute end-3 z-30"
                            :class="screen.md ? 'top-16' : 'top-3'"
                            @click.stop="toggle"
                        >
                            <BaseIcon name="eye_slash" />
                        </button>
                    </template>
                    <VideoPlayerShort ref="vibbVideoRef" :video="vibbItem.subject" reload alwaysPlay :allow-pip="false" :is-content-warning="isContentWarning" :action-offset-y="screen.md ? 64 : 12" @in-viewport="handleSetCurrentVibb" class="w-full h-full" />
                    <div class="bg-footer-linear ps-3 py-3 pe-20 lg:pe-3 absolute inset-x-0 bottom-0 text-white z-20 max-h-1/2 overflow-y-auto scrollbar-hidden rounded-b-none md:rounded-b-md">
                        <div class="flex gap-base-2 items-center">
                            <Avatar :user="item.user" :border="false" :activePopover="false" tab="clips" />
                            <UserName :user="item.user" :activePopover="false" tab="clips" />
                        </div>
                        <ContentHtml 
                            :content="item.content" 
                            :mentions="item.mentions" 
                            :limit="100"
                            :can-translate="item.canContentTranslate"
                            :subject-id="item.id"
                            subject-type="posts"
                            class="mt-base-1"
                        />
                        <span v-if="songItem" class="bg-black text-white px-2 py-1 text-xs rounded-lg inline-flex items-center gap-1 mt-base-1">
                            <BaseIcon name="music_note" size="16" />
                            {{ songItem.subject.name }}
                        </span>
                    </div>
                    <div class="vibb-main-action absolute flex flex-col gap-base-2 md:gap-5 z-20" :class="screen.lg ? 'bottom-4 end-4' : 'bottom-0 -end-16'">
                        <div class="vibb-main-action-item flex flex-col items-center gap-2">
                            <ReactionButton 
                                :subject="item" 
                                :class="['vibb-main-action-like', buttonActionStyle]"
                            />
                            <button @click.stop="openLikersModal('posts', item.id)" class="vibb-main-action-item-text text-base-xs text-shadow-base font-semibold text-white leading-none">{{ item.like_count }}</button>
                        </div>
                        <div class="vibb-main-action-item flex flex-col items-center gap-2">
                            <button @click="handleToggleComment()" :class="buttonActionStyle">
                                <BaseIcon name="message"/>
                            </button>
                            <span class="vibb-main-action-item-text text-base-xs text-shadow-base font-semibold text-white leading-none">{{ item.comment_count }}</span>
                        </div>
                        <div class="vibb-main-action-item">
                            <button class="flex flex-col items-center gap-2" @click="handleOpenPostAnalytics">
                                <div :class="buttonActionStyle">
                                    <BaseIcon name="eye"/>
                                </div>
                                <span class="vibb-main-action-item-text text-base-xs text-shadow-base font-semibold text-white leading-none">{{ item.view_count }}</span>
                            </button>
                        </div>
                        <div class="vibb-main-action-item">
                            <BookmarkButton 
                                :subject="item" 
                                :class="['vibb-main-action-bookmark', buttonActionStyle]"
                            />
                        </div>
                        <div class="vibb-main-action-item">
                            <button @click="openShareModal('posts', item)" :class="buttonActionStyle">
                                <BaseIcon name="share"/>
                            </button>
                        </div>
                        <div class="vibb-main-action-item">
                            <button v-if="authenticated" @click="openDropdownMenu()" :class="buttonActionStyle">
                                <BaseIcon name="more_horiz_outlined"/>
                            </button>
                        </div>
                        <Avatar v-if="!screen.lg" :user="item.user" :activePopover="false" :border="false" :size="44" tab="vibbs" />
                    </div>
                </ContentWarningWrapper>
            </div>
        </div>
    </template>
    <div v-else ref="vibbItemRef" class="vibb-wrapper min-h-full w-full py-0 md:py-base-2">
        <div class="h-full mx-auto bg-web-wash dark:bg-dark-web-wash md:rounded-md" :style="{ aspectRatio: screen.md ? 'auto' : '0.5625 / 1', maxWidth: screen.md ? '100%' : `${vibbItemWidth}px` }"></div>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from "@/store/app"
import { useVibbStore } from "@/store/vibb"
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import VideoPlayerShort from '@/components/utilities/VideoPlayerShort.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import PostOptionsMenu from '@/components/posts/PostOptionsMenu.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import AnalyticsModal from '@/components/modals/AnalyticsModal.vue'
import ReactionButton from '@/components/utilities/ReactionButton.vue'
import BookmarkButton from '@/components/utilities/BookmarkButton.vue'

export default {
    components: { VideoPlayerShort, BaseIcon, Avatar, UserName, ContentHtml, ContentWarningWrapper, ReactionButton, BookmarkButton },
    props: {
        item: {
            type: Object,
            default: null
        }
    },
    data(){
        return{
            isContentWarning: Boolean(this.item?.content_warning_categories.length && this.item?.showContentWarning),
            vibbItemWidth: 1
        }
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        ...mapState(useAppStore, ['screen', 'openedModalCount', 'setOpenedModalCount']),
        ...mapState(useVibbStore, ['showVibbComment']),
        vibbItem(){
            return this.item.items[0]
        },
        songItem(){
            return this.item.items[1]
        },
        buttonActionStyle(){
            return 'vibb-main-action-item-icon flex items-center justify-center w-10 h-10 md:w-11 md:h-11 shadow-lg bg-white rounded-full dark:bg-dark-web-wash'
        }
	},
    watch:{
        item: {
            handler: function() {
                this.isContentWarning = Boolean(this.item.content_warning_categories.length && this.item.showContentWarning)
            },
            deep: true
        },
        openedModalCount(newVal, oldVal){
            if(oldVal === 2 && newVal === 1){
                this.$refs.vibbVideoRef.handlePlayVisibleItem()
            }
        }
    },
    mounted(){
        this.calculateVibbItemWidth();
        window.addEventListener('resize', this.calculateVibbItemWidth);
    },
    unmounted(){
        window.removeEventListener('resize', this.calculateVibbItemWidth);
        this.setCurrentVibb(null)
    },
    methods: {
        ...mapActions(useVibbStore, ['setCurrentVibb', 'setShowVibbComment']),
        handleToggleComment(){
            this.setShowVibbComment(!this.showVibbComment)
        },
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
        calculateVibbItemWidth() {
            this.$nextTick(() => {
                const el = this.$refs.vibbItemRef;
                const styles = window.getComputedStyle(el);
                const paddingTop = parseFloat(styles.paddingTop) || 0;
                const paddingBottom = parseFloat(styles.paddingBottom) || 0;
                const elHeightWithoutPadding = el.clientHeight - paddingTop - paddingBottom;
                if (el && elHeightWithoutPadding) {
                    const newWidth = elHeightWithoutPadding * (9 / 16);
                    this.vibbItemWidth = newWidth;
                }
            });
        },
        handleSetCurrentVibb(){
            this.setCurrentVibb(this.item)
            let vibbUrl = this.$router.resolve({
                name: 'vibb',
                query: { 'id': this.item.id}
            });
            changeUrl(vibbUrl.fullPath, false)
        }
    }
}
</script>