export function toggleFollowHashtag(hashtagData){
    return window.axios.post('follow/hashtag/store', hashtagData).then((response) => {
        return response.data.data
    })
}
export function getMyHashtags(page){
    return window.axios.get(`follow/hashtag/${page}`).then((response) => {
        return response.data.data
    })
}
export function getSuggestUsers(keyword){
    return window.axios.get(`user/suggest_signup/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function toggleFollowTrainer(trainerData){
    return window.axios.post('follow/user/store', trainerData).then((response) => {
        return response.data.data
    })
}
export function getFollowingUsers(userId, page){
    return window.axios.get(`follow/user/get_following/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getFollowerUsers(userId, page){
    return window.axios.get(`follow/user/get_follower/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function toggleFollowUser(userData){
    return window.axios.post('follow/user/store', userData).then((response) => {
        return response.data.data
    })
}
export function toggleStopNotificationUser(userData){
    return window.axios.post('follow/user/store_notification', userData).then((response) => {
        return response.data.data
    })
}
export function getMyFollowingUsers(type, page){
    return window.axios.get(`follow/user/get_my_following/${type}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getMyFollowerUsers(type, page){
    return window.axios.get(`follow/user/get_my_follower/${type}/${page}`).then((response) => {
        return response.data.data
    })
}