<template>
	<div class="flex story-content mx-auto select-none cursor-pointer relative">
		<StoryDetail v-if="showStory" :storyId="storyId" @set-error="setError" :isPage="true" :storiesList="[storyId]" />
		<div v-else class="flex items-center justify-center bg-gray-300 text-white text-2xl lg:rounded-base-lg w-full h-full" @click="showStory = !showStory">
			{{$t('View Story')}}
		</div>
	</div>
</template>

<script>
import StoryDetail from '@/components/stories/StoryDetail.vue'
import { mapState ,mapActions } from 'pinia'
import { useAppStore } from '../../store/app'
import { useStoriesStore } from '../../store/stories'

export default {
	data(){
		return {
			showStory: false
		}
	},
	computed:{
		...mapState(useStoriesStore, ['deleteStoryItem']),
    },
	components: { StoryDetail },
	props: ['storyId'],
	watch: {
		async deleteStoryItem(){
			this.$router.push({ name: 'home' })
		},
	},
	methods: {
		...mapActions(useAppStore, ['setErrorLayout']),
		setError(){
			this.setErrorLayout(true)
		}
	}
}
</script>