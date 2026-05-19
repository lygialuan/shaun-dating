export function getHomeFeeds(page){
    return window.axios.get(`post/home/${page}`).then((response) => {
        return response.data.data
    })
}
export function postFeed(postData){
    return window.axios.post('post/store', postData).then((response) => {
        return response.data.data;
    });
}
export function deletePost(postId){
    return window.axios.post("post/delete", postId).then((response) => {
        return response.data.data
    })
}
export function getUserFeeds(userId, page){
    return window.axios.get(`/post/profile/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getPostById(postId){
    return window.axios.get(`/post/get/${postId}`).then((response) => {
        return response.data.data
    })
}
export function getDiscoveryFeeds(page){
    return window.axios.get(`/post/discovery/${page}`).then((response) => {
        return response.data.data
    })
}

export function getWatchFeeds(page){
    return window.axios.get(`/post/watch/${page}`).then((response) => {
        return response.data.data
    })
}

export function uploadPostImages(data){
    return window.axios.post('post/upload_photo', data).then((response) => {
        return response.data.data;
    });
}
export function deletePostItem(item){
    return window.axios.post('post/delete_item', item).then((response) => {
        return response.data.data;
    });
}
export function fetchLink(data){
    return window.axios.post('post/fetch_link', data).then((response) => {
        return response.data.data;
    });
}
export function editPost(data){
    return window.axios.post('post/store_edit', data).then((response) => {
        return response.data.data;
    }); 
}
export function uploadPostVideo(data, onUploadProgress){
    return window.axios.post('post/upload_video', data, {
        onUploadProgress: onUploadProgress
    }).then((response) => {
        return response.data.data;
    });
}
export function getMediaFeeds(page){
    return window.axios.get(`/post/media/${page}`).then((response) => {
        return response.data.data
    })
}
export function getProfileMediaFeeds(userId, page){
    return window.axios.get(`/post/profile_media/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function uploadPostFiles(fileData){
    return window.axios.post('post/upload_file', fileData).then((response) => {
        return response.data.data;
    });
}
export function votePoll(pollId, pollItemId, action){
    return window.axios.post('post/store_vote_poll', { poll_id: pollId, poll_item_id: pollItemId, action: action }).then((response) => {
        return response.data.data;
    });
}
export function getPollVotes(pollItemId, page){
    return window.axios.get(`/post/get_poll_item_vote/${pollItemId}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function changeCommentPrivacy(data){
    return window.axios.post('post/store_comment_privacy', data).then((response) => {
        return response.data.data;
    }); 
}
export function changeContentWarningPost(data){
    return window.axios.post('post/store_content_warning', data).then((response) => {
        return response.data.data;
    });
}
export function getDocumentFeeds(page){
    return window.axios.get(`/post/document/${page}`).then((response) => {
        return response.data.data
    })
}
export function storeStopComment(data){
    return window.axios.post('post/store_stop_comment', data).then((response) => {
        return response.data.data;
    });
}
export function togglePinProfile(data){
    return window.axios.post(`post/store_pin_profile`, data).then((response) => {
        return response.data.data
    })
}
export function togglePinHomePage(data){
    return window.axios.post(`post/store_pin_home`, data).then((response) => {
        return response.data.data
    })
}
export function getPostsListByIds(postIds){
    return window.axios.post(`post/get_by_ids?ids=${postIds}`).then((response) => {
        return response.data.data
    })
}
export function uploadPaidContentThumb(file){
    return window.axios.post('post/upload_thumb', file).then((response) => {
        return response.data.data;
    });
}
export function getPostNotifications(postId){
    return window.axios.get(`post/get_new_home/${postId}`).then((response) => {
        return response.data.data
    })
}