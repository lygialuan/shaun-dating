export function getCommentsByItemId(itemType, itemId, page){
    return window.axios.get(`/comment/get/${itemType}/${itemId}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function postNewComment(data){
    return window.axios.post('comment/store', data).then((response) => {
        return response.data.data;
    });
}
export function deleteComment(commentId){
    return window.axios.post("/comment/delete", commentId).then((response) => {
        return response.data.data
    })
}
export function getRepliesByCommentId(commentId, page){
    return window.axios.get(`/comment/get_reply/${commentId}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function postNewReply(data){
    return window.axios.post('comment/store_reply', data).then((response) => {
        return response.data.data;
    });
}
export function deleteReply(replyId){
    return window.axios.post("/comment/delete_reply", replyId).then((response) => {
        return response.data.data
    })
}
export function getCommentSingle(type, itemId, commentId, replyId){
    return window.axios.get(`/comment/get_single/${type}/${itemId}/${commentId}/${replyId}`).then((response) => {
        return response.data.data
    })
}
export function editComment(data){
    return window.axios.post('comment/store_edit', data).then((response) => {
        return response.data.data;
    }); 
}
export function editReply(data){
    return window.axios.post('comment/store_reply_edit', data).then((response) => {
        return response.data.data;
    }); 
}
export function uploadCommentImages(imageData){
    return window.axios.post('comment/upload_photo', imageData).then((response) => {
        return response.data.data;
    });
}
export function deleteCommentItem(itemId){
    return window.axios.post('comment/delete_item', {id: itemId}).then((response) => {
        return response.data.data;
    });
}
export function uploadReplyImages(imageData){
    return window.axios.post('comment/upload_reply_photo', imageData).then((response) => {
        return response.data.data;
    });
}
export function deleteReplyItem(itemId){
    return window.axios.post('comment/delete_reply_item', {id: itemId}).then((response) => {
        return response.data.data;
    });
}