<template>
	<PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMorePosts">
		<template #empty>
			<div class="main-content-section">
				<div class="text-center">{{$t('No posts found')}}</div>
			</div>
		</template>
	</PostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { usePostStore } from '../../store/post'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: { PostsList },
    props: ["search_type", "type", "query"],
    data(){
		return{
			queryData: this.query,
			loadmore_status: false,
			currentPage: 1
		}
    },
    computed: {
        ...mapState(usePostStore, ['postsList', 'loadingPostsList'])
    },
    mounted(){
		this.getSearchPostsList(this.search_type, this.queryData, this.type, this.currentPage);
    },
	watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
			this.currentPage = 1
			if(this.queryData){
				this.getSearchPostsList(this.search_type, this.queryData, this.type, this.currentPage)
			}
        }
    },
	unmounted(){
		this.unsetPostsList()
	},
    methods: {
        ...mapActions(usePostStore, ['getSearchPostsList', 'unsetPostsList']),
		loadMorePosts($state) {		
			this.getSearchPostsList(this.search_type, this.queryData, this.type, ++this.currentPage).then((response) => {
				if (response.length === 0) {
					$state.complete()
				} else {
					$state.loaded()
				}
			})	
		}
    }
}
</script>