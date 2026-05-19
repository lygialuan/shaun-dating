<template>
	<MasonryPostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreMediaFeeds" class="pb-5">
		<template #empty>
			<div class="main-content-section">
				<div class="p-5 text-center">
					<div class="text-base-lg font-bold mb-base-2">{{ $t('Nothing to see here yet') }}</div>
					<div>{{ $t('Start following people and tags to see updated posts') }}</div>
				</div>
			</div>
		</template>
	</MasonryPostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import MasonryPostsList from '@/components/posts/MasonryPostsList.vue'

export default {
    components: { MasonryPostsList },
	props: ['user'],
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getMediaPostsList(this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getMediaPostsList', 'unsetPostsList']),
		loadMoreMediaFeeds($state) {
			this.getMediaPostsList(++this.currentPage).then((response) => {
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