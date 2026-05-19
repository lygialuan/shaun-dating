export function getPaidContentConfig(){
    return window.axios.get('paid_content/get_config').then((response) => {
        return response.data.data
    })
}
export function getPaidContentPackages(){
    return window.axios.get('paid_content/get_packages').then((response) => {
        return response.data.data
    })
}
export function getPaidContentProfilePackages(userId){
    return window.axios.get(`paid_content/get_profile_packages/${userId}`).then((response) => {
        return response.data.data
    })
}
export function storeUserPackage(data){
    return window.axios.post('paid_content/store_user_package', data).then((response) => {
        return response.data.data
    })
}
export function getEarningReport(){
    return window.axios.get('paid_content/get_earning_report').then((response) => {
        return response.data.data
    })
}
export function getEarningTransaction(){
    return window.axios.get('paid_content/get_earning_transaction').then((response) => {
        return response.data.data
    })
}
export function getUserSubscriber(dateType, status, page, keyword, fromDate, toDate){
    return window.axios.get(`paid_content/get_user_subscriber?date_type=${dateType}&status=${status}&page=${page}&keyword=${keyword}&from_date=${fromDate}&to_date=${toDate}`).then((response) => {
        return response.data.data
    })
}
export function getSubscriberDetail(subscriberId){
    return window.axios.get(`paid_content/get_subscriber_detail/${subscriberId}`).then((response) => {
        return response.data.data
    })
}
export function storeSubscriberCancel(subscriberId){
    return window.axios.post('paid_content/store_subscriber_cancel', {id: subscriberId}).then((response) => {
        return response.data.data
    })
}
export function storeSubscriberResume(subscriberId){
    return window.axios.post('paid_content/store_subscriber_resume', {id: subscriberId}).then((response) => {
        return response.data.data
    })
}
export function getSubscriberTransaction(subscriberId, page){
    return window.axios.get(`paid_content/get_subscriber_transaction/${subscriberId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function storeSubscribeUser(data){
    return window.axios.post('paid_content/store_subscriber_user', data).then((response) => {
        return response.data.data
    })
}
export function storePaidPost(data){
    return window.axios.post('paid_content/store_paid_post', data).then((response) => {
        return response.data.data
    })
}
export function getTipPackages(){
    return window.axios.get(`paid_content/get_tip_packages`).then((response) => {
        return response.data.data
    })
}
export function storeTip(data){
    return window.axios.post('paid_content/store_tip', data).then((response) => {
        return response.data.data;
    });
}
export function getMyPaidPostsList(page){
    return window.axios.get(`paid_content/get_my_paid_post/${page}`).then((response) => {
        return response.data.data
    })
}
export function getProfilePaidPostsList(userId, page){
    return window.axios.get(`paid_content/get_profile_paid_post/${userId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function editPaidPost(data){
    return window.axios.post('paid_content/store_edit_post', data).then((response) => {
        return response.data.data
    })
}