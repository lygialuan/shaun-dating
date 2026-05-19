export function getListCount(){
    return window.axios.get(`user_list/get_count`).then((response) => {
        return response.data.data
    })
}
export function getUserList(page){
    return window.axios.get(`user_list/get/${page}`).then((response) => {
        return response.data.data
    })
}
export function storeUserList(data){
    return window.axios.post(`user_list/store`, data).then((response) => {
        return response.data.data
    })
}
export function deleteUserList(listId){
    return window.axios.post(`user_list/delete`, {id: listId}).then((response) => {
        return response.data.data
    })
}
export function getMembersList(listId, page, query){
    return window.axios.get(`user_list/get_members?id=${listId}&page=${page}&query=${query}`).then((response) => {
        return response.data.data
    })
}
export function storeMembersList(data){
    return window.axios.post(`user_list/store_members`, data).then((response) => {
        return response.data.data
    })
}
export function deleteMemberList(memberId){
    return window.axios.post(`user_list/delete_member`, {id: memberId}).then((response) => {
        return response.data.data
    })
}
export function getSendMessageConfig(){
    return window.axios.get('user_list/send_message_config').then((response) => {
        return response.data.data
    })
}
export function searchForSend(query){
    return window.axios.get(`user_list/search_for_send?query=${query}`).then((response) => {
        return response.data.data
    })
}
export function sendMessage(data){
    return window.axios.post(`user_list/send_message`, data).then((response) => {
        return response.data.data
    })
}