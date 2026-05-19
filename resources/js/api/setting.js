export function getNotificationSettings(){
    return window.axios.get('user/notification_setting').then((response) => {
        return response.data.data
    })
}

export function saveNotificationSettings(data){
    return window.axios.post('user/store_notification_setting', data).then((response) => {
        return response.data.data
    })
}

export function getPrivacySettings(){
    return window.axios.get('user/privacy_setting').then((response) => {
        return response.data.data
    })
}

export function savePrivacySettings(data){
    return window.axios.post('user/store_privacy_setting', data).then((response) => {
        return response.data.data
    })
}

export function getEmailSetting() {
    return window.axios.get('user/email_setting').then((response) => {
        return response.data.data
    })
}

export function saveEmailSetting(data) {
    return window.axios.post('user/store_email_setting', data).then((response) => {
        return response.data.data
    })
}
export function getProfileSettings(){
    return window.axios.get('user/get_edit_profile').then((response) => {
        return response.data.data
    })
}
export function storeProfileSettings(settingData){
    return window.axios.post('user/store_edit_profile', settingData).then((response) => {
        return response.data.data
    })
}