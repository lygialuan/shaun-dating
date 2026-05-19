<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMorePosts">
		<template #empty>
			<div class="main-content-section">
				<div class="p-5 text-center">
					{{ $t('No one has shared anything yet, be the first to create one.') }}
				</div>
			</div>
		</template>
	</PostsList>
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
		this.getExploreGroupPostsList(this.currentPage)
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getExploreGroupPostsList', 'unsetPostsList']),
		loadMorePosts($state) {
			this.getExploreGroupPostsList(++this.currentPage).then((response) => {
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