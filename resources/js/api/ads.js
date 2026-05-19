export function storeAdvertising(adsData){
    return window.axios.post('advertising/store', adsData).then((response) => {
        return response.data.data
    })
}
export function storeEditAdvertising(adsData){
    return window.axios.post('advertising/store_edit', adsData).then((response) => {
        return response.data.data
    })
}
export function storeBootAdvertising(adsData){
    return window.axios.post('advertising/store_boot', adsData).then((response) => {
        return response.data.data
    })
}
export function storeStopAdvertising(adsId){
    return window.axios.post('advertising/store_stop', {id: adsId}).then((response) => {
        return response.data.data
    })
}
export function storeEnableAdvertising(adsId){
    return window.axios.post('advertising/store_enable', {id: adsId}).then((response) => {
        return response.data.data
    })
}
export function storeCompleteAdvertising(adsId){
    return window.axios.post('advertising/store_complete', {id: adsId}).then((response) => {
        return response.data.data
    })
}
export function getAdvertising(status, page){
    return window.axios.get(`/advertising/get/${status}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getAdvertisingDetail(adsId){
    return window.axios.get(`/advertising/get_detail/${adsId}`).then((response) => {
        return response.data.data;
    });
}
export function getAdvertisingReport(adsId, page){
    return window.axios.get(`/advertising/get_report/${adsId}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getAdvertisingConfig(){
    return window.axios.get(`/advertising/config`).then((response) => {
        return response.data.data;
    });
}
export function storeValidateAdvertising(adsData){
    return window.axios.post('advertising/validate_store', adsData).then((response) => {
        return response.data.data
    })
}
export function storeValidateBootAdvertising(adsData){
    return window.axios.post('advertising/validate_store_boot', adsData).then((response) => {
        return response.data.data
    })
}