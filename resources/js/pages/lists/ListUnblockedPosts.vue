<template>
    <h2 class="text-xl font-bold mb-4">{{ $t('Unblocked Posts') }}</h2>
    <PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreDiscoveryFeeds" :shadow="true">
		<template #empty>
			<div class="text-center">{{ $t('No unblocked posts') }}</div>
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
			page: 1
		}
    },
    mounted(){
		this.getMyPaidPostsList(this.page)
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getMyPaidPostsList', 'unsetPostsList']),
		loadMoreDiscoveryFeeds($state) {
			this.getMyPaidPostsList(++this.page).then((response) => {
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