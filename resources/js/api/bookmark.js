export function getBookmarkItem(itemType, page){
    return window.axios.get(`/bookmark/get/${itemType}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function toggleBookmarkItem(data){
    return window.axios.post("bookmark/store", data).then((response) => {
        return response.data.data
    })
}