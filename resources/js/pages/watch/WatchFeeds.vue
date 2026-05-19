<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreDiscoveryFeeds" />
</template>

<script>
import {mapActions, mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { usePostStore } from '../../store/post'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: { PostsList },
	props: ['user'],
	computed: {
		...mapState(useAuthStore, ['authenticated']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getWatchPostsList(this.currentPage)
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getWatchPostsList','unsetPostsList']),
		loadMoreDiscoveryFeeds($state) {
			this.getWatchPostsList(++this.currentPage).then((response) => {
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