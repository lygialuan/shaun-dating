<template>
	<PostStatusBoxHolder />
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMorePosts" :show-notifications="true" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import PostsList from '@/components/posts/PostsList.vue'
import { usePostStore } from '@/store/post'
import { useActionStore } from '@/store/action'
import PostStatusBoxHolder from '@/components/posts/PostStatusBoxHolder.vue'

export default {
	components: { PostsList, PostStatusBoxHolder },
	data() {
		return {
			currentPage: 1
		}
	},
	mounted() {
		this.getHomePostsList(this.currentPage);
		this.setCurrentPostPage('home');
	},
	unmounted() {
		this.unsetPostsList()
		this.setCurrentPostPage()
	},
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
		...mapState(useActionStore, ['samePage'])
	},
	watch: {
		samePage() {
			this.currentPage = 1
			this.unsetPostsList()
			this.getHomePostsList(this.currentPage)
		}
	},
	methods: {
		...mapActions(usePostStore, ['getHomePostsList', 'unsetPostsList', 'setCurrentPostPage']),
		loadMorePosts($state) {
			this.getHomePostsList(++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})
		}
	},
}
</script>
