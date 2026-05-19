<template>
	<h2 class="text-xl font-bold mb-4">{{ $t('Vibbs') }}</h2>
	<PreviewVibbsList :loading="loadingPostsList" :vibbs-list="postsList" subject-type="my" @load-more="loadMoreVibbsUser">
		<template #empty>
			<div class="text-center">{{$t('No Vibbs')}}</div>
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
	computed: {
		...mapState(usePostStore, ["loadingPostsList", "postsList"]),
    },
    data(){
		return{
			currentPage: 1
		}
    },
    mounted(){
		this.getMyVibbsList(this.currentPage);
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
		...mapActions(usePostStore, ['getMyVibbsList', 'unsetPostsList']),
		loadMoreVibbsUser($state) {
			this.getMyVibbsList(++this.currentPage).then((response) => {
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