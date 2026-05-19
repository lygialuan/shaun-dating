<template>
    <h2 class="text-xl font-bold mb-4">{{ $t('Stories') }}</h2>
    <StoriesList :loading="loadingStories" :list="myStoriesList" :has-load-more="hasLoadMoreStories" @load-more="getMyStoriesList(page)"/>
</template>

<script>
import { mapState } from 'pinia'
import { getMyStories } from '@/api/stories'
import { useStoriesStore } from '@/store/stories';
import StoriesList from '@/components/lists/StoriesList.vue';

export default{
    components: { StoriesList },
    data(){
        return{
            hasLoadMoreStories: false,
            loadingStories: true,
            myStoriesList: [],
            page: 1
        }
    },
    mounted() {
        this.getMyStoriesList(this.page);
    },
    computed: {
        ...mapState(useStoriesStore, ['deleteStoryItem']),
    },
    watch: {
        deleteStoryItem(){
            this.myStoriesList = this.myStoriesList.filter(story => story.id !== this.deleteStoryItem.id)  
        }
    },
    methods: {
        async getMyStoriesList(page){
            try {
				const response = await getMyStories(page)
                if(page === 1){
                    this.myStoriesList = response.items
                }else{
                    this.myStoriesList = window._.concat(this.myStoriesList, response.items);
                }
                if(response.has_next_page){
                    this.hasLoadMoreStories = true
                    this.page++;
                }else{
                    this.hasLoadMoreStories = false
                }
                this.loadingStories = false
			} catch (error) {
                this.loadingStories = false
			}
        }
    } 
}
</script>