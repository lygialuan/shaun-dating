<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreDiscoveryFeeds" />
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
		this.getDiscoveryPostsList(this.currentPage)
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getDiscoveryPostsList', 'unsetPostsList']),
		loadMoreDiscoveryFeeds($state) {
			this.getDiscoveryPostsList(++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	}
}
</script>