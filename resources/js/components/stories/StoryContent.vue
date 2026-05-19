<template>
    <div class="flex items-center absolute inset-0 bg-gray-300 w-full h-full lg:rounded-base-lg overflow-hidden">
        <div class="h-48 bg-story-detail-linear absolute top-0 inset-x-0 z-10"></div>
        <template v-if="story.type === 'text'">
            <img class="h-full w-full" :src="story.background.photo_url">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 text-center w-[70%] md:w-[80%] lg:w-[90%] max-h-[65%] overflow-x-hidden overflow-y-auto story-content-box" :style="{ 'color': story.content_color }">
                <ContentHtml class="story_content mb-base-2" :content="story.content" :limit="150" :mentions="story.mentions" :contentKey="story.id" @click_read_more="clickReadmore"/>
            </div>
        </template>
        <img v-else-if="story.type === 'photo'" class="mx-auto max-h-full" :src="story.photo_url">
        <div v-else-if="story.type === 'video'" class="flex flex-col justify-center relative w-full h-full">
            <div class="absolute inset-0">
                <div class="w-full h-full bg-cover bg-center bg-no-repeat blur-3xl scale-150" :style="{ backgroundImage: `url(${story.video.thumb.url})`}"></div>
            </div>
            <div class="flex flex-col justify-center w-full h-full z-10">
                <video
                    ref="videoStoryRef"
                    :src="story.video.file?.url"
                    :controls="false"
                    autoplay
                    playsinline
                    :muted="!enableStorySound"
                    class="max-h-full"
                    :poster="story.video.thumb.url" 
                />
            </div>
        </div>
        <audio v-if="story.song && ['text', 'photo'].includes(story.type)" ref="audioRef" :src="story.song.file_url" autoplay loop :muted="!enableStorySound"></audio>
        <div class="h-36 bg-footer-linear absolute bottom-0 inset-x-0"></div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useStoriesStore } from '@/store/stories'
import ContentHtml from '@/components/utilities/ContentHtml.vue'

export default {
    components: { 
        ContentHtml
    },
    props: {
        story: {
            type: Object,
            default: null
        }
    },
    computed: {
        ...mapState(useStoriesStore, ['enableStorySound'])
    },
    methods: {
        clickReadmore(){
            this.$emit('click_read_more')
        },
        playStory(){
            switch (this.story.type) {
                case 'text':
                case 'photo':
                    this.$refs.audioRef?.play()
                    break;
                case 'video':
                    this.$refs.videoStoryRef?.play()
                    break;
            }
        },
        pauseStory(){
            switch (this.story.type) {
                case 'text':
                case 'photo':
                    this.$refs.audioRef?.pause()
                    break;
                case 'video':
                    this.$refs.videoStoryRef?.pause()
                    break;
            }
        }
    },
    emits: ['click_read_more']
}
</script>