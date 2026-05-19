<template>
	<div class="main-content-section">
        <UserPagesList :loading="loadingPages" :pagesList="searchPagesList" :has-load-more="true" :auto-load-more="true" @load-more="loadMorePages" />
	</div>
</template>

<script>
import { getSearchResults } from '@/api/search';
import UserPagesList from '@/components/lists/UserPagesList.vue';

export default {
	components:{ UserPagesList },
	props: ["search_type", "type", "query"],
	data(){
        return{
            error: null,
            queryData: this.query,
            loadingPages: true,
            searchPagesList: [],
            currentPage: 1
        }
    },
    mounted(){
        this.getSearchPagesList(this.queryData, this.currentPage)
    },
    watch: {
        '$route'() {
			this.queryData = !window._.isNil(this.$route.query.q) ? this.$route.query.q : ''
            this.currentPage = 1
            if(this.queryData){
                this.getSearchPagesList(this.queryData, this.currentPage)
            }
        }
    },
    methods: {
        async getSearchPagesList(query, page){
            try {             
				const response = await getSearchResults(this.search_type, query, this.type, page)

                // Apply data to users list
                if(this.currentPage === 1){
                    this.searchPagesList = response
                }else{
                    this.searchPagesList = window._.concat(this.searchPagesList, response);
                }
                return response
			} catch (error) {
                this.showError(error.error)
			} finally {
                this.loadingPages = false
            }
        },
        loadMorePages($state) {
			this.getSearchPagesList(this.queryData, ++this.currentPage).then((response) => {
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