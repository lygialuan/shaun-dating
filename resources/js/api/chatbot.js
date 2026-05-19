export function getChatbotMessages(page) {
    return window.axios.get(`chatbot/get_history/${page}`).then((response) => {
        return response.data.data.items;
    });
}
export function sendChatbotMessage(data){
    return window.axios.post('chatbot/send_message', data).then((response) => {
        return response.data.data.message;
    });
}
export function getChatbotProvider() {
    return window.axios.get(`chatbot/get_provider`).then((response) => {
        return response.data.data;
    });
}
export function clearChatbotHistory(){
    return window.axios.post('chatbot/clear_history').then((response) => {
        return response.data.data;
    });
}