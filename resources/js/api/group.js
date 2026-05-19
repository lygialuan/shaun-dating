export function getGroupCategories(){
    return window.axios.get('groups/get_categories').then((response) => {
        return response.data.data
    })
}
export function storeGroupCategories(categoriesData){
    return window.axios.post('groups/store_category', categoriesData).then((response) => {
        return response.data.data
    })
}
export function storeGroupHashtags(hashtagsData){
    return window.axios.post(`groups/store_hashtag`, hashtagsData).then((response) => {
        return response.data.data
    }) 
}
export function storeGroup(groupData){
    return window.axios.post(`groups/store`, groupData).then((response) => {
        return response.data.data
    })
}
export function getExploreGroupFeeds(page){
    return window.axios.get(`groups/get_explore/${page}`).then((response) => {
        return response.data.data
    })
}
export function getAllGroups(page, keyword, category){
    return window.axios.get(`groups/get_all?page=${page}&keyword=${keyword}&category=${category}`).then((response) => {
        return response.data.data
    }) 
}
export function getSuggestGroups(page){
    return window.axios.get(`groups/get_for_you/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getGroupProfile(groupId){
    return window.axios.get(`groups/get_profile/${groupId}`).then((response) => {
        return response.data.data
    })
}
export function uploadGroupCover(groupData){
    return window.axios.post(`groups/upload_cover`, groupData).then((response) => {
        return response.data.data
    })
}
export function storeDescriptionGroup(descriptionData){
    return window.axios.post(`groups/store_description`, descriptionData).then((response) => {
        return response.data.data
    })
}
export function storeNameGroup(nameData){
    return window.axios.post(`groups/store_name`, nameData).then((response) => {
        return response.data.data
    })
}
export function getYourGroupFeeds(page){
    return window.axios.get(`groups/get_your_feed/${page}`).then((response) => {
        return response.data.data
    })
}
export function getGroupPosts(groupId, page){
    return window.axios.get(`groups/get_post/${groupId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getGroupMedias(groupId, page){
    return window.axios.get(`groups/get_media/${groupId}/${page}`).then((response) => {
        return response.data.data
    })
}
export function getGroupRules(groupId){
    return window.axios.get(`groups/get_rule/${groupId}`).then((response) => {
        return response.data.data
    })
}
export function storeGroupRule(ruleData){
    return window.axios.post(`groups/store_rule`, ruleData).then((response) => {
        return response.data.data
    })
}
export function storeGroupRuleOrder(ruleData){
    return window.axios.post(`groups/store_rule_order`, ruleData).then((response) => {
        return response.data.data
    })
}
export function deleteGroupRule(ruleId){
    return window.axios.post(`groups/delete_rule`, { id: ruleId }).then((response) => {
        return response.data.data
    })
}
export function storeGroupTypePrivate(groupId){
    return window.axios.post(`groups/store_type_private`, { id: groupId }).then((response) => {
        return response.data.data
    })
}
export function storeGroupNotificationSettings(settingData){
    return window.axios.post(`groups/store_notify_setting`, settingData).then((response) => {
        return response.data.data
    })
}
export function storeJoinGroup(groupId){
    return window.axios.post(`groups/store_join`, { id: groupId }).then((response) => {
        return response.data.data
    })
}
export function storeLeaveGroup(groupId){
    return window.axios.post(`groups/store_leave`, { id: groupId }).then((response) => {
        return response.data.data
    })
}
export function removeJoinRequest(requestId){
    return window.axios.post(`groups/remove_join_request`, { id: requestId }).then((response) => {
        return response.data.data
    })
}
export function togglePinPost(pinData){
    return window.axios.post(`groups/store_pin`, pinData).then((response) => {
        return response.data.data
    })
}
export function getGroupAdmin(groupId, page){
    return window.axios.get(`groups/get_admin/${groupId}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getGroupMembers(groupId, query, page){
    return window.axios.get(`groups/get_members/${groupId}/?query=${query}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getGroupJoinRequests(groupId, query, page){
    return window.axios.get(`groups/get_join_request/${groupId}/?query=${query}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function acceptJoinRequest(requestId){
    return window.axios.post(`groups/accept_join_request`, { id: requestId }).then((response) => {
        return response.data.data
    })
}
export function acceptMultiJoinRequest(groupId, requestIds){
    return window.axios.post(`groups/accept_multi_join_request`, { id: groupId, request_ids: requestIds }).then((response) => {
        return response.data.data
    })
}
export function deleteJoinRequest(requestId){
    return window.axios.post(`groups/delete_join_request`, { id: requestId }).then((response) => {
        return response.data.data
    })
}
export function deleteMultiJoinRequests(groupId, requestIds){
    return window.axios.post(`groups/delete_multi_join_request`, { id: groupId, request_ids: requestIds }).then((response) => {
        return response.data.data
    })
}
export function storeBlockMember(groupId, userId){
    return window.axios.post(`groups/store_block`, { id: groupId, user_id: userId }).then((response) => {
        return response.data.data
    })
}
export function removeBlockMember(blockId){
    return window.axios.post(`groups/remove_block`, { id: blockId }).then((response) => {
        return response.data.data
    })
}
export function getBlockedGroupMembers(groupId, query, page){
    return window.axios.get(`groups/get_blocks/${groupId}/?query=${query}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function removeGroupMember(memberId){
    return window.axios.post(`groups/remove_member`, { id: memberId }).then((response) => {
        return response.data.data
    })
}
export function storeGroupSettings(settingsData){
    return window.axios.post(`groups/store_setting`, settingsData).then((response) => {
        return response.data.data
    })
}
export function getMyPendingPosts(groupId, page){
    return window.axios.get(`groups/get_my_post_pending/${groupId}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function deleteMyPendingPost(postId){
    return window.axios.post(`groups/delete_my_post_pending`, { id: postId }).then((response) => {
        return response.data.data
    })
}
export function getPendingPosts(groupId, userId, keyword, page, fromDate, toDate, type, sort){
    return window.axios.get(`groups/get_post_pending/${groupId}?user_id=${userId}&query=${keyword}&page=${page}&from_date=${fromDate}&to_date=${toDate}&type=${type}&sort=${sort}`).then((response) => {
        return response.data.data
    }) 
}
export function deletePendingPost(postId){
    return window.axios.post(`groups/delete_post_pending`, { id: postId }).then((response) => {
        return response.data.data
    })
}
export function acceptPendingPost(postId){
    return window.axios.post(`groups/accept_post_pending`, { id: postId }).then((response) => {
        return response.data.data
    })
}
export function searchUsersForGroupAdmin(groupId, keyword){
    return window.axios.get(`groups/search_user_for_admin/${groupId}/${keyword}`).then((response) => {
        return response.data.data
    }) 
}
export function transferGroupOwner(ownerData){
    return window.axios.post(`groups/store_transfer_owner`, ownerData).then((response) => {
        return response.data.data
    })
}
export function storeGroupAdmin(adminData){
    return window.axios.post(`groups/add_admin`, adminData).then((response) => {
        return response.data.data
    })  
}
export function removeGroupAdmin(userId){
    return window.axios.post(`groups/remove_admin`, {id: userId}).then((response) => {
        return response.data.data
    }) 
}
export function getGroupOverview(groupId){
    return window.axios.get(`groups/get_report_overview/${groupId}`).then((response) => {
        return response.data.data
    }) 
}
export function getReportGroupChart(groupId){
    return window.axios.get(`groups/get_report_chart/${groupId}`).then((response) => {
        return response.data.data
    }) 
}
export function getAdminManageConfig(groupId){
    return window.axios.get(`groups/get_admin_manage_config/${groupId}`).then((response) => {
        return response.data.data
    }) 
}
export function getUserManageConfig(groupId){
    return window.axios.get(`groups/get_user_manage_config/${groupId}`).then((response) => {
        return response.data.data
    }) 
}
export function getPostsWithHashtag(groupId, hashtag, page){
    return window.axios.get(`groups/get_post_with_hashtag/${groupId}/${hashtag}/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getManagedGroups(status, page){
    return window.axios.get(`groups/get_manage_group/?status=${status}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}
export function getJoinedGroups(page){
    return window.axios.get(`groups/get_joined/${page}`).then((response) => {
        return response.data.data
    }) 
}
export function deleteGroup(deleteData){
    return window.axios.post(`groups/delete`, deleteData).then((response) => {
        return response.data.data
    })  
}
export function hideGroup(hideData){
    return window.axios.post(`groups/store_hide`, hideData).then((response) => {
        return response.data.data
    })  
}
export function openGroup(openData){
    return window.axios.post(`groups/store_open`, openData).then((response) => {
        return response.data.data
    })  
}
export function searchGroupPosts(groupId, keyword, page){
    return window.axios.get(`groups/search_post?query=${keyword}&id=${groupId}&page=${page}`).then((response) => {
        return response.data.data
    }) 
}