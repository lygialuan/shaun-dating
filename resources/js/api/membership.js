export function getSubscriptionConfig(){
    return window.axios.get('user_subscription/config').then((response) => {
        return response.data.data;
    });
}
export function getCurrentSubscription(){
    return window.axios.get('user_subscription/get_current').then((response) => {
        return response.data.data
    })
}
export function storeSubscription(subscriptionData){
    return window.axios.post('user_subscription/store', subscriptionData).then((response) => {
        return response.data.data
    })
}
export function storeTrialSubscription(subscriptionData){
    return window.axios.post('user_subscription/store_trial', subscriptionData).then((response) => {
        return response.data.data
    })
}