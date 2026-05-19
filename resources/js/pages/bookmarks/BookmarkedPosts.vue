<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMorePosts" />
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: { PostsList },
    data(){
		return{
			currentPage: 1
		}
    },
    computed: {
        ...mapState(usePostStore, ['postsList', 'loadingPostsList'])
    },
    mounted(){
		this.getBookmarkedPostsList(this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
        ...mapActions(usePostStore, ['getBookmarkedPostsList', 'unsetPostsList']),
		loadMorePosts($state) {
			this.getBookmarkedPostsList(++this.currentPage).then((response) => {
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