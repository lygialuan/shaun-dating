export function getMentionUserList(keyword, notMe = 0, onlyUser = 0, notParent = 0){
    return window.axios.get(`user/search/${keyword}?not_me=${notMe}&only_user=${onlyUser}&not_parent=${notParent}`).then((response) => {
        return response.data.data
    })
}
export function getUserProfileInfo(user_name){
    return window.axios.get(`user/profile/${user_name}`).then((response) => {
        return response.data.data
    })
}
export function getBlockUsersList(type, page){
    return window.axios.get(`user/block/${type}/${page}`).then((response) => {
        return response.data.data
    })
}
export function toggleBlockUser(userData){
    return window.axios.post('user/store_block', userData).then((response) => {
        return response.data.data
    })
}
export function getSuggestUsers(){
    return window.axios.get('user/suggest').then((response) => {
        return response.data.data
    })
}
export function getTrendingUsers(){
    return window.axios.get('user/trending').then((response) => {
        return response.data.data
    })
}
export function loginFirst() {
    return window.axios.post('user/store_login_first').then((response) => {
        return response.data.data
    })
}
export function getComplete() {
    return window.axios.get('user/complete').then((response) => {
        return response.data.data
    });
}
export function me() {
    return window.axios.get('user/me');
}
export function getSuggestSearchUsers(page, keyword){
    return window.axios.get(`user/suggest_search?query=${keyword}&page=${page}`).then((response) => {
        return response.data.data
    })
}
export function getTrendingSearchUsers(page, keyword){
    return window.axios.get(`user/trending_search?query=${keyword}&page=${page}`).then((response) => {
        return response.data.data
    })
}
export function uploadCoverProfilePicture(data){
    return window.axios.post('user/upload_cover', data).then((response) => {
        return response.data.data;
    });
}
export function uploadAvatarProfilePicture(data){
    return window.axios.post('user/upload_avatar', data).then((response) => {
        return response.data.data;
    });
}
export function storeDarkmode(status){
    return window.axios.post('user/store_darkmode', {darkmode: status}).then((response) => {
        return response.data.data;
    })
}
export function storeVideoAutoPlay(status){
    return window.axios.post('user/store_video_auto_play', status).then((response) => {
        return response.data.data;
    })
}
export function sendEmailVerify(){
    return window.axios.post('user/send_email_verify').then((response) => {
        return response.data.data;
    })
}
export function checkEmailVerify(code){
    return window.axios.post('user/check_email_verify', {'code': code}).then((response) => {
        return response.data.data;
    })
}
export function storeChangePassword(passwordData){
    return window.axios.post('user/change_password', passwordData)
}
export function sendCodeForgotPassword(email){
    return window.axios.post('user/send_code_forgot_password', email)
}
export function checkCodeForgotPassword(codeData){
    return window.axios.post('user/check_code_forgot_password', codeData)
}
export function storeForgotPassword(passwordData){
    return window.axios.post('user/store_forgot_password', passwordData)
}
export function storeAccount(dataUser){
    return window.axios.post('user/store_account', dataUser)
}
export function checkPasswordAccount(password){
    return window.axios.post('user/check_password', password)
}
export function deleteUserAccount(password){
    return window.axios.post('user/delete', password)
}
export function changeLanguage(languageKey){
    return window.axios.post('user/store_language', languageKey)
}
export function getDownload(){
    return window.axios.get('user/get_download').then((response) => {
        return response.data.data
    })
}
export function requestDownload(password){
    return window.axios.post('user/store_download', password)
}
export function addVerifyEmailPassword(email, password, password_confirmed){
    return window.axios.post('user/send_add_email_password_verify', {
        email: email,
        password: password,
        password_confirmed: password_confirmed
    })
}
export function saveVerifyEmailPassword(email, password, password_confirmed, code){
    return window.axios.post('user/store_add_email_password_verify', {
        email: email,
        password: password,
        password_confirmed: password_confirmed,
        code: code
    })
}

export function removeLoginOtherDevice(password){
    return window.axios.post('user/remove_login_other_device', {
        password: password
    })
}
export function sendPhoneVerify(data){
    return window.axios.post("user/send_phone_verify", data).then((response) => {
        return response.data.data
    })
}
export function changePhoneVerify(data){
    return window.axios.post("user/change_phone_verify", data).then((response) => {
        return response.data.data
    })
}
export function checkPhoneVerify(code){
    return window.axios.post("user/check_phone_verify", {code: code}).then((response) => {
        return response.data.data
    })
}
export function changePhoneWhenEdit(data){
    return window.axios.post("user/change_phone_when_edit", data).then((response) => {
        return response.data.data
    })
}
export function checkPhoneWhenEdit(data){
    return window.axios.post("user/check_phone_when_edit", data).then((response) => {
        return response.data.data
    })
}
export function uploadPhotosVerify(data){
    return window.axios.post('user/upload_photos_verify', data).then((response) => {
        return response.data.data;
    });
}
export function removePhotoVerify(data){
    return window.axios.post('user/remove_photo_verify', data).then((response) => {
        return response.data.data;
    });
}
export function changeMainPhoto(data){
    return window.axios.post('user/change_main_photo', data).then((response) => {
        return response.data.data;
    });
}
export function completedPhotoVerify(data){
    return window.axios.post('user/completed_photo_verify', data).then((response) => {
        return response.data.data;
    });
}
export function getAllUsers(params) {
    return window.axios.get('user/get_all_users', {
        params
    }).then(res => res.data.data)
}
