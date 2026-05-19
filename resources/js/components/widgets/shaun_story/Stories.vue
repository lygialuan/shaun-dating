<template>
	<SlimScrollStory>
		<div class="flex gap-2 md:gap-4 mb-4 mt-4">
			<div @click="createStory()" class="cursor-pointer">
				<div class="flex flex-col items-center">
					<div class="w-[100px] h-[100px] rounded-full border-4 border-dark-primary-color flex items-center justify-center bg-black text-white text-3xl">
						<BaseIcon name="plus" class="text-white"/>
					</div>
					<span class="text-sm mt-2">{{$t('Add story')}}</span>
				</div>
			</div>
			<div class="stories-list-scroll w-[100px] h-[100px]" v-for="story in storiesList" :key="story.id" @click="showStoryDetail(story.id)">
				<div class="flex flex-col items-center cursor-pointer">
					<div class="rounded-full border-4" :class="story.seen ? 'border-gray-300 dark:border-slate-600' : 'border-dark-primary-color'">
						<Avatar :user="story.user" :activePopover="false" :border="false" :router="false" :size="92"/>
					</div>
					<div class="text-sm md:text-base truncate max-w-full mt-2">
						<UserName :user="story.user" :activePopover="false" class="text-sm !font-normal" :router="false"/>
					</div>
				</div>
			</div>
		</div>
		<InfiniteLoading @infinite="loadMoreStories">
			<template #spinner><span></span></template>
			<template #complete><span></span></template>
		</InfiniteLoading>
	</SlimScrollStory>			
</template>

<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import { getStories, getStoryDetailInList } from '@/api/stories'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import StoryDetailModal from '@/components/stories/StoryDetailModal.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import { useAuthStore } from '../../../store/auth'
import { useStoriesStore } from '../../../store/stories'
import { useActionStore } from '../../../store/action'
import { useAppStore } from '../../../store/app'
import SlimScrollStory from '@/components/utilities/SlimScrollStory.vue'
import { useUserStore } from '@/store/user'

export default {
	components: { BaseIcon, InfiniteLoading, Avatar, UserName, SlimScrollStory },
	props: ['data', 'params', 'position'],
	data(){
		return {
			storiesList: [],
			currentPage: 1
		}
	},
	computed:{
        ...mapState(useAuthStore, ['user', 'authenticated'])	,
		...mapState(useStoriesStore, ['seenStoryItem', 'deleteStoryItem']),
		...mapState(useActionStore, ['samePage']),
		...mapState(useAppStore, ['setOpenedModalCount']),
		...mapState(useUserStore, ['filterParams']),
    },
	mounted(){
		this.getStoriesList(this.currentPage)
	},
	watch: {
		seenStoryItem(seenStoryItemNew){
			var story = window._.find(this.storiesList, {id: seenStoryItemNew.storyItem.story_id})
			if (story) {
				if (! story.seen && story.item.id != seenStoryItemNew.storyItem.id) {
					story.item = seenStoryItemNew.storyItem
				}

				if (! story.seen && seenStoryItemNew.seen) {
					story.seen = true
				}
			}
		},
		async deleteStoryItem(storyItem){
			var index = window._.findIndex(this.storiesList, function(story) { 
                return story.id == storyItem.storyId; 
            });

			if (index == -1) {
				return
			}

			try {
				var story = await getStoryDetailInList(storyItem.storyId)
				this.storiesList[index] = story
			} catch (error) {				
				this.storiesList = this.storiesList.filter(story => story.id !== storyItem.storyId)
			}
		},
		samePage(){
			this.resetStories()
		},
		filterParams: {
			deep: true,
			handler(value) {
				this.filterParams = value
				this.resetStories()
			}
		}
	},
	methods: {
		async getStoriesList(page){
			if (! this.authenticated) {
				return
			}
			try {
				let pushedStoriessList = await getStories(page, this.filterParams)
				if (page == 1) {
					this.storiesList = [];
				}			
				this.storiesList = window._.concat(this.storiesList, pushedStoriessList)
				return pushedStoriessList
			} catch (error) {
				this.showError(error.error)
			}
		},
		showStoryDetail(storyId){
			let storyUrl = this.$router.resolve({
                name: 'story_view',
                params: { 'storyId': storyId }
            });
            changeUrl(storyUrl.fullPath)
			this.setOpenedModalCount()
            this.$dialog.open(StoryDetailModal, {			
                data: {
                    id: storyId,
					storiesList: this.storiesList
                },
                props:{
					class: 'p-dialog-profile p-dialog-story-detail p-dialog-no-header-title',
                    modal: true,
                    showHeader: false,
                    draggable: false
                },
                onClose: () => {
					changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
					this.setOpenedModalCount(false)
                }
            });
        },
		loadMoreStories($state) {
			this.getStoriesList(++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		},
		createStory() {
			if (this.user) {
				let permission = 'story.allow_create'
				if(this.checkPermission(permission)){
					this.$router.push({ 'name': 'stories' })
				}
			}
		},
		resetStories() {
			this.currentPage = 1
			this.storiesList = []
			this.getStoriesList(this.currentPage)
		}
	}
}
</script>