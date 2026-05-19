export function getConfig(){
    return window.axios.get('vibb/config').then((response) => {
        return response.data.data
    })
}
export function getSearchSongs(keyword){
    return window.axios.get(`vibb/search_song/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function uploadVibbVideo(data, onUploadProgress){
    return window.axios.post('vibb/upload_video', data, {
        onUploadProgress: onUploadProgress
    }).then((response) => {
        return response.data.data;
    });
}
export function storeVibb(data){
    return window.axios.post('vibb/store', data).then((response) => {
        return response.data.data
    })
}
export function getVibbsForYou(page){
    return window.axios.get(`/vibb/for_you/${page}`).then((response) => {
        return response.data.data
    })
}
export function getFollowingVibbs(page){
    return window.axios.get(`/vibb/following/${page}`).then((response) => {
        return response.data.data
    })
}
export function getProfileVibbs(userId, page){
    return window.axios.get(`/vibb/profile/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getMyVibbs(page){
    return window.axios.get(`/vibb/my/${page}`).then((response) => {
        return response.data.data
    })
}