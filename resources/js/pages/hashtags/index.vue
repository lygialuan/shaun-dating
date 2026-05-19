<template>
	<h1 class="page-title mb-base-2">{{ currentTab === 'suggest' ? $t('Tags for you') : $t('Trending Tags') }}</h1>
	<div class="main-content-section">
		<BaseInputText :placeholder="$t('Enter tags')" left_icon="pencil" v-model="hashtagKeyword" @input="findHashtag()" class="mb-3"/>
		<HashtagsList :loading="loadingSearchHashtags" :hashtagsList="searchHashtags" :hasLoadMore="loadmoreStatus" :auto-load-more="true" @load-more="loadSearchHashtags" />
	</div>
</template>
<script>
import { getSuggestSearchHashtags, getTrendingSearchHashtags } from '@/api/hashtag'
import HashtagsList from '@/components/lists/HashtagsList.vue';
import BaseInputText from '@/components/inputs/BaseInputText.vue'
var typingTimer = null;

export default {
	components:{ HashtagsList, BaseInputText },
	props: ['tab', 'title'],
	data(){
		return{
			currentTab: this.tab ? this.tab : '',
			hashtagKeyword: '',
			loadmoreStatus: false,
            loadingSearchHashtags: true,
			searchHashtags: [],
            currentPage: 1
		}
	},
	mounted(){
		switch(this.currentTab){
			case 'trending':
				this.getTrendingSearchHashtagsList(this.currentPage, this.hashtagKeyword)
				break
			default: 
				this.getSuggestSearchHashtagsList(this.currentPage, this.hashtagKeyword)
		}
    },
	methods: {
		findHashtag(){
			this.currentPage = 1
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => 
				{
					switch(this.currentTab){
						case 'trending':
							this.getTrendingSearchHashtagsList(this.currentPage, this.hashtagKeyword)
							break
						default: 
							this.getSuggestSearchHashtagsList(this.currentPage, this.hashtagKeyword)
					}
				}
			, 400);
		},
		async getSuggestSearchHashtagsList(page, keyword){
			try {
				const response = await getSuggestSearchHashtags(page, keyword)
				// apply data to hashtags list
                if(page === 1){
                    this.searchHashtags = response.items
                }else{
                    this.searchHashtags = window._.concat(this.searchHashtags, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
				return response
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loadingSearchHashtags = false
			}
		},
		async getTrendingSearchHashtagsList(page, keyword){
            try {
				const response = await getTrendingSearchHashtags(page, keyword)
				// apply data to hashtags list
                if(page === 1){
                    this.searchHashtags = response.items
                }else{
                    this.searchHashtags = window._.concat(this.searchHashtags, response.items);
                }
				// check load more page
				if(response.has_next_page){
					this.loadmoreStatus = true
				}else{
					this.loadmoreStatus = false
				}
				return response
			} catch (error) {
                this.showError(error.error)
			} finally {
				this.loadingSearchHashtags = false
			}
        },
		loadSearchHashtags($state){
			switch(this.currentTab){
				case 'trending':
					this.getTrendingSearchHashtagsList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})		
					break;
				default: 
					this.getSuggestSearchHashtagsList(++this.currentPage, this.hashtagKeyword).then((response) => {
						if(response.has_next_page){
							this.loadmoreStatus = true
							$state.loaded()
						}else{
							this.loadmoreStatus = false
							$state.complete()
						}
					})
					break;
			}
		}
	}
}
</script>