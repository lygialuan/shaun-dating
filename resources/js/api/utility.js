export function getInit() {
    return window.axios.get('init');
}
export function getCsrf() {
    return window.axios.get('csrf');
}
export function getDataServerLayout(data) {
    return window.axios.post('layout/get-detail', data).then((response) => {
        return response.data.data;
    });
}
export function getPingNotification() {
    return window.axios.get('user/ping');
}
export function shareToEmails(subjectData){
    return window.axios.post('utility/share_email', subjectData).then((response) => {
        return response.data.data
    })
}
export function unsubscribeEmail(data){
    return window.axios.post('utility/unsubscribe_email', data).then((response) => {
        return response.data.data
    })
}
export function checkAccessCode(code){
    return window.axios.post('utility/check_access_code', code).then((response) => {
        return response.data.data
    })
}
export function storeContact(data){
    return window.axios.post('utility/store_contact', data);
}
export function translateContent(data){
    return window.axios.post('utility/content_translate', data).then((response) => {
        return response.data.data
    })
}
export function storeFcmToken(data) {
    return window.axios.post('utility/store_fcm_token', data);
}
export function removeFcmToken(data) {
    return window.axios.post('utility/remove_web_fcm_token', data);
}
export function getComplianceData(id){
    return window.axios.get(`compliance/tasks/${id}`).then((response) => {
        return response.data.data
    })
}
