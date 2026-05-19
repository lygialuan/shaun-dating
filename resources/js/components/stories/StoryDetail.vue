<template>
    <div>
        <div class="absolute top-3 start-4 z-[1001]">
            <button @click="closeModal">
                <BaseIcon name="arrow_left" size="20" />
            </button>
        </div>
    </div>
    <div v-if="story" class="flex items-center justify-center min-h-[100svh] p-2 md:p-0">
        <div class="relative w-full max-w-[420px] sm:max-w-[500px] md:max-w-[550px] h-[clamp(480px,90dvh,835px)] mt-6 md:mt-0 select-none">
            <div class="h-full w-full relative">
                <div class="story-progress absolute top-4 inset-x-base-2 lg:inset-x-4 flex gap-1 z-30">
                    <div v-for="item in story.items" :key="item.id" class="flex-1 bg-dark-gray rounded-full story-remaining-bar">
                        <div v-if="storyItem.id > item.id" class="rounded-full h-base-1 w-full bg-primary-color story-progress-bar"></div>
                        <div v-if="storyItem.id == item.id" class="rounded-full h-base-1 w-full bg-progress story-progress-bar" :style="{'width': (time * 100 / storyTimeout) +'%'}"></div>
                    </div>
                </div>
                <StoryItemDetail :canMessage="story.canMessage" :story="storyItem" :owner="story.user" :runningStory="run" :isPage="isPage" @play_story="doPlayStory" @pause_story="stopPlayStory" @close_story_modal="$emit('closeModal')" />
            </div>
            <button v-if="showPrev" @click="prevStories" class="story-controls story-controls-prev absolute top-1/2 -translate-y-1/2 ltr:left-base-2 ltr:lg:-left-20 rtl:right-base-2 rtl:lg:-right-20 z-10 flex items-center justify-center w-10 h-10 bg-black-trans-4 md:bg-white rounded-full shadow-md text-white md:text-main-color dark:bg-black-trans-4 dark:md:bg-dark-web-wash dark:text-white"><BaseIcon :name="user.rtl ? 'caret_right' : 'caret_left'"/></button>
            <button v-if="showNext" @click="nextStories" class="story-controls story-controls-next absolute top-1/2 -translate-y-1/2 ltr:right-base-2 ltr:lg:-right-20 rtl:left-base-2 rtl:lg:-left-20 z-10 flex items-center justify-center w-10 h-10 bg-black-trans-4 md:bg-white rounded-full shadow-md text-white md:text-main-color dark:bg-black-trans-4 dark:md:bg-dark-web-wash dark:text-white"><BaseIcon :name="user.rtl ? 'caret_left' : 'caret_right'"/></button>
        </div>
    </div>
    <Skeleton v-else height="100%" width="100%" class="!rounded-none lg:!rounded-base-lg"/>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getStoryDetail, storyViewItem } from '@/api/stories'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ViewersModal from '@/components/stories/ViewersModal.vue'
import StoryItemDetail from '@/components/stories/StoryItemDetail.vue'
import { useAppStore } from '../../store/app'
import { useStoriesStore } from '../../store/stories'
import { useAuthStore } from '../../store/auth'
import Skeleton from "primevue/skeleton";

export default {
	components: { BaseIcon, StoryItemDetail, Skeleton },
    props: {
        storyId: {
            default: ''
        },
        storiesList: {
            default: []
        },
        isPage: {
            default: false
        }
    },
    data() {    
        return {
            id: this.storyId,
			story: null,
            storyItem: null,
            showPrev: false,
            showNext: false,
            interval: null,
            time: 0,
            run: false
		}
	},
    watch: {
        storyItem(newStoryItem) {
            if (newStoryItem) {
                if (this.authenticated) {
                    storyViewItem({
                        'id' : newStoryItem.id
                    })
                    var index = window._.findIndex(this.story.items, function(item) { 
                        return item.id == newStoryItem.id; 
                    });
                    var seen = (index == (this.story.items.length - 1))
                    this.setSeenStoryItem({'storyItem' : seen ? this.story.items[0] : this.story.items[index + 1], 'seen': seen})
                }
                this.time = 0;
                this.doPlayStory()
            }
        },
        time(newTime) {
            if (parseInt(newTime) == this.storyTimeout) {
                clearInterval(this.interval)
                this.interval = null
                this.run = false
                if (this.showNext) {
                    this.nextStories()
                } else {
                    if (! this.isPage) {
                        this.$emit('closeModal');
                        this.storyItem = null
                    }
                }
            }
        },
        deleteStoryItem(){
            if(this.story.items && window._.find(this.story.items, {id: this.deleteStoryItem.id})){
                this.story.items = this.story.items.filter(story => story.id !== this.deleteStoryItem.id)
                if (this.showPrev) {
                    this.prevStories()
                }
                if (this.showNext) {
                    this.nextStories()
                }              
            }
            if(this.storyItem && this.storyItem.id == this.deleteStoryItem.id){
                this.$emit('closeModal');
                this.storyItem = null
            }
        },
        hasLayerModal(newValue){
            if(newValue){
                window.removeEventListener('keydown', this.onKeyDown)
            } else {
                window.addEventListener('keydown', this.onKeyDown)
            }
        }
    },
	mounted() {
        if(this.id){
            this.loadStoryDetail(this.id)
            window.addEventListener('keydown', this.onKeyDown)
        }
    },
    unmounted() {
        window.removeEventListener('keydown', this.onKeyDown)
    },
    computed: {
		...mapState(useAppStore, ['config']),
        ...mapState(useStoriesStore, ['deleteStoryItem', 'hasLayerModal']),
        ...mapState(useAuthStore, ['user', 'authenticated']),
        storyTimeout() {
            if (this.storyItem && this.storyItem.type === 'video' && this.storyItem.video.duration) {
                return this.storyItem.video.duration;
            }
            return this.config.story.timeout;
        }
	},
    methods: {        
        ...mapActions(useStoriesStore, ['setSeenStoryItem']),
        async loadStoryDetail(storyId){
            try {             
                this.story = await getStoryDetail(storyId);
                var self = this;
                this.storyItem = window._.find(this.story.items, function(item) {
                    return item.id == self.story.item_view_id;
                });
				this.checkShowButton();
            } catch (error) {
                this.$emit('setError')
                if (! this.isPage) {
                    this.showError(error.error)
                }
            }
        },
        prevStories(){
            var self = this;
            var item = window._.findLast(this.story.items, function(item) { 
                return item.id < self.storyItem.id; 
            });

            if (item) {
                this.storyItem = item;
                this.checkShowButton()
            } else {
                var index = this.getIndexStory();
                var story = this.storiesList[index - 1];

                this.id = story.id;
                this.loadStoryDetail(this.id);

                let storyUrl = this.$router.resolve({
                    name: 'story_view',
                    params: { 'storyId': this.id }
                });
                changeUrl(storyUrl.fullPath)
            }
        },
        nextStories(){
            var self = this;
            var item = window._.find(this.story.items, function(item) { 
                return item.id > self.storyItem.id; 
            });

            if (item) {
                this.storyItem = item;
                this.checkShowButton()
            } else {
                var index = this.getIndexStory();
                var story = this.storiesList[index + 1];
                
                this.id = story.id;
                this.loadStoryDetail(this.id);

                let storyUrl = this.$router.resolve({
                    name: 'story_view',
                    params: { 'storyId': this.id }
                });
                changeUrl(storyUrl.fullPath)
            }
        },
        checkShowButton(){
            var self = this;

            var index = window._.findIndex(this.story.items, function(item) { 
                return item.id == self.storyItem.id; 
            });
            
            if (index == 0 && this.story.items.length == 1) {
                this.checkPrev()
                this.checkNext()
            } else {
                if (index == 0) {
                    this.checkPrev()
                    this.showNext = true;
                } else if (index == this.story.items.length - 1)  {
                    this.checkNext()
                    this.showPrev = true;
                } else {
                    this.showPrev = true;
                    this.showNext = true;
                }
            }
        },
        checkPrev()
        {
            var index = this.getIndexStory()
            if (index < 1) {
                this.showPrev = false;
            } else {
                this.showPrev = true;
            }
        },
        checkNext()
        {
            var index = this.getIndexStory()
            if (this.storiesList.length > 1 && index < this.storiesList.length - 1) {                
                this.showNext = true;
            } else {
                this.showNext = false;
            }
        },
        getIndexStory()
        {
            var self = this
            return window._.findIndex(this.storiesList, function(story) { 
                return story.id == self.id; 
            });
        },
        doPlayStory(){
            if (parseInt(this.time) >= this.storyTimeout ) {
                this.time = 0                
                this.storyItem = this.story.items[0]
                this.checkShowButton()
            }
            this.run = true
            if (this.interval != null) {
                clearInterval(this.interval)
            }
            this.interval = setInterval(() => {
                this.time += 0.1
            }, 100)
        },
        stopPlayStory(){
            clearInterval(this.interval)
            this.interval = null
            this.run = false
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
                    this.doPlayStory()
                }
            });
            this.stopPlayStory()
        },
        onKeyDown (e){
			switch(e.key){
                case 'ArrowLeft':
                    if(this.user.rtl){
                        this.showNext && this.nextStories()
                    }else{
                        this.showPrev && this.prevStories()
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    break;
                case 'ArrowRight':
                    if(this.user.rtl){
                        this.showPrev && this.prevStories()
                    }else{
                        this.showNext && this.nextStories()
                    }
                    e.stopPropagation();
                    e.preventDefault();
                    break;
                default:
                    break;
            }
		},
        closeModal() {
            this.$emit('closeModal');
        },
    }
}
</script>