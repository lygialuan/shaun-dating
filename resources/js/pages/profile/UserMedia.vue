<template>
    <div class="main-content-section">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Media') }}</h3>
        <MasonryPostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreMediaFeeds">
			<template #empty>
				<div class="p-5 text-center">
					{{ $t('Nothing to see here yet') }}
				</div>
			</template>
		</MasonryPostsList>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import MasonryPostsList from '@/components/posts/MasonryPostsList.vue'

export default {
    props: ['userInfo'],
    components: { MasonryPostsList },
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getProfileMediaPostsList(this.userInfo.id, this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getProfileMediaPostsList', 'unsetPostsList']),
		loadMoreMediaFeeds($state) {
			this.getProfileMediaPostsList(this.userInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
	},
    emits: ['change_tab', 'update_user_info']
}
</script>