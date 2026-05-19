import { defineStore } from 'pinia'
import { getChatRoomsList, getChatRequestRoomsList, markSeenChat, markUnseenChat, getRoomMessages, getRoomDetail, actionChatRoom, sendMessage, deleteMessages, toggleChatNotification, checkRoomOnline, unsentRoomMessage, sendChatFund, createChatRoom, deleteRoom } from '../api/chat';

export const useChatStore = defineStore('chat', {
    // convert to a function
    state: () => ({
        loadingRoomsList: true,
        roomsList: [],
        roomInfo: null,
        loadingRoomMessagesList: true,
        roomMessagesList: [],
        loadmoreRoomsList: false,
        loadmoreRoomMessagesList: false,
        routerName: '',
        removeRoomIdTime: null,
        replyMessage: null
    }),
    actions: {
        doMarkSeenRoom(roomId){
            this.roomsList.map(room => {
                if(room.id === roomId){
                    room.message_count = 0
                }
                return room
            })
        },
        doPushRoomsList(items) {
            this.roomsList = window._.unionBy(window._.concat(this.roomsList, items),'id');
        },
        doSetLastMessageListRoom(roomData) {
            if(this.roomsList && window._.find(this.roomsList, {id: roomData.id})){
                let room = window._.find(this.roomsList, {id: roomData.id})
                room.last_message = roomData.last_message
                room.last_update = roomData.last_update
            }
            this.roomsList = window._.orderBy(this.roomsList, ['last_update'],['desc']);
        },
        doAddMessage(newMessage){
            const { message, room } = newMessage;

            if (window._.find(this.roomMessagesList, {id: message.id})) {
                return;
            }

            if (this.roomInfo && room.id == this.roomInfo.id) {
                room.message_count = 0;
                if(message.client_message_id){
                    const msgIndex = this.roomMessagesList.findIndex(m => m.client_message_id === message.client_message_id);
                    if (msgIndex !== -1) {
                        this.roomMessagesList[msgIndex] = message;
                    } else {
                        this.roomMessagesList.push(message);
                    }
                } else {
                    this.roomMessagesList.push(message);
                }
            }            

            // Update the room in roomsList
            this.roomsList = this.roomsList.map(r => r.id === room.id ? room : r);

            this.roomsList = window._.orderBy(this.roomsList, ['last_update'],['desc']);

        },
        doAddClientMessage(message){
            this.roomMessagesList = window._.concat(this.roomMessagesList, message)
        },
        doRemoveMessageByClientMessageId(clientMessageId) {
            this.roomMessagesList = this.roomMessagesList.filter(
                msg => msg.client_message_id !== clientMessageId
            );
        },
        doDeleteMessages(roomId){
            // delete message in room detail
            this.roomMessagesList = []

            // delete message in rooms list
            if(this.roomsList && window._.find(this.roomsList, {id: roomId})){
                let room = window._.find(this.roomsList, {id: roomId})
                room.last_message = null
            }
        },
        doUnsentMessage(messageData){
            if(this.roomMessagesList.length){
                if(window._.find(this.roomMessagesList, {id: Number(messageData.id)})){
                    let message = window._.find(this.roomMessagesList, {id: messageData.id})
                    message.is_delete = 1
                }
                window._.forEach(this.roomMessagesList, function(room){
                    if(room.parent_message && room.parent_message.id == messageData.id){
                        room.parent_message.is_delete = 1
                    }  
                })
            }       
            if(this.roomsList.length && window._.find(this.roomsList, {id: Number(messageData.room_id)})){
                let room = window._.find(this.roomsList, {id: Number(messageData.room_id)})
                if(room.last_message.id == messageData.id){
                    room.last_message.is_delete = 1
                }
            }
        },
        doToggleChatNotification(roomData){
            if(this.roomsList){
                let room = this.roomsList.find(room => room.id === roomData.room_id); // get room in rooms list
                if(room.id == roomData.room_id){
                    if(roomData.action === 'add'){
                        room.enable_notify = true;
                    }else if(roomData.action === 'remove'){
                        room.enable_notify = false;
                    }      
                }
            }
            
            if(this.roomInfo && this.roomInfo.id === roomData.room_id){
                let room = this.roomInfo // get room in room detail
                if(room.id == roomData.room_id){
                    if(roomData.action === 'add'){
                        room.enable_notify = true;
                    }else if(roomData.action === 'remove'){
                        room.enable_notify = false;
                    }      
                }
            }            
        },
        doSetRoomOnline(roomData) {
            this.roomsList.map(room => {
                if (window._.has(roomData, room.id)) {
                    room.is_online = roomData[room.id]
                }

                return room
            })

            if (this.roomInfo && window._.has(roomData, this.roomInfo.id)) {
                this.roomInfo.is_online = roomData[this.roomInfo.id]
            }
        },
        doAddRoom(room) {
            if (! window._.find(this.roomsList,{id: room.id})) {
                this.roomsList.unshift(room)
            }
        },
        doSetRoomSeenUser(userId) {
            this.roomInfo.members.map(member => {
                if(member.id === userId){
                    let lastMessage = window._.last(this.roomMessagesList);
                    if (lastMessage) {
                        member.message_seen_id = lastMessage.id
                    }                    
                }
                return member
            })
        },
        doSetRoomUnread(roomId) {
            this.roomsList.map(room => {
                if (room.id == roomId) {
                    room.message_count = 1
                }

                return room
            })
        },
        doSetRoomAccept(roomId) {
            if(this.roomInfo && this.roomInfo.id === roomId){
                this.roomInfo.status = 'accepted'
                this.roomInfo.user_status = 'accepted'
            }
        },
        doRemoveRoom(roomId) {
            this.roomsList = this.roomsList.filter(room => room.id !== roomId)
            this.removeRoomIdTime = Date.now()
        },
        async getRoomsList(page){
            try {
				const response = await getChatRoomsList(page)
                if(page === 1){
                    this.roomsList = response.items;
                }
				else{
                    this.doPushRoomsList(response.items) 
                }
                // check load more page
				if(response.has_next_page){
                    this.loadmoreRoomsList = true
				}else{
                    this.loadmoreRoomsList = false
				}
                this.loadingRoomsList = false
                return response
			} catch (error) {
                this.loadingRoomsList = false
				console.log(error)
			}
        },
        async getRequestRoomsList(page){
            try {
				const response = await getChatRequestRoomsList(page)
                if(page === 1){
                    this.roomsList = response.items;
                }
				else{
                    this.doPushRoomsList(response.items) 
                }
                // check load more page
				if(response.has_next_page){
                    this.loadmoreRoomsList = true
				}else{
                    this.loadmoreRoomsList = false
				}
                this.loadingRoomsList = false
                return response
			} catch (error) {
                this.loadingRoomsList = false
				console.log(error)
			}
        },
        async markSeenRoom(roomId){
            await markSeenChat(roomId)
            this.doMarkSeenRoom(roomId)
        },
        async markUnseenRoom(roomId){
            await markUnseenChat(roomId)
            this.roomsList.map(room => {
                if(room.id === roomId){
                    room.message_count = 1
                }
                return room
            }) 
        },
        async getRoomMessages({roomId, page}){
            try {                      
                const response = await getRoomMessages(roomId, page)
                if(page === 1){
                    this.roomMessagesList = [];
                }
                this.roomMessagesList = window._.concat(window._.reverse(response.items), this.roomMessagesList);
                // check load more page
				if(response.has_next_page){
					this.loadmoreRoomMessagesList = true
				}else{
					this.loadmoreRoomMessagesList = false
				}
                this.loadingRoomMessagesList = false
                return response
            } catch (error) {
                this.loadingRoomMessagesList = false
                console.log(error)
            }
        },
        async getRoomDetail(roomId){
            try {
                const response = await getRoomDetail(roomId)
                this.roomInfo = response

                //set last message and time for list room
                this.doSetLastMessageListRoom(response)
            } catch (error) {
                console.log(error)
            }
        },
        async doActionChatRoom({roomId, action}){
            try {
                await actionChatRoom({room_id: roomId, action: action})
                if(action === 'accept'){
                    this.roomInfo.status = 'accepted'
                }
            } catch (error) {
                console.log(error)
            }
        },
        async sendMessage(newMessage){
            const response = await sendMessage(newMessage)
            this.doAddMessage(response)
        },
        async sendFundMessage(newMessage){
            const response = await sendChatFund(newMessage)
            this.doAddMessage(response)
        },
        async deleteMessages(roomId){
            try {
                await deleteMessages(roomId)
                this.doDeleteMessages(roomId)
            } catch (error) {
                console.log(error)
            }
        },
        async toggleChatNotification(roomData){
            try {
                await toggleChatNotification(roomData)
                this.doToggleChatNotification(roomData)
            } catch (error) {
                console.log(error)
            }
        },
        clearRoomDetail() {
            this.roomInfo = null
        },
        clearRoomList() {
            this.roomsList = []
            this.loadingRoomsList = true
            this.loadmoreRoomsList = false   
        },
        clearRoomMessagesList(){
            this.roomMessagesList = [];
            this.loadingRoomMessagesList = true
            this.loadmoreRoomMessagesList = false
        },
        async pingRoomOnline() {
            if (this.roomsList.length > 0) {
                let roomIds = this.roomsList.map(room => {
                    return room.id
                })
                const response = await checkRoomOnline({'ids': roomIds})
                this.doSetRoomOnline(response)
            }
        },
        setChatMessageSentEvent(data) {            
            if ((data.status == 'accepted' && this.routerName == 'chat') || data.status != 'accepted' && this.routerName == 'chat_requests') {
                this.doAddRoom(data.room)
            }
            this.doAddMessage(data)
            if (this.roomInfo && data.room.id == this.roomInfo.id) {
                this.markSeenRoom(data.room.id)
                this.doSetRoomSeenUser(data.message.user_id)
            }
        },        
        setChatRoomSeenSelfEvent(data) {
            this.doMarkSeenRoom(data.room_id)
        },
        setRoomSeenEvent(data) {
            if (this.roomInfo && data.room_id == this.roomInfo.id) {
                this.doSetRoomSeenUser(data.user_id)
            }
        },
        setRoomUnreadEvent(data) {
            this.doSetRoomUnread(data.room_id)            
        },
        setRoomUserStatus(status) {
            this.roomInfo.user_status = status
        },
        setRouterName(routerName){
            this.routerName = routerName
        },
        setRoomAcceptEvent(data) {
            this.doSetRoomAccept(data.room_id)
        },
        removeRoomId(roomId) {
            this.doRemoveRoom(roomId)
        },
        async unsentRoomMessage(message){
            await unsentRoomMessage(message.id)
            this.doUnsentMessage(message)
        },
        setChatMessageUnsentEvent(data) {    
            this.doUnsentMessage(data.message)
        },
        setReplyMessage(message){
            this.replyMessage = message
        },
        async createNewRoom(userId){
            const response = await createChatRoom({user_id: userId})
            this.doPushRoomsList(response)
            return response
        },
        async deleteRoom(roomId){
            try {
                await deleteRoom(roomId)
                this.doDeleteMessages(roomId)
                this.doRemoveRoom(roomId)
            } catch (error) {
                console.log(error)
            }
        }
    },
    persist: false
  })