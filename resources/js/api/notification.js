export function getUserNotifications(page){
    return window.axios.get(`notification/get/${page}?clear=1`).then((response) => {
        return response.data.data;
    });
}
export function toggleEnableNotification(itemData){
    return window.axios.post('notification/store_enable', itemData).then((response) => {
        return response.data.data
    })
}
export function markSeenNotification(notificationData){
    return window.axios.post('notification/store_seen', notificationData).then((response) => {
        return response.data.data
    })
}
export function markAllAsRead(){
    return window.axios.post('notification/mark_all_as_read').then((response) => {
        return response.data.data
    })
}