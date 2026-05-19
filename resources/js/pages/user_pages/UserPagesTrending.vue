<template>
    <UserPagesList :loading="loadingPagesList" :pagesList="pagesList" :has-load-more="loadMoreStatus" @load-more="getPagesList(searchPage)" />
</template>

<script>
import UserPagesList from '@/components/lists/UserPagesList.vue';
import { getTrendingUserPages } from '@/api/page'

export default {
    props: ['categoryId'],
    components: { UserPagesList },
    data(){
        return {
            searchPage: 1,
            loadingPagesList: true,
            loadMoreStatus: false,
            pagesList: []
        }
    },
    mounted() {
        this.getPagesList(this.searchPage)
    },
    methods: {
        async getPagesList(page){
            try {
                const response = await getTrendingUserPages(page)
                if(page === 1){
                    this.pagesList = []
                }
                this.pagesList = window._.concat(this.pagesList, response.items);
                if(response.has_next_page){
                    this.loadMoreStatus = true
                    this.searchPage++;
                }else{
                    this.loadMoreStatus = false
                }
            } catch (error) {
                console.log(error)
            } finally {
                this.loadingPagesList = false
            }
        }
    }
}
</script>