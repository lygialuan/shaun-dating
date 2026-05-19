export function getSearchSuggest(keyword){
    return window.axios.get(`search/suggest?query=${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getSearchResults(search_type, query, type, page){
    return window.axios.get(`search/${search_type}?query=${query}&type=${type}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getSearchHistories(){
    return window.axios.get(`search/get_search_histories`).then((response) => {
        return response.data.data
    }) 
}

export function storeSearchHistory(query){
    return window.axios.post(`search/store_search_history`, {query: query}).then((response) => {
        return response.data.data
    }) 
}

export function deleteSearchHistory(searchHistoryId) {
    return window.axios.post(`search/delete_search_history`, {id: searchHistoryId}).then((response) => {
        return response.data.data;
    });
}