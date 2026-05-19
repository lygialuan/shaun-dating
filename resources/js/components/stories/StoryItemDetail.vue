<template>
    <div v-if="story" class="story-content flex h-full w-full lg:rounded-base-lg relative">
        <div class="story-content-header absolute top-8 inset-x-base-2 lg:inset-x-4 z-30">
            <div class="flex justify-between items-center gap-base-2">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-base-2">
                        <Avatar :user="owner" :activePopover="false" :border=false />
                        <div class="flex-1 min-w-0 text-white">
                            <UserName :user="owner" :activePopover="false" class="story-name min-w-0"/>
                            <div class="story-date text-xs truncate">{{ story.created_at }}</div>
                            <span v-if="story.song" class="text-xs inline-flex items-center gap-1 w-full">
                                <BaseIcon name="music_note" size="16" />
                                <div class="truncate">{{ story.song.name }}</div>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2 text-white story-icon">
                    <button v-if="run" @click="doPauseStateStory">
                        <BaseIcon name="pause" />
                    </button>
                    <button v-else @click="doPlayStateStory">
                        <BaseIcon name="play" />
                    </button>
                    <button v-if="enableStorySound" @click="doMuteSong" v-tooltip.bottom="isMobile ? '' : $t('Mute')">
                        <BaseIcon name="speaker_high" />
                    </button>
                    <button v-else @click="doUnmuteSong" v-tooltip.bottom="isMobile ? '' : $t('Unmute')">
                        <BaseIcon name="speaker_none" />
                    </button>
                    <button v-if="user.id" @click="openDropdownMenu">
                        <BaseIcon name="more_horiz_outlined" />
                    </button>
                    <button v-if="!isPage" class="block lg:hidden" @click="closeStoryModal">
                        <BaseIcon name="close" />
                    </button>           
                </div>
            </div>        
        </div>
        <StoryContent ref="storyContentRef" :story="story" class="story-content-body text-2xl md:text-3xl font-semibold" @click_read_more="doPauseStateStory"/>
        <button v-if="owner && user.id == owner.id" class="story-viewers border-b border-white pb-1 flex items-center text-xs text-white whitespace-nowrap absolute bottom-4 start-4 z-10" @click="openViewersModal(story.id)"><BaseIcon name="eye" size="16" class="me-1"/>{{ $filters.numberShortener(story.count, $t('[number] viewer'), $t('[number] viewers')) }}</button>     
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility/index'
import { storeMessages } from '@/api/stories'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useStoriesStore } from '@/store/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import StoryOptionsMenu from '@/components/stories/StoryOptionsMenu.vue'
import ViewersModal from '@/components/stories/ViewersModal.vue'
import StoryContent from '@/components/stories/StoryContent.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import PermissionModal from '@/components/modals/PermissionModal.vue';

export default {
    components: { BaseIcon, StoryContent, Avatar, UserName },
    props: {
        story: {
            type: Object,
            default: null
        },
        owner: {
            type: Object,
            default: null,
        },
        runningStory: {
            type: Boolean,
            default: false
        },
        canMessage: {
            type: Boolean,
            default: false
        },
        isPage: {
            type: Boolean,
            default: false
        },
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {    
        return {
            run: this.runningStory,            
            message_content: null,
            runState: this.runningStory,
            emojiOpened: false,
            focusMessage: false
		}
	},
    watch: {
        time(newTime) {
            if (parseInt(newTime) == this.storyTimeout) {
                this.run = false
            }
        },
        runningStory(){
            this.run = this.runningStory
            if(!this.run){
                this.stopPlayStory()
            }
        },
        story() {
            this.runState = this.runningStory
            this.$refs.story_message?.resetContent()
        },
        focusMessage(){
            if(this.focusMessage){
                this.stopPlayStory()
            }else{
                if(this.emojiOpened){
                    this.stopPlayStory()
                }else{
                    this.doPlayStory()
                }
            }
        },
        emojiOpened(){
            if(this.emojiOpened){
                this.stopPlayStory()
            }else{
                if(this.focusMessage){
                    this.stopPlayStory()
                }else{
                    this.doPlayStory()
                }
            }
        },
        hasLayerModal(newVal){
            if(newVal){
                this.doPauseStateStory();
            } else {
                this.doPlayStateStory();
            }
        }
    },
    computed: {
		...mapState(useAppStore, ['config', 'isMobile', 'screen']),
        ...mapState(useAuthStore, ['user']),
        ...mapState(useStoriesStore, ['enableStorySound', 'hasLayerModal']),
        storyTimeout() {
            if (this.story && this.story.type === 'video' && this.story.video.duration) {
                return this.story.video.duration;
            }
            return this.config.story.timeout;
        }
	},
    mounted(){
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
    },
    unmounted() {
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
    },
    methods: {
        ...mapActions(useStoriesStore, ['setEnableStorySound', 'setHasLayerModal']),
        doPlayStory(){
            if (! this.runState) {
                return 
            }
            this.run = true
            this.$refs.storyContentRef.playStory()
            this.$emit('play_story')
        },
        stopPlayStory(){
            this.run = false
            this.$refs.storyContentRef.pauseStory()
            this.$emit('pause_story')
        },
        doUnmuteSong(){
            this.setEnableStorySound(true)
        },
        doMuteSong(){
            this.setEnableStorySound(false)
        },
        openDropdownMenu(){
            this.$dialog.open(StoryOptionsMenu, {
                data: {
                    story: this.story
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
                    this.setHasLayerModal(false);
                }
            });
            this.setHasLayerModal(true);
        },
        openViewersModal(id){
            this.$dialog.open(ViewersModal, {
                data: {
                    itemId: id
                },
                props:{
                    header: this.$t('Viewers'),
                    class: 'likers-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                    this.setHasLayerModal(false);
                }
            });
            this.setHasLayerModal(true);
        },
        addEmoji(emoji){		
			this.$refs.story_message.addContent(emoji)
		},
        async sendStoryMessage(storyId){
            let permission = 'chat.allow'
            if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
                this.$dialog.open(PermissionModal, {
                    props:{
                        header: this.$t('Permission'),
                        modal: true,
                        draggable: false
                    },
                    data: {
                        permission: permission
                    },
                    onClose: () => {
                        this.doPlayStateStory();
                    }
                })
                this.$refs.story_message.setContent('')
                this.$refs.story_message.blurTextarea()
                this.$refs.emojiPickerRef.close()
                this.doPauseStateStory()
                return
            }

            try {
                if (this.message_content.trim() == '') {
                    return
                }

                await storeMessages({
                    id: storyId,
                    content: this.message_content
                })
                this.$refs.story_message.setContent('')
                this.$refs.story_message.blurTextarea()
                this.$refs.emojiPickerRef.close()
                this.showSuccess(this.$t('Your message has been sent.'))
                this.doPlayStateStory()
            } catch (error) {
                this.showError(error.error)
            }
        },
        doPlayStateStory(){
            this.runState = true
            this.doPlayStory()
        },
        doPauseStateStory(){
            this.runState = false
            this.stopPlayStory()
        },
        closeStoryModal(){
            this.$emit('close_story_modal')
        },
        handleOpenEmoji(){
            this.emojiOpened = true
        },
        handleCloseEmoji(){
            this.emojiOpened = false
        },
        handleFocusMessage(){
            setTimeout(() => {
                this.focusMessage = true
            }, 200);
        },
        handleFocusoutMessage(){
            setTimeout(() => {
                this.focusMessage = false
            }, 200);
        },
        handleVisibilityChange() {
            if (document.hidden) {
                this.stopPlayStory();
            }
        }
    },
    emits: ['play_story', 'pause_story', 'close_story_modal']
}
</script>