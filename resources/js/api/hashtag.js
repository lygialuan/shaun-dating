export function getHashtag(hashtagName){
    return window.axios.get(`hashtag/get/${hashtagName}`).then((response) => {
        return response.data.data
    })
}
export function getSuggestHashtags(){
    return window.axios.get('hashtag/suggest').then((response) => {
        return response.data.data
    })
}
export function getTrendingHashtags(){
    return window.axios.get('hashtag/trending').then((response) => {
        return response.data.data
    })
}
export function getSuggestSignupHashtags(keyword){
    return window.axios.get(`hashtag/suggest_signup/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getSuggestSearchHashtags(page, keyword){
    return window.axios.get(`hashtag/suggest_search?page=${page}&query=${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getTrendingSearchHashtags(page, keyword){
    return window.axios.get(`hashtag/trending_search?page=${page}&query=${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getMentionHashtagsList(keyword, is_create = 0){
    return window.axios.get(`hashtag/search/${keyword}?is_create=${is_create}`).then((response) => {
        return response.data.data
    })
}