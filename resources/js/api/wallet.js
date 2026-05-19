export function getWalletConfig(){
    return window.axios.get(`wallet/config`).then((response) => {
        return response.data.data
    })
}
export function getWalletTransactions(page, type, date_type, from_date, to_date){
    return window.axios.get(`wallet/get_transactions?type=${type}&date_type=${date_type}&from_date=${from_date}&to_date=${to_date}&page=${page}`).then((response) => {
        return response.data.data
    })
}
export function getWalletPackages(){
    return window.axios.get(`wallet/get_packages`).then((response) => {
        return response.data.data
    })
}
export function storeDeposit(depositData){
    return window.axios.post(`wallet/store_deposit`, depositData).then((response) => {
        return response.data.data
    })
}
export function storeWithdraw(withdrawData){
    return window.axios.post(`wallet/store_withdraw`, withdrawData).then((response) => {
        return response.data.data
    })
}
export function storeSend(fundData){
    return window.axios.post(`wallet/store_send`, fundData).then((response) => {
        return response.data.data
    })
}
export function getSuggestUsersToSendFund(keyword){
    return window.axios.get(`wallet/suggest_user_send/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getWithdrawalInfo(){
    return window.axios.get('wallet/get_withdrawal_info').then((response) => {
        return response.data.data
    })
}