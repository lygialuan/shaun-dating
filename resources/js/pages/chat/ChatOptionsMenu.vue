<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white" tabindex="0" autofocus>
        <button v-if="room.enable" :class="importantActionClass" @click="reportChatRoom(room.id)">{{$t('Report')}}</button>
        <button :class="importantActionClass" @click="deleteAllMessages(room.id)">{{$t('Delete Messages')}}</button>
        <button :class="importantActionClass" @click="clearRoom(room.id)">{{$t('Delete Room')}}</button>
        <template v-if="room.enable">
            <button :class="importantActionClass" @click="blockUser(getOthersMemberInRoom(room.members)[0].id)">{{$t('Block')}}</button>
            <button v-if="room.enable_notify" :class="normalActionClass" @click="stopNotification(room.id)">{{$t('Stop Notifications')}}</button>
            <button v-else :class="normalActionClass" @click="turnOnNotification(room.id)">{{$t('Turn on Notifications')}}</button>
        </template>
        <button :class="normalActionClass" @click="cancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapActions } from 'pinia';
import { checkPopupBodyClass } from '@/utility'
import { toggleBlockUser } from '@/api/user';
import ReportModal from '@/components/modals/ReportModal.vue'
import { useChatStore } from '@/store/chat';
import { useUtilitiesStore } from '@/store/utilities';

export default {
    data(){
        return{
            room: this.dialogRef.data.room
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        ...mapActions(useChatStore, ['deleteMessages', 'toggleChatNotification', 'removeRoomId', 'deleteRoom']),
        ...mapActions(useUtilitiesStore, ['setShowChatBoxBottom']),
        reportChatRoom(roomId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'chat_rooms',
                    id: roomId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'chat-report-modal',
                    modal: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass()
                }
             }), 300);
        },
        deleteAllMessages(roomId){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete all messages?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deleteMessages(roomId);
                    checkPopupBodyClass()
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);         
        },
        clearRoom(roomId){
            this.dialogRef.close();
            setTimeout(() => this.$confirm.require({
                message: this.$t('Are you sure you want to delete this room?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
                    this.deleteRoom(roomId);
                    checkPopupBodyClass()
                    if(this.showMiniChatBox()){
                        this.setShowChatBoxBottom(true, null, 'chat')
                    }
                },
                reject: () => {
                    checkPopupBodyClass()
                },
                onHide: () => {
                    checkPopupBodyClass()
                }
            }), 300);         
        },
        async blockUser(userId) {
            try {
                await toggleBlockUser({
                    id: userId,
                    action: "add"
                });
                this.dialogRef.close();
                this.removeRoomId(this.room.id)
                if(this.showMiniChatBox()){
                    this.setShowChatBoxBottom(true, null, 'chat')
                } 
            }
            catch (error) {
                this.showError(error.error)
            }
        },
        stopNotification(roomId){
            this.toggleChatNotification({
                room_id: roomId,
                action: 'remove'
            })
            this.dialogRef.close();
        },
        turnOnNotification(roomId){
            this.toggleChatNotification({
                room_id: roomId,
                action: 'add'
            })
            this.dialogRef.close();
        },
        cancel(){
            this.dialogRef.close();
        }
    }
}
</script>