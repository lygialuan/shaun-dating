<template>
	<PreviewVibbsList :loading="loadingPostsList" :vibbs-list="postsList" :subject-id="userInfo.id" subject-type="user" @load-more="loadMoreVibbsUser">
		<template #empty>
			<div class="main-content-section">
				<div class="p-5 text-center">
					{{ $t('Nothing to see here yet') }}
				</div>
			</div>
		</template>
	</PreviewVibbsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import PreviewVibbsList from '@/components/vibb/PreviewVibbsList.vue'

export default {
	props: ['userInfo'],
    components: { PreviewVibbsList },
    data(){
		return{
			currentPage: 1
		}
    },
	computed: {
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    mounted(){
		this.getUserVibbsList(this.userInfo.id, this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getUserVibbsList', 'unsetPostsList']),
		loadMoreVibbsUser($state) {
			this.getUserVibbsList(this.userInfo.id, ++this.currentPage).then((response) => {
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