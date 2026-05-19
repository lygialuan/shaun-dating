export function sendGift(data){
    return window.axios.post(`gift/send`, data).then((response) => {
        return response.data.data
    }) 
}
export function getGiftReceivedList(userId, page){
    return window.axios.get(`gift/gift_received/${userId}/${page}`).then((response) => {
        return response.data.data.items
    })
}
export function getGiftList(page){
    return window.axios.get(`gift/list/${page}`).then((response) => {
        return response.data.data.items
    })
}