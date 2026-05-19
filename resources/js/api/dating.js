export function getAttributes(){
    return window.axios.get(`dating/attributes`).then((response) => {
        return response.data.data;
    });
}
export function saveAttributes(data){
    return window.axios.post(`dating/save_attributes`, data).then((response) => {
        return response.data.data
    }) 
}
export function getInterestAttributes(){
    return window.axios.get(`dating/interest_attributes`).then((response) => {
        return response.data.data;
    });
}
export function saveInterestAttributes(data){
    return window.axios.post(`dating/save_interest_attributes`, data).then((response) => {
        return response.data.data
    }) 
}
export function saveFilter(data){
    return window.axios.post(`dating/save_filter`, data).then((response) => {
        return response.data.data
    }) 
}
export function suggestionLocations(keyword){
    return window.axios.get(`dating/suggestion_locations/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function swipe(data){
    return window.axios.post(`dating/swipe`, data).then((response) => {
        return response.data.data
    }) 
}
export function getUserActions(page, action){
    return window.axios.get(`dating/get_user_actions/${page}/${action}`).then((response) => {
        return response.data.data
    })
}