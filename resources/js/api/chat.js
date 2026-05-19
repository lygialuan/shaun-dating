export function getChatRoomsList(page){
    return window.axios.get(`chat/get_room/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getRoomMessages(roomId, page){
    return window.axios.get(`chat/get_room_message/${roomId}/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getChatRequestRoomsList(page){
    return window.axios.get(`chat/get_room_request/${page}`).then((response) => {
        return response.data.data;
    });
}
export function getRequestsCount(){
    return window.axios.get('chat/get_request_count').then((response) => {
        return response.data.data;
    });
}
export function getRoomDetail(roomId){
    return window.axios.get(`chat/detail/${roomId}`).then((response) => {
        return response.data.data;
    });
}
export function createChatRoom(roomData){
    return window.axios.post('chat/store_room', roomData).then((response) => {
        return response.data.data;
    });
}
export function sendMessage(chatData){
    return window.axios.post('chat/store_room_message', chatData).then((response) => {
        return response.data.data;
    });
}
export function uploadChatImages(imageData){
    return window.axios.post('chat/upload_photo', imageData).then((response) => {
        return response.data.data;
    });
}
export function deleteChatItem(itemData){
    return window.axios.post('chat/delete_message_item', itemData).then((response) => {
        return response.data.data;
    });
}
export function actionChatRoom(actionData){
    return window.axios.post('chat/store_room_status', actionData).then((response) => {
        return response.data.data;
    });
}
export function markSeenChat(roomId){
    return window.axios.post(`chat/store_room_seen/${roomId}`).then((response) => {
        return response.data.data;
    });
}
export function markUnseenChat(roomId){
    return window.axios.post(`chat/store_room_unseen/${roomId}`).then((response) => {
        return response.data.data;
    });
}
export function deleteMessages(roomId){
    return window.axios.post('chat/clear_room_message', {id: roomId}).then((response) => {
        return response.data.data;
    });
}
export function toggleChatNotification(chatData){
    return window.axios.post('chat/store_room_notify', chatData).then((response) => {
        return response.data.data;
    });
}
export function checkRoomOnline(roomIds){
    return window.axios.post('chat/check_room_online', roomIds).then((response) => {
        return response.data.data;
    });
}
export function getSearchRoomsList(keyword){
    return window.axios.get(`chat/search_room/${keyword}`).then((response) => {
        return response.data.data;
    });
}
export function getSearchRequestRoomsList(keyword){
    return window.axios.get(`chat/search_room_request/${keyword}`).then((response) => {
        return response.data.data;
    });
}
export function unsentRoomMessage(messageId){
    return window.axios.post(`chat/unsent_room_message/${messageId}`).then((response) => {
        return response.data.data;
    });
}
export function uploadChatFiles(fileData){
    return window.axios.post('chat/upload_file', fileData).then((response) => {
        return response.data.data;
    });
}
export function sendChatFund(fundData){
    return window.axios.post(`chat/send_fund`, fundData).then((response) => {
        return response.data.data
    })
}
export function deleteRoom(roomId){
    return window.axios.post('chat/delete_room', {id: roomId}).then((response) => {
        return response.data.data;
    });
}
export function sendVoice(data){
    return window.axios.post('chat/store_audio', data).then((response) => {
        return response.data.data;
    });
}
export function storeActiveRoom(roomId){
    return window.axios.post(`chat/store_active_room/${roomId}`).then((response) => {
        return response.data.data;
    });
}
export function aiSuggestion(roomId){
    return window.axios.get(`ai_chat_profiles/suggestion/${roomId}`).then((response) => {
        return response.data.data;
    });
}