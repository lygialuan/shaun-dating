export function getConfig(){
    return window.axios.get('story/config').then((response) => {
        return response.data.data
    })
}
export function getSearchSongs(keyword){
    return window.axios.get(`story/search_song/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getStories(page, filters = {}) {
    return window.axios.get(`story/get/${page}`, {
        params: {
            filters: filters
        }
    }).then((response) => {
        return response.data.data
    })
}
export function storeStory(data){
    return window.axios.post('story/store', data).then((response) => {
        return response.data.data
    })
}
export function getStoryDetail(story_id){
    return window.axios.get(`story/detail/${story_id}`).then((response) => {
        return response.data.data
    }) 
}
export function deleteStory(data){
    return window.axios.post('story/delete_item', data).then((response) => {
        return response.data.data
    })
}
export function storyViewItem(data){
    return window.axios.post('story/store_view_item', data).then((response) => {
        return response.data.data
    })
}
export function getMyStories(page){
    return window.axios.get(`story/my/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getViewers(storyId, page){
    return window.axios.get(`story/get_view/${storyId}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function storeMessages(messageData){
    return window.axios.post('story/store_message', messageData).then((response) => {
        return response.data.data
    })
}
export function getStorySingleDetail(storyId){
    return window.axios.get(`story/detail_item/${storyId}`).then((response) => {
        return response.data.data
    }) 
}
export function getStoryDetailInList(storyId){
    return window.axios.get(`story/detail_in_list/${storyId}`).then((response) => {
        return response.data.data
    }) 
}
export function shareStories(storyData){
    return window.axios.post('story/share_message', storyData).then((response) => {
        return response.data.data
    })
}
export function uploadStoryVideo(data, onUploadProgress){
    return window.axios.post('story/upload_video', data, {
        onUploadProgress: onUploadProgress
    }).then((response) => {
        return response.data.data;
    });
}