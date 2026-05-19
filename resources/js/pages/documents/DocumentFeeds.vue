<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreDocumentFeeds" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import PostsList from '@/components/posts/PostsList.vue'

export default {
	components: { PostsList },
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
	mounted(){
		this.getDocumentPostsList(this.currentPage)
    },
	unmounted(){
		this.unsetPostsList()
	},
	methods: {
		...mapActions(usePostStore, ['getDocumentPostsList', 'unsetPostsList']),
		loadMoreDocumentFeeds($state) {
			this.getDocumentPostsList(++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	},
}
</script>
