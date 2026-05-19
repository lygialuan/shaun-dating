export function createLinkPayment(subscriptionData){
    return window.axios.post('gateway_recurring/create_link_payment', subscriptionData).then((response) => {
        return response.data.data
    })
}