<template>
	<div v-if="currentHashtag || currentKeyword" class="leading-none my-5 px-base-2 md:px-0">
		<div class="inline-flex gap-base-2 items-center text-primary-color font-bold text-base cursor-pointer dark:text-dark-primary-color" @click="handleRemoveSelected">
			<BaseIcon name="caret_left" class="text-main-color dark:text-white rtl:-rotate-180" />
			{{ currentKeyword && currentKeyword }}
			{{ currentHashtag && hashtagChar + currentHashtag }}
		</div>
	</div>
	<PostsList :key="postsListKey" :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreUserFeeds">
		<template #empty>
			<div class="main-content-section">
				<div class="p-5 text-center">
					{{ $t('Nothing to see here yet') }}
				</div>
			</div>
		</template>
	</PostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import { useAuthStore } from '@/store/auth'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: {
		BaseIcon,
		PostsList
	},
	props: ['userInfo'],
	computed: {
		...mapState(useAuthStore, ['user']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList', 'searchPost']),
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
        }
	},
    mounted(){
		this.updateFeedsByKeyword()
		if(this.userInfo.user_name === this.user.user_name){
			this.setCurrentPostPage('profile')
		}
    },
	unmounted(){
		this.unsetPostsList()
		this.setCurrentPostPage()
		this.resetSearchPost()
	},
    methods: {
		...mapActions(usePostStore, ['getUserPostsList', 'unsetPostsList', 'setCurrentPostPage', 'getSearchPagePosts', 'getPagePostsListWithHashtag', 'resetSearchPost']),
		loadMoreUserFeeds($state) {
			if(this.currentKeyword){
				this.getSearchPagePosts(this.userInfo.id, this.currentKeyword, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			} else if(this.currentHashtag){
				this.getPagePostsListWithHashtag(this.userInfo.id, this.currentHashtag, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			} else {
				this.getUserPostsList(this.userInfo.id, ++this.currentPage).then((response) => {
					response.length === 0 ? $state.complete() : $state.loaded();
				})
			}
		},
		handleSelectHashtag(hashtag){
			this.currentKeyword = ''
			this.currentHashtag = hashtag;
			this.currentPage = 1
			this.getPagePostsListWithHashtag(this.userInfo.id, hashtag, this.currentPage)
		},
		handleSelectKeyword(keyword){
			this.currentHashtag = ''
			this.currentKeyword = keyword
			this.currentPage = 1
			this.getSearchPagePosts(this.userInfo.id, keyword, this.currentPage)
		},
		handleRemoveSelected(){
			this.currentKeyword = ''
			this.currentHashtag = ''
			this.currentPage = 1
			this.getUserPostsList(this.userInfo.id, this.currentPage)
		},
		updateFeedsByKeyword(){
			if (this.searchPost.keyword) {
                this.searchPost.type === 'hashtag' ? this.handleSelectHashtag(this.searchPost.keyword) : this.handleSelectKeyword(this.searchPost.keyword);
            } else {
                this.handleRemoveSelected();
            }
		}
	},
	emits: ['change_tab', 'update_user_info']
}
</script>