<template>
	<PostStatusBoxHolder post-from="groups" :subject="groupInfo" />
	<div v-if="groupInfo.hashtag_trending.length || currentHashtag || currentKeyword" class="leading-none my-5 px-base-2 md:px-0">
		<template v-if="!currentHashtag && !currentKeyword">
			<SlimScroll v-if="groupInfo.hashtag_trending.length">
				<button v-for="hashtag in groupInfo.hashtag_trending" :key="hashtag.name" @click="handleSelectHashtag(hashtag.name)" class="text-primary-color font-semibold mx-2 first:ms-0 cursor-pointer dark:text-dark-primary-color">
					{{ hashtagChar + hashtag.name }}
				</button>
			</SlimScroll>
		</template>
		<div v-else class="inline-flex gap-base-2 items-center text-primary-color font-bold text-base cursor-pointer dark:text-dark-primary-color" @click="handleRemoveSelected">
			<BaseIcon name="caret_left" class="text-main-color dark:text-white rtl:-rotate-180" />
			{{ currentKeyword && currentKeyword }}
			{{ currentHashtag && hashtagChar + currentHashtag }}
		</div>
	</div>
	<div v-if="groupInfo?.user_post_pending_count" class="flex items-center bg-web-wash rounded-none md:rounded-base-lg p-4 mb-base-2 dark:bg-dark-web-wash">
		<div class="flex-1 font-bold">{{ $filters.numberShortener(groupInfo?.user_post_pending_count, $t('[number] pending post'), $t('[number] pending posts')) }}</div>
		<BaseButton :to="{ name: 'groups_user_manage', params: { id: groupInfo.id } }">{{ $t('Manage post') }}</BaseButton>
	</div>
	<PostsList :key="postsListKey" :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMorePosts">
		<template #empty>
			<div class="main-content-section">
				<div class="p-5 text-center">
					<div class="text-base-lg font-bold">{{$t('Nothing to see here yet')}}</div>
				</div>
			</div>
		</template>
	</PostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import PostsList from '@/components/posts/PostsList.vue'
import PostStatusBoxHolder from '@/components/posts/PostStatusBoxHolder.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Constant from '@/utility/constant'
import SlimScroll from '@/components/utilities/SlimScroll.vue'

export default {
    props: ['groupInfo'],
    components: { PostsList, PostStatusBoxHolder, BaseButton, BaseIcon, SlimScroll }, 
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList', 'pinnedPostFlag', 'searchPost']),
		postsListKey(){
			if(this.currentKeyword){
				return this.currentKeyword
			} else if(this.currentHashtag){
				return this.currentHashtag
			} else {
				return 'all'
			}
		}
    },
    data(){
		return{
			currentPage: 1,
			currentHashtag: '',
			currentKeyword: '',
			hashtagChar: Constant.HASHTAG
		}
    },
	watch:{
		searchPost: {
            handler: function (){
                this.updateFeedsByKeyword();
            },
            deep: true
        },
		groupInfo(){
			if(this.groupInfo.canView){
				this.updateFeedsByKeyword()
			}
		},
		pinnedPostFlag(){
			if(this.groupInfo.canView){
				this.updateFeedsByKeyword()
			}
		}
	},
    mounted(){
		if(this.groupInfo.canView){
			this.updateFeedsByKeyword()
		}
		this.setCurrentPostPage('group');
    },
	unmounted(){
		this.unsetPostsList()
		this.setCurrentPostPage()
		this.resetSearchPost()
	},
    methods: {
		...mapActions(usePostStore, ['getGroupPostsList', 'unsetPostsList', 'setCurrentPostPage', 'getGroupPostsListWithHashtag', 'getSearchGroupPosts', 'resetSearchPost']),
		loadMorePosts($state) {
			if(this.currentKeyword){
				this.getSearchGroupPosts(this.groupInfo.id, this.currentKeyword, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			} else if(this.currentHashtag){
				this.getGroupPostsListWithHashtag(this.groupInfo.id, this.currentHashtag, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			} else {
				this.getGroupPostsList(this.groupInfo.id, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			}
		},
		handleSelectHashtag(hashtag){
			this.currentKeyword = ''
			this.currentHashtag = hashtag;
			this.currentPage = 1
			this.getGroupPostsListWithHashtag(this.groupInfo.id, hashtag, this.currentPage)
		},
		handleSelectKeyword(keyword){
			this.currentHashtag = ''
			this.currentKeyword = keyword
			this.currentPage = 1
			this.getSearchGroupPosts(this.groupInfo.id, keyword, this.currentPage)
		},
		handleRemoveSelected(){
			this.currentKeyword = ''
			this.currentHashtag = ''
			this.currentPage = 1
			this.getGroupPostsList(this.groupInfo.id, this.currentPage)
		},
		updateFeedsByKeyword(){
			if (this.searchPost.keyword) {
                this.searchPost.type === 'hashtag' ? this.handleSelectHashtag(this.searchPost.keyword) : this.handleSelectKeyword(this.searchPost.keyword);
            } else {
                this.handleRemoveSelected();
            }
		}
	}
}
</script>