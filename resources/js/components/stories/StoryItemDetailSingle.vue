<template>
    <div v-if="story" class="flex flex-col h-full w-full select-none relative">
        <div class="absolute top-4 left-2 right-2 lg:left-4 lg:right-4 flex gap-1 z-30">
            <div class="flex-1 bg-gray-400 rounded-full story-remaining-bar">
                <div class="rounded-full h-1 w-full bg-progress story-progress-bar" :style="{'width': (time * 100 / storyTimeout) +'%'}"></div>
            </div>
        </div>
        <StoryItemDetail :canMessage="story.canMessage" :story="story.item" :owner="story.user" :runningStory="run" :isPage="isPage" @play_story="doPlayStory" @pause_story="doPauseStory" @close_story_modal="$emit('closeModal')" />        
    </div>
	<Loading v-else class="mx-auto"/>
</template>

<script>
import { mapState } from 'pinia'
import { getStorySingleDetail } from '@/api/stories'
import Loading from '@/components/utilities/Loading.vue'
import StoryItemDetail from '@/components/stories/StoryItemDetail.vue'
import { useAppStore } from '../../store/app'

export default {
	components: { Loading, StoryItemDetail },
    props: {
        storyItemId: {
            default: ''
        },
        isPage: {
            default: false
        }
    },
    data() {    
        return {
            id: this.storyItemId,
            run: false,
            time: 0,
            story: null
		}
	},
    computed: {
		...mapState(useAppStore, ['config']),
        storyTimeout() {
            if (this.story.item && this.story.item.type === 'video' && this.story.item.video.duration) {
                return this.story.item.video.duration;
            }
            return this.config.story.timeout;
        }
	},
    watch: {
        story(newStoryItem) {
            if (newStoryItem) {
                this.time = 0
                this.doPlayStory()          
            }
        },
        time(newTime) {
            if (parseInt(newTime) == this.storyTimeout) {
                clearInterval(this.interval)
                this.run = false
            }
        }
    },
    mounted(){
        this.getStoryDetail()
    },
    methods: {
        async getStoryDetail(){
            try {
                this.story = await getStorySingleDetail(this.id)
            } catch (error) {
                this.$emit('setError')
                if (! this.isPage) {
                    this.showError(error.error)
                    this.$emit('closeModal')
                }
            }
        },
        doPlayStory(){
            if (parseInt(this.time) >= this.storyTimeout ) {
                this.time = 0
            }
            this.run = true
            if (this.interval != null) {
                clearInterval(this.interval)
            }
            this.interval = setInterval(() => {
                this.time+=0.01
            }, 10)
        },
        doPauseStory(){
            clearInterval(this.interval)
            this.interval = null
            this.run = false
        }
    }
}
</script>