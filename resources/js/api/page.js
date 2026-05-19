export function getPageCategories(){
    return window.axios.get('user_page/get_categories').then((response) => {
        return response.data.data
    })
}
export function storeUserPage(pageData){
    return window.axios.post(`user_page/store`, pageData).then((response) => {
        return response.data.data
    })
}
export function getAllUserPages(page, keyword, category){
    return window.axios.get(`user_page/get_all?page=${page}&keyword=${keyword}&category=${category}`).then((response) => {
        return response.data.data
    }) 
}
export function getTrendingUserPages(page){
    return window.axios.get(`user_page/get_trending/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getUserPagesForYou(page){
    return window.axios.get(`user_page/get_for_you/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getMyUserPages(page){
    return window.axios.get(`user_page/get_my/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getFollowingPages(page){
    return window.axios.get(`user_page/get_following/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function switchPage(pageId){
    return window.axios.post(`user_page/switch_page`, {id: pageId}).then((response) => {
        return response.data.data
    })
}
export function loginBack(){
    return window.axios.post(`user_page/login_back`).then((response) => {
        return response.data.data
    })
}
export function storeDescriptionPage(descriptionData){
    return window.axios.post(`user_page/store_description`, descriptionData).then((response) => {
        return response.data.data
    })
}
export function storeAddressPage(addressData){
    return window.axios.post(`user_page/store_address`, addressData).then((response) => {
        return response.data.data
    })
}
export function storePhoneNumberPage(phoneNumber){
    return window.axios.post(`user_page/store_phone_number`, {phone_number: phoneNumber}).then((response) => {
        return response.data.data
    })
}
export function storeEmailPage(email){
    return window.axios.post(`user_page/store_email`, {email: email}).then((response) => {
        return response.data.data
    })
}
export function storePrivacyPage(type, value){
    return window.axios.post(`user_page/store_privacy`, {type: type, value: value}).then((response) => {
        return response.data.data
    })
}
export function getPriceRangePage(){
    return window.axios.get(`user_page/get_prices`).then((response) => {
        return response.data.data
    }) 
}
export function storePricePage(price){
    return window.axios.post(`user_page/store_price`, {price: price}).then((response) => {
        return response.data.data
    })
}
export function storeWebsitesPage(websites){
    return window.axios.post(`user_page/store_websites`, {websites: websites}).then((response) => {
        return response.data.data
    })
}
export function getOpenHoursPage(){
    return window.axios.get(`user_page/get_hours`).then((response) => {
        return response.data.data
    }) 
}
export function storeOpenHoursPage(type, values){
    return window.axios.post(`user_page/store_hour`,{type: type, values: values}).then((response) => {
        return response.data.data
    }) 
}
export function storeCategoriesPage(categoriesData){
    return window.axios.post(`user_page/store_category`, categoriesData).then((response) => {
        return response.data.data
    }) 
}
export function storeHashtagsPage(hashtagsData){
    return window.axios.post(`user_page/store_hashtag`, hashtagsData).then((response) => {
        return response.data.data
    }) 
}
export function storeProfilePage(settingData){
    return window.axios.post(`user_page/store_profile`, settingData).then((response) => {
        return response.data.data
    }) 
}
export function getAdminPage(page){
    return window.axios.get(`user_page/get_admin/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function storeAdminPage(adminData){
    return window.axios.post(`user_page/add_admin`, adminData).then((response) => {
        return response.data.data
    })  
}
export function removeAdminPage(userId){
    return window.axios.post(`user_page/remove_admin`, {id: userId}).then((response) => {
        return response.data.data
    }) 
}
export function storeReviewPage(reviewData){
    return window.axios.post(`user_page/store_review`, reviewData).then((response) => {
        return response.data.data
    }) 
}
export function getReviewsPage(pageId, page){
    return window.axios.get(`user_page/get_reviews/${pageId}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function storeEnableReviewPage(enable){
    return window.axios.post(`user_page/store_enable_review`, {enable : enable}).then((response) => {
        return response.data.data
    }) 
}
export function getReportOverviewPage(){
    return window.axios.get(`user_page/get_report_overview`).then((response) => {
        return response.data.data
    }) 
}
export function getReportAudiencePage(){
    return window.axios.get(`user_page/get_report_audience`).then((response) => {
        return response.data.data
    }) 
}
export function getNotifySettingsPage(){
    return window.axios.get(`user_page/get_notify_setting`).then((response) => {
        return response.data.data
    }) 
}
export function storeNotifySettingsPage(dataSettings){
    return window.axios.post(`user_page/store_notify_setting`, dataSettings).then((response) => {
        return response.data.data
    }) 
}
export function transferPageOwner(ownerData){
    return window.axios.post(`user_page/transfer_owner`, ownerData).then((response) => {
        return response.data.data
    }) 
}
export function searchUsersForPageAdmin(keyword){
    return window.axios.get(`user_page/suggest_user_for_admin/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function getSuggestUsersForTransfer(keyword){
    return window.axios.get(`user_page/suggest_user_for_transfer/${keyword}`).then((response) => {
        return response.data.data
    })
}
export function deletePageAccount(password){
    return window.axios.post('user_page/delete', password)
}
export function getFeaturePackages(){
    return window.axios.get('user_page/get_feature_packages').then((response) => {
        return response.data.data
    })
}
export function storeFeaturePage(packageData){
    return window.axios.post('user_page/store_feature', packageData).then((response) => {
        return response.data.data
    })
}
export function searchPagePosts(pageId, keyword, page){
    return window.axios.get(`user_page/search_post?query=${keyword}&id=${pageId}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getPagePostsWithHashtag(pageId, hashtag, page){
    return window.axios.get(`user_page/get_post_with_hashtag/${pageId}/${hashtag}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getPageSwitches(page){
    return window.axios.get(`user_page/get_switch/${page}`).then((response) => {
        return response.data.data
    }) 
}