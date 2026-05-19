export function get2FACurrent(){
    return window.axios.get('two_factor/get_current').then((response) => {
        return response.data.data
    })
}
export function remove2FA(data){
    return window.axios.post('two_factor/remove', data).then((response) => {
        return response.data.data
    })
}
export function sendSetupMail(data){
    return window.axios.post('two_factor/send_setup_email', data).then((response) => {
        return response.data.data
    })
}
export function verifySetupMail(data){
    return window.axios.post('two_factor/verify_setup_email', data).then((response) => {
        return response.data.data
    })
}
export function sendSetupPhone(data){
    return window.axios.post('two_factor/send_setup_phone', data).then((response) => {
        return response.data.data
    })
}
export function verifySetupPhone(data){
    return window.axios.post('two_factor/verify_setup_phone', data).then((response) => {
        return response.data.data
    })
}
export function getCodeApp(data){
    return window.axios.post('two_factor/get_code_app', data).then((response) => {
        return response.data.data
    })
}
export function verifyCodeApp(data){
    return window.axios.post('two_factor/verify_code_app', data).then((response) => {
        return response.data.data
    })
}
export function getLoginCurrent(data){
    return window.axios.post('two_factor/get_login_current', data).then((response) => {
        return response.data.data
    })
}
export function sendLoginCode(data){
    return window.axios.post('two_factor/send_login_code', data).then((response) => {
        return response.data.data
    })
}
export function verifyLoginCode(data){
    return window.axios.post('two_factor/verify_login_code', data).then((response) => {
        return response.data.data
    })
}