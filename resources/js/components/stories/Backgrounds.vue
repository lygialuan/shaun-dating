<template>
    <SlimScroll v-if="storyBackgrounds.length > 0" class="bg-white rounded-full dark:bg-slate-800" gap="5" padding="8">
        <div class="backgrounds-list-item" v-for="background in storyBackgrounds" :key="background.id" @click="selectBackground(background)"><img class="transition-all w-8 h-8 rounded-full cursor-pointer" :src="background.photo_url" :class="background.is_selected ? 'border-2 border-color-link scale-125 dark:border-dark-primary-color' : ''"></div>
        <template #prev>
            <button class="absolute top-1/2 -translate-y-1/2 -left-2 flex items-center justify-center w-8 h-8 bg-white rounded-full shadow-md text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-600 z-10">
                <BaseIcon name="caret_left"/>
            </button>
        </template>
        <template #next>
            <button class="absolute top-1/2 -translate-y-1/2 -right-2 flex items-center justify-center w-8 h-8 bg-white rounded-full shadow-md text-main-color dark:bg-slate-800 dark:text-white dark:shadow-slate-600 z-10">
                <BaseIcon name="caret_right"/>
            </button>
        </template>
    </SlimScroll>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useStoriesStore } from '../../store/stories'
import SlimScroll from '@/components/utilities/SlimScroll.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { SlimScroll, BaseIcon },
    computed:{
        ...mapState(useStoriesStore, ['storyBackgrounds', 'selectedBackground']),
    },
    mounted(){
		this.getStoryBackgrounds()
	},
    methods: {
        ...mapActions(useStoriesStore, ['getStoryBackgrounds', 'selectBackground'])
    }
}
</script>