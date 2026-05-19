export function inviteByEmails(inviteData){
    return window.axios.post('invite/store', inviteData).then((response) => {
        return response.data.data;
    });
}
export function inviteByCsvFile(inviteFile){
    return window.axios.post('invite/store_csv', inviteFile).then((response) => {
        return response.data.data;
    });
}
export function getInviteInfo(){
    return window.axios.get('invite/info').then((response) => {
        return response.data.data;
    });
}
export function getReferralsList(type, page, keyword){
    return window.axios.get(`invite/get?type=${type}&page=${page}&query=${keyword}`).then((response) => {
        return response.data.data;
    });
}