

<template>
	<div class="flex story-content mx-auto select-none cursor-pointer relative">
		<StoryItemDetailSingle v-if="showStory" @set-error="setError" :isPage="true" :storyItemId="storyItemId"/>
		<div v-else class="flex items-center justify-center bg-gray-300 text-white text-2xl lg:rounded-base-lg w-full h-full" @click="showStory = !showStory">
			{{$t('View Story')}}
		</div>
	</div>
</template>

<script>
import StoryItemDetailSingle from '@/components/stories/StoryItemDetailSingle.vue'
import { mapState ,mapActions } from 'pinia'
import { useStoriesStore } from '../../store/stories'
import { useAppStore } from '../../store/app'

export default {
	data(){
		return {
			showStory: false
		}
	},
	computed:{
		...mapState(useStoriesStore, ['deleteStoryItem']),
    },
	components: { StoryItemDetailSingle },
	props: ['storyItemId'],
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