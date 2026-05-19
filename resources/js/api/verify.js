export function getVerificationDocuments(){
    return window.axios.get('user_verify/get_files').then((response) => {
        return response.data.data;
    });
}
export function uploadVerificationDocuments(data){
    return window.axios.post('user_verify/upload_file', data).then((response) => {
        return response.data.data;
    });
}
export function deleteVerificationDocuments(item){
    return window.axios.post('user_verify/delete_file', item).then((response) => {
        return response.data.data;
    });
}
export function storeVerificationDocuments(data){
    return window.axios.post('user_verify/store_request', data).then((response) => {
        return response.data.data;
    });
}