export function toggleLikeItem(postData){
    return window.axios.post("/like/store", postData).then((response) => {
        return response.data.data
    })
}

export function getLikersByItemId(itemType, itemId, page){
    return window.axios.get(`/like/get/${itemType}/${itemId}/${page}`).then((response) => {
        return response.data.data;
    });
}