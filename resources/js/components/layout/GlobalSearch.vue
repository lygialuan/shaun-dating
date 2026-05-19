<template>
	<div class="global-search-header relative w-full" v-click-outside="closeSearchBox">
		<form @submit.prevent="searchText">
			<BaseInputText ref="searchInput" v-model="searchModal" @input="onInputSearch" @focus="onFocusSearch" :autofocus="autofocus"
				:placeholder="$t('Search')" class="global-search-header-input" left_icon="search" />
		</form>
		<SearchSuggestion v-if="isShownSearchBox" :searchContent="searchContent" :searchResultsList="searchResultsList"
			:loading-search-histories="loadingSearchHistories" :historySearchesList="historySearchesList" @close="closeSearchBox"
			@delete-history="handleDeleteSearchHistory" @save-history="handleSaveHistorySearch" />
	</div>
</template>

<script>
import {
	getSearchSuggest,
	getSearchHistories,
	storeSearchHistory,
	deleteSearchHistory,
} from '@/api/search'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import SearchSuggestion from '@/components/search/SearchSuggestion.vue'
import Constant from '@/utility/constant'

let typingTimer = null

export default {
	components: { BaseInputText, SearchSuggestion },
	props: {
		autofocus: { type: Boolean, default: false }
	},
	data() {
		return {
			type: this.$route.params.type || 'post',
			isShownSearchBox: false,
			searchModal: this.$route.query.q || '',
			searchResultsList: {},
			loadingSearchHistories: true,
			historySearchesList: [],
			mentionChar: Constant.MENTION,
			hashtagChar: Constant.HASHTAG,
		}
	},
	computed: {
		searchContent(){
			return this.searchModal?.replace('#', '') || ''
		}
	},
	watch: {
		'$route'() {
			this.searchModal = this.$route.name === 'search' ? this.$route.query.q : ''
		}
	},
	methods: {
		openSearchBox() {
			this.isShownSearchBox = true
			if(this.searchContent){
				this.getSearchSuggestsList(this.searchContent)
			} else {
				this.handleGetSearchHistories()
			}
		},
		closeSearchBox() {
			this.isShownSearchBox = false
			this.historySearchesList = []
		},
		onInputSearch() {
			if (this.searchContent) {
				clearTimeout(typingTimer)
				typingTimer = setTimeout(() => this.getSearchSuggestsList(this.searchContent), 400)
			} else {
				this.handleGetSearchHistories()
			}
		},
		onFocusSearch(){
			this.openSearchBox()
		},
		async searchText() {
			if (this.searchContent) {
				this.$router.push({
					name: 'search',
					params: { search_type: 'text', type: 'post' },
					query: { q: this.searchContent },
				})
				this.closeSearchBox()
				this.blurSearchInput()
				this.handleSaveHistorySearch(this.searchContent)
			}
		},
		async getSearchSuggestsList(keyword) {
			if (!keyword) return
			try {
				this.searchResultsList = { text: keyword, hashtags: [], users: [], pages: [], groups: [] }
				const res = await getSearchSuggest(keyword)
				Object.assign(this.searchResultsList, res)
			} catch (error) {
				this.showError(error.error)
			}
		},
		async handleGetSearchHistories() {
			if(this.searchContent) return;
			this.loadingSearchHistories = true
			try {
				this.historySearchesList = await getSearchHistories()
			} catch (error) {
				this.showError(error.error)
			} finally {
				this.loadingSearchHistories = false
			}
		},
		async handleSaveHistorySearch(query) {
			try {
				await storeSearchHistory(query)
			} catch (error) {
				this.showError(error.error)
			}
		},
		async handleDeleteSearchHistory(id) {
			try {
				await deleteSearchHistory(id)
				this.historySearchesList = this.historySearchesList.filter(h => h.id !== id)
			} catch (error) {
				this.showError(error.error)
			}
		},
		blurSearchInput() {
			if (this.$refs.searchInput) {
				this.$refs.searchInput.$el.querySelector('input').blur()
			}
		}
	}
}
</script>