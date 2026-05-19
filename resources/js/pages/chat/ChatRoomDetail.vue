<template>
    <UploadWrap @drop_data="dropChatImages" class="flex flex-col flex-1 w-full min-h-0">  
        <div class="flex-1 min-w-0 min-h-0 relative">
            <button v-if="showScrollToNewMessageBtn" :style="{boxShadow: '0 2px 4px rgba(0, 0, 0, 0.1),0 8px 20px rgba(0, 0, 0, 0.1)'}" class="scroll-bottom-btn absolute bottom-4 left-1/2 -translate-x-1/2 bg-white text-primary-color rounded-full p-base-2 z-10 dark:bg-slate-700 dark:text-dark-primary-color" @click="scrollToEnd">
                <BaseIcon name="arrow_down" />
            </button>
            <div class="flex flex-col messages-list relative px-5 h-full overflow-auto py-base-2" ref="messages_list" @scroll="onScroll">
                <span ref="top_messages_list" class="absolute top-5">&nbsp;</span>
                <div class="flex-1"></div>
                <Loading v-if="loadingRoomMessagesList"/>
                <template v-else>
                    <InfiniteLoading v-if="loadmoreRoomMessagesList" @infinite="loadMoreRoomMessages">				
                        <template #spinner>
                            <Loading />
                        </template>
                        <template #complete><span></span></template>
                    </InfiniteLoading>  
                    <div v-for="(message, index) in roomMessagesList" :key="message.id" class="w-full py-1">    
                        <div v-if="index === 0 || (message.created_at_time - roomMessagesList[index > 0 ? (index - 1) : 0].created_at_time > 1000 )" class="chat-date-time text-xs text-sub-color text-center font-medium mt-1 mb-3 dark:text-slate-400">
                            {{message.created_at}}
                        </div>
                        <ChatMessageItem :message="message" :room_info="roomInfo" :show-avatar="index === 0 || (message.user_id !== roomMessagesList[index > 0 ? (index - 1) : 0].user_id)"/>
                        <!-- Users have seen message -->
                        <template v-if="roomInfo && roomInfo.status == 'accepted' && roomInfo.user_status != 'sent'">
                            <template v-for="member in roomInfo.members">
                                <template v-if="member.id != user.id && message.id == member.message_seen_id"> <!-- Messages of current user -->
                                    <img class="w-5 h-5 rounded-full mt-2 ms-auto object-cover object-center" :key="member.id" :src="member.avatar" :alt="member.name" />
                                </template>
                            </template>
                        </template>
                    </div>                                                         
                </template>       
                <div v-if="typing" class="text-xs text-sub-color dark:text-slate-400 mt-base-2">
                    {{$t('Typing ...')}}
                </div>
                <span ref="bottom_messages_list"></span>              
            </div>
        </div>
        <template v-if="roomInfo">
            <div v-if="roomInfo.enable && roomInfo.status !== 'accepted'" class="flex gap-base-2 p-base-2">
                <button @click="doActionRoom(roomInfo.id, 'accept')" class="accept-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-green dark:border-white/10">{{$t('Accept')}}</button>
                <button @click="doActionRoom(roomInfo.id, 'block')" class="decline-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-red dark:border-white/10">{{$t('Block')}}</button>
                <button @click="doActionRoom(roomInfo.id, 'delete')" class="decline-request-btn flex-1 p-base-2 border border-divider rounded-base-10xl text-base-red dark:border-white/10">{{$t('Delete')}}</button>
            </div>
            <div v-if="roomInfo.enable && !roomInfo.is_group && roomInfo.user_status === 'sent'" class="flex gap-base-2 p-base-2">
                <span class="waiting-request flex-1 p-base-2 border border-divider rounded-base-10xl text-center bg-gray-6 text-main-color dark:bg-slate-600 dark:text-slate-300 dark:border-slate-600">{{$t('Wait for request to be accepted')}}</span>
            </div>
            <div v-if="roomInfo.enable && roomInfo.status === 'accepted'" class="relative px-base-2 py-base-2 rounded-b-none md:rounded-b-base-lg">
                <div v-if="chatImagesUpload.length || chatImagesUploadLoading.length || chatFilesUpload.length" class="flex gap-base-2 overflow-x-auto overflow-y-hidden whitespace-nowrap py-base-2">
                    <template v-if="chatImagesUpload.length">
                        <div v-for="image in chatImagesUpload" :key="image.subject.id" class="flex-shrink-0 border border-divider inline-block w-16 h-16 bg-cover bg-center relative rounded-md dark:border-white/10" :style="{ backgroundImage: `url(${image.subject.url})`}">
                            <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removeChatImage(image.id)">
                                <BaseIcon name="close" size="16" />
                            </button>
                        </div>                     
                    </template>
                    <div v-for="index in chatImagesUploadLoading" :key="index" class="flex-shrink-0 inline-flex items-center justify-center w-16 h-16 bg-cover bg-center border border-divider dark:border-white/10 rounded-md">
                        <span class="loading-icon">
                            <div class="loader"></div>
                        </span>
                    </div>
                    <button v-if="chatImagesUpload.length || chatImagesUploadLoading.length" class="flex-shrink-0 inline-flex items-center justify-center w-16 h-16 text-main-color border border-divider dark:text-white/50 dark:border-white/10 rounded-md hover:bg-hover" @click="this.$refs.uploadChatImages.open()">
                        <BaseIcon name="photo" />
                    </button>
                    <template v-if="chatFilesUpload.length">
                        <div v-for="file in chatFilesUpload" :key="file.id" class="bg-web-wash border border-divider p-base-2 rounded-md relative max-w-[200px] w-full dark:bg-dark-web-wash dark:border-slate-700">
                            <div class="flex items-center gap-2">
                                <BaseIcon name="file"></BaseIcon>
                                <span class="truncate">{{ file.subject.name }}</span>
                            </div>
                            <button class="shadow-md inline-flex items-center justify-center absolute -top-2 -end-2 bg-white border border-divider text-main-color rounded-full w-5 h-5" @click="removeChatFile(file.id)">
                                <BaseIcon name="close" size="16" />
                            </button>
                        </div>
                    </template>
                </div>
                <div v-if="replyMessage" class="reply-status-item bg-web-wash p-base-2 absolute bottom-full left-0 right-0 w-full dark:bg-dark-web-wash z-10">
                    <div class="flex justify-between gap-base-2 text-sm font-bold mb-1">
                        {{$t('Replying to')}} {{ user.id === replyMessage.user.id ? $t('yourself') : replyMessage.user.name }}
                        <button @click="cancelReplyMessage()">
                            <BaseIcon name="close" size="16" class="reply-status-item-icon"/>
                        </button>
                    </div>
                    <div class="text-xs text-sub-color dark:text-slate-400">{{ replyType }}</div>
                </div>
                <div v-if="isRecording" class="flex gap-base-1">
                    <button @click="cancelRecording">
                        <BaseIcon name="x_circle" />
                    </button>
                    <div class="audio-container flex-1 flex justify-between items-center rounded-full px-base-2 py-1 bg-primary-color dark:bg-dark-primary-color">
                        <div ref="audioProgressRef" v-if="canPreviewVoice" class="audio-container-progress" :style="{animationDuration: `${duration}s`, animationPlayState: isPlayingVoice ? 'running' : 'paused'}"></div>
                        <div v-if="!canPreviewVoice" class="audio-container-progress" :style="{animationDuration: '30s', animationIterationCount: 'infinite'}"></div>
                        <div class="relative">
                            <button v-if="canPreviewVoice" @click="togglePreviewVoice"><BaseIcon :name="isPlayingVoice ? 'pause' : 'play'"/></button>
                            <button v-else @click="stopRecording"><BaseIcon name="pause"/></button>
                        </div>
                        <span class="bg-main-color text-white text-sm px-base-2 py-1 rounded-full">{{ formatDuration(timePlayed) }}</span>
                        <audio ref="voiceRef" :src="audioUrl"></audio>
                    </div>
                    <BaseButton @click="sendVoiceMessage()" :loading="loadingSendMessage" class="send-message-btn h-[38px] w-[38px] rtl:rotate-180" icon="send_message"></BaseButton>
                </div>
                <div v-else class="flex items-start gap-2 leading-none relative">
                    <div ref="chatIconsGroup" class="absolute top-0 start-0 flex items-center gap-base-1 h-9 self-start">
                        <DropdownMenu v-if="!isExpandingActions" appendTo="self">
                            <template v-slot:dropdown-button>
                                <BaseIcon name="plus_circle"/>
                            </template>
                            <template v-slot:dropdown-content>
                                <div class="w-44 p-2 space-y-base-2">
                                    <div v-if="config.wallet.enable" class="flex items-center gap-base-2" role="button" @click="handleClickSendFund(getOthersMemberInRoom(roomInfo.members)[0])">
                                        <BaseIcon name="coin" />
                                        <span>{{ $t('Send Fund') }}</span>
                                    </div>
                                    <div v-if="chatImagesUpload.length === 0" class="flex items-center gap-base-2" role="button" @click="this.$refs.uploadChatFiles.open()">
                                        <BaseIcon name="paperclip" />
                                        <span>{{ $t('Attach Files') }}</span>
                                    </div>
                                    <div v-if="chatFilesUpload.length === 0" class="flex items-center gap-base-2" role="button" @click="this.$refs.uploadChatImages.open()">
                                        <BaseIcon name="camera" />
                                        <span>{{ $t('Attach Photos') }}</span>
                                    </div>  
                                    <div v-if="enableTenorGifs" class="flex items-center gap-base-2" role="button" @click="this.$refs.uploadChatGifs.open()">
                                        <BaseIcon name="gif" />
                                        <span>{{ $t('Choose a GIF') }}</span>
                                    </div>
                                    <div v-if="enableAudioRecord" class="flex items-center gap-base-2" role="button" @click="startRecording">
                                        <BaseIcon name="microphone" />
                                        <span>{{ $t('Send a voice') }}</span>
                                    </div>
                                </div>
                            </template>
                        </DropdownMenu>
                        <button v-if="config.wallet.enable" @click="handleClickSendFund(getOthersMemberInRoom(roomInfo.members)[0])" v-tooltip.top="{value: $t('Send Fund'), showDelay: 1000}">
                            <BaseIcon name="coin" />
                        </button>
                        <UploadFiles v-if="chatImagesUpload.length === 0" ref="uploadChatFiles" @upload="uploadChatFiles" :tip="$t('Attach Files')" />
                        <UploadImages v-if="chatFilesUpload.length === 0" ref="uploadChatImages" @upload="uploadChatImages" :tip="$t('Attach Photos')" />
                        <TenorGifs v-if="enableTenorGifs" ref="uploadChatGifs" @upload="uploadChatGif" :tip="$t('Choose a GIF')" appendTo="self" :offsetX="chat.content ? ( this.actionItemWidth - this.$refs.chatIconsGroup?.offsetWidth ) : 0" />
                        <button v-if="enableAudioRecord" @click="startRecording" v-tooltip.top="{ value: $t('Send a voice'), showDelay: 1000 }">
                            <BaseIcon name="microphone" />
                        </button>
                    </div>
                    <div class="chat-form w-full flex gap-base-1 leading-none bg-white dark:bg-slate-800 border border-divider dark:border-white/10 rounded-md relative" :style="{ marginInlineStart: expandActionsChat + 'px', transition: 'margin 0.3s ease' }">
                        <EmojiTextField :placeholder="$t('Write Message')" :rows="1" ref="chat_content" class="max-h-24 border-0" v-model="chat.content" @update:modelValue="inputChange" @paste="pasteImage" @enter="sendMessages(roomInfo.id)" :autofocus="true"/>
                        <EmojiPicker ref="emojiPicker" @emoji_click="addEmoji" appendTo="self" class="absolute top-base-1 end-2" />
                    </div>
                    <div>
                        <BaseButton v-if="isMobile" @touchend.prevent="sendMessages()" class="send-message-btn h-[38px] w-[38px] rtl:rotate-180" icon="send_message" :loading="loadingSendMessage"></BaseButton>
                        <BaseButton v-else @click.prevent="sendMessages()" class="send-message-btn h-[38px] w-[38px] rtl:rotate-180" icon="send_message" :loading="loadingSendMessage"></BaseButton>
                    </div>
                </div>
            </div>
        </template>
    </UploadWrap>
    <div class="wave-form"><div class="wave-form-content"></div></div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { isVisible } from '@/utility';
import { uploadChatImages, uploadChatFiles, deleteChatItem, sendVoice, storeActiveRoom, aiSuggestion } from '@/api/chat';
import { uuidv4 } from '@/utility'
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Loading from '@/components/utilities/Loading.vue';
import EmojiPicker from '@/components/utilities/EmojiPicker.vue';
import EmojiTextField from '@/components/utilities/EmojiTextField.vue';
import InfiniteLoading from 'v3-infinite-loading'
import { useAuthStore } from '@/store/auth';
import { useChatStore } from '@/store/chat';
import { useAppStore } from '@/store/app';
import { useUtilitiesStore } from '@/store/utilities';
import UploadWrap from '@/components/layout/UploadWrap.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import ChatMessageItem from './ChatMessageItem.vue';
import SendFundChatModal from '@/components/modals/SendFundChatModal.vue'
import TenorGifs from '@/components/utilities/TenorGifs.vue'
import UploadFiles from '@/components/utilities/UploadFiles.vue'
import UploadImages from '@/components/utilities/UploadImages.vue'
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import AudioRecorder from 'audio-recorder-polyfill'
import mpegEncoder from 'audio-recorder-polyfill/mpeg-encoder'
AudioRecorder.encoder = mpegEncoder
AudioRecorder.prototype.mimeType = 'audio/mpeg'
window.MediaRecorder = AudioRecorder
var checkTypingTimer = null

export default {
    components: { Loading, BaseIcon, InfiniteLoading, EmojiPicker, EmojiTextField, UploadWrap, BaseButton, ChatMessageItem, TenorGifs, UploadFiles, UploadImages, DropdownMenu },
    props: ['room_id'],
    data(){
        return{
            currentPage: 1,
            chatImagesUpload: [],
			chatImagesUploadLoading: [],
            chat: {
				type: 'text',
				content: '',
				items: null
			},
            typing: false,
            typingTimer: false,
            typingSend: false,
            loadingSendMessage: false,
            showScrollToNewMessageBtn: false,
            chatFilesUpload: [],
            expandActionsChat: 0,
            spacingChatActionItem: 30,
            actionItemWidth: 20,
            isRecording: false,
            canPreviewVoice: false,
            isPlayingVoice: false,
            mediaRecorder: null,
            audioBlob: null,
            audioUrl: '',
            duration: 0,
            timePlayed: 0,
            durationTimer: null,
            recordingStartedAt: 0,
            key: uuidv4()
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useChatStore, ['loadingRoomMessagesList', 'roomMessagesList', 'roomInfo', 'loadmoreRoomMessagesList', 'replyMessage', 'doAddMessage']),
        ...mapState(useAppStore, ['isMobile', 'config']),
        ...mapState(useUtilitiesStore, ['currentAudioPlaying']),   
        replyType(){
            if(this.replyMessage.content){
                return this.replyMessage.content
            }
            switch (this.replyMessage.type) {
                case 'photo':
                    return this.$t('Images')
                case 'file':
                    return this.$t('Files')
                case 'send_fund':
                    return this.$t('Send Funds')
                case 'audio':
                    return this.$t('Voice Message')
                default:
                    return null
            }
        },
        isExpandingActions(){
            return !this.chat.content
        },
        enableAudioRecord(){
            return this.config.ffmegEnable && !this.isRecording && this.chatImagesUpload.length === 0 && this.chatFilesUpload.length === 0
        },
        enableTenorGifs(){
            return this.chatFilesUpload.length === 0 && this.config.chat.enable_gifs
        }
    },
    mounted(){
        this.getRoomMessages({roomId: this.room_id, page: this.currentPage})
        this.getRoomDetail(this.room_id)
        this.scrollToEnd()
        this.setExpandActionsChat()
        if(this.config?.ai_chat_profiles?.enable){
            this.fetchAiSuggestion();
        }
        document.addEventListener('visibilitychange', this.handleVisibilityChange);

        this.$nextTick(() => {
            this.startStoreActiveRoomInterval();
        });
    },
    updated(){
        this.setExpandActionsChat()
    },
    unmounted(){
        this.clearRoomDetail();
        this.clearRoomMessagesList();
        if (this.config.broadcastEnable && this.roomInfo && this.roomInfo.status == 'accepted') {
            window.Echo.leave(Constant.CHANNEL_ROOM + this.room_id)
        }
        this.setReplyMessage(null)
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);

        this.clearStoreActiveRoomInterval();
    },
    watch: {
        roomMessagesList: {
            handler: function() {
                if(isVisible(this.$refs.top_messages_list, this.$refs.messages_list)){
                    this.resetScroll()
                }
                if(isVisible(this.$refs.bottom_messages_list, this.$refs.messages_list)){
                    this.scrollToEnd()
                }
            },
            deep: true
        },
        roomInfo(roomInfo){
            if (roomInfo) {
                this.markSeenRoom(roomInfo.id)
                if (! this.config.broadcastEnable) {
                    this.pingNotification()
                }
                let self = this;
                if (this.config.broadcastEnable && roomInfo.status == 'accepted') {
                    window.Echo.private(Constant.CHANNEL_ROOM + roomInfo.id)
                        .listenForWhisper('typing', (e) => {                        
                        if (e.user_id == self.user.id) {
                            return
                        }
                        self.typing = true;

                        if (this.typing) {
                            if(isVisible(self.$refs.bottom_messages_list, self.$refs.messages_list)){
                                self.scrollToEnd()
                            }                            
                        }
                        
                        clearTimeout(checkTypingTimer)
                        checkTypingTimer = setTimeout(function() {
                            self.typing = false
                        }, 3000);
                    });
                }
                this.scrollToEnd()
                this.setExpandActionsChat()
            }
        },
        replyMessage(){
            this.$refs.chat_content.setContent(this.chat.content)
        },
        room_id(_, previousRoomId){
            this.getRoomMessages({roomId: this.room_id, page: this.currentPage})
            this.getRoomDetail(this.room_id)
            this.scrollToEnd()
            if (this.config.broadcastEnable && this.roomInfo && this.roomInfo.status == 'accepted') {
                window.Echo.leave(Constant.CHANNEL_ROOM + previousRoomId)
            }
        },
        isPlayingVoice(value) {
            if (value) {
                this.setCurrentAudioPlaying(`preview_audio_${this.key}`);
            }
        },
        currentAudioPlaying(audioPlaying){
            if(audioPlaying){
                if(audioPlaying !== `preview_audio_${this.key}`){
                    const audio = this.$refs.voiceRef;
                    if(audio){
                        audio.pause();
                    }
                    this.isPlayingVoice = false;
                    clearInterval(this.durationTimer);
                }
                this.stopRecording()
            }
        }
    },
    methods:{
        ...mapActions(useChatStore, ['getRoomMessages', 'getRoomDetail', 'doActionChatRoom', 'sendMessage','clearRoomDetail', 'markSeenRoom', 'clearRoomMessagesList', 'setRoomUserStatus', 'removeRoomId', 'setReplyMessage', 'doAddClientMessage', 'doRemoveMessageByClientMessageId']),
        ...mapActions(useUtilitiesStore, ['pingNotification', 'setShowChatBoxBottom', 'setCurrentAudioPlaying']),
        setExpandActionsChat() {
            if(this.chat.content){
                this.expandActionsChat = this.spacingChatActionItem;
            } else {
                setTimeout(() => {
                    const chatIconsGroupWidth = this.$refs.chatIconsGroup?.offsetWidth;
                    this.expandActionsChat = chatIconsGroupWidth + 5;
                }, 200);
            }
        },
        loadMoreRoomMessages($state){
            this.getRoomMessages({roomId: this.room_id, page: ++this.currentPage}).then((response) => {
                if(response.has_next_page){
                    $state.loaded()
                }else{
                    $state.complete()
                }
            })								
        },
        async doActionRoom(roomId, action){
            await this.doActionChatRoom({roomId, action})
            switch (action) {
                case 'accept':
                    if(this.showMiniChatBox()){
                        this.setShowChatBoxBottom(true, roomId, 'chat')
                    } else {
                        this.$router.push({
                            name: 'chat',
                            params: {
                                'room_id': roomId
                            }
                        });
                    }
                    break
                case 'block':
                case 'delete': {    
                    this.removeRoomId(roomId)    
                    if(this.showMiniChatBox()){
                        this.setShowChatBoxBottom(true, null, 'chat_requests')
                    }         
                    break
                }
            }
        },
        checkType() {
            if(this.chatImagesUpload.length){					
				this.chat.type = 'photo'
			}else if(this.chatFilesUpload.length){
                this.chat.type = 'file'
            }else{
				this.chat.type = 'text'
			}
            this.setExpandActionsChat()
		},
        uploadChatImages(event){
			this.startUploadChatImages(event.target.files)
		},
        dropChatImages(event){
			this.startUploadChatImages(event.dataTransfer.files)
		},
        pasteImage(event){
            this.startUploadChatImages(event.clipboardData.items, true)			
		},
        async startUploadChatImages(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i])
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    var fileContent = await this.convertImage(blob);
                    formData.append('file', fileContent, blob.name)
                    this.chatImagesUploadLoading.unshift(i)
                    try {
                        const response = await uploadChatImages(formData);
                        this.chatImagesUpload.push(response);
                        this.checkType()
                        this.chatImagesUploadLoading.shift()
                        this.$refs.chat_content.focus()
                    } catch (error) {
                        this.showError(error.error)
                        this.chatImagesUploadLoading.shift()
                        this.$refs.uploadChatImages.reset()
                    }	
                }
			}
            this.$refs.uploadChatImages.reset()
		},
        async removeChatImage(imageId) {
            try {
                await deleteChatItem({
                    id: imageId
                });
                this.chatImagesUpload = this.chatImagesUpload.filter(image => image.id !== imageId);
                this.checkType()
                this.$refs.chat_content.focus()
                this.$refs.uploadChatImages.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        uploadChatFiles(event){
			this.startUploadChatFiles(event.target.files)
		},
        async startUploadChatFiles(uploadedFiles, clipboard){
            if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
                if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i], 'chat')
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
                    try {
                        const response = await uploadChatFiles(formData);
                        this.chatFilesUpload.push(response);
                        this.checkType()
                        this.$refs.chat_content.focus()
                    } catch (error) {
                        this.showError(error.error)
                        this.$refs.uploadChatFiles.reset()
                    }	
                }
			}
            this.$refs.uploadChatFiles.reset()
		},
        async removeChatFile(fileId) {
            try {
                await deleteChatItem({
                    id: fileId
                });
                this.chatFilesUpload = this.chatFilesUpload.filter(file => file.id !== fileId);
                this.checkType()
                this.$refs.chat_content.focus()
                this.$refs.uploadChatFiles.reset()
            } catch (error) {
                this.showError(error.error)
            }
        },
        addEmoji(emoji){
            this.$refs.chat_content.addContent(emoji)	
		},
        uploadChatGif(file) {
            this.startUploadChatImages([file]);
        },
        inputChange(){
            if (this.config.broadcastEnable) {
                let channel = window.Echo.private(Constant.CHANNEL_ROOM + this.room_id);
                let self = this
                if (! this.typingSend) {
                    if (self.chat.content != '') {
                        channel.whisper('typing', {
                            user_id: self.user.id
                        });
                        this.typingSend = true
                    }
                    setTimeout(function() {
                        self.typingSend = false
                    }, 2000);
                }
            }
            this.setExpandActionsChat()
        },
        async sendMessages() {
            let data = null;
            try {

                //check chat message empty
                let check = false;
                if (this.chat.content != '' || this.chatImagesUpload.length || this.chatFilesUpload.length) {
                    check = true;
                }

                if (! check) {                         
                    return;
                }

                const client_message_id = `msg_${Date.now()}_${uuidv4()}`;

                let idItems = null;
                if (this.chatImagesUpload.length) {
                    idItems = this.chatImagesUpload.map(image => image.id);
                } else if(this.chatFilesUpload.length) {
                    idItems = this.chatFilesUpload.map(file => file.id);
                }

                data = {
                    client_message_id: client_message_id,
                    room_id: this.room_id,
                    type: this.chat.type,
                    content: this.chat.content,
                    items: idItems,
                    parent_message_id: this.replyMessage ? this.replyMessage.id : 0,
                    user_id: this.user.id
                }

                // optimistic update
                this.doAddClientMessage(data)

                // reset form data
                this.chat.type = 'text'
                this.chat.content = ''
                this.chatImagesUpload = []
                this.chatFilesUpload = []
                this.scrollToEnd()
                this.cancelReplyMessage()
                this.setExpandActionsChat()
                this.$refs.emojiPicker.close()
                if (! this.roomInfo.is_group && (this.roomInfo.user_status === 'created' || this.roomInfo.user_status === 'cancelled')) {
                    this.setRoomUserStatus('sent');
                }

                await this.sendMessage(data)
            } catch (error) {
                this.showError(error.error)
                if (data && data.client_message_id) {
                    this.doRemoveMessageByClientMessageId(data.client_message_id);
                }
            }
        },
        scrollToEnd(){
            setTimeout(() => {
                this.$nextTick(() => {
                    if (this.$refs.messages_list) {
                        this.$refs.messages_list.scrollTop = this.$refs.messages_list.scrollHeight
                    }
                })
            }, 100);
        },
        resetScroll(){
            var parentEl = this.$refs.messages_list
            var prevHeight = this.$refs.messages_list.scrollHeight
            this.$nextTick(() => {
                if (this.$refs.messages_list) {
                    this.$refs.messages_list.scrollTop = parentEl.scrollHeight - prevHeight
                }
            })
        },
        
        onScroll(){
            if(isVisible(this.$refs.bottom_messages_list, this.$refs.messages_list)){
                this.showScrollToNewMessageBtn = false
            }else{
                this.showScrollToNewMessageBtn = true
            }
        },
        cancelReplyMessage(){
            this.setReplyMessage(null)
        },
        handleClickSendFund(selectedUser){
            if (this.user) {
                let permission = 'wallet.send_fund'
                if(this.checkPermission(permission)){
                    this.$dialog.open(SendFundChatModal, {
                        data: {
                            selectedUser: [selectedUser],
                            messageData: {
                                room_id: this.room_id,
                                type: 'send_fund',
                                content: this.chat.content,
                                items: [],
                                parent_message_id: 0
                            }
                        },
                        props: {
                            header: this.$t('Send funds to') + ' ' + selectedUser.name,
                            class: 'send-fund-modal',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        }
                    })
                }
            }
        },
        formatDuration(seconds) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
        },
        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.mediaRecorder.start();
                this.isRecording = true;
                this.duration = 0;
                this.timePlayed = 0;
                this.audioChunks = [];
                this.recordingStartedAt = Date.now();
                this.durationTimer = setInterval(() => {
                    const elapsedSeconds = Math.floor((Date.now() - this.recordingStartedAt) / 1000);
                    this.timePlayed = elapsedSeconds;
                    this.duration = elapsedSeconds;
                }, 250);

                this.mediaRecorder.addEventListener('dataavailable', (event) => {
                    this.audioChunks.push(event.data);
                })
                this.setCurrentAudioPlaying()

                this.mediaRecorder.addEventListener('stop', () => {
                    const mime = (this.mediaRecorder && this.mediaRecorder.mimeType) ? this.mediaRecorder.mimeType : 'audio/mpeg';
                    const audioBlob = new Blob(this.audioChunks, { type: mime });
                    this.audioBlob = audioBlob;
                    this.audioUrl = URL.createObjectURL(this.audioBlob);
                    this.canPreviewVoice = true;
                    this.$nextTick(() => {
                        const audio = this.$refs.voiceRef;
                        if (audio) {
                            const setDurationFromMeta = () => {
                                if (!isNaN(audio.duration) && isFinite(audio.duration) && audio.duration > 0) {
                                    this.duration = Math.max(this.duration, Math.round(audio.duration));
                                }
                            };
                            if (audio.readyState >= 1) {
                                setDurationFromMeta();
                            } else {
                                audio.addEventListener('loadedmetadata', setDurationFromMeta, { once: true });
                            }
                        }
                    });
                })
            } catch (error) {
                this.showError(this.$t('No microphone connection. Please check your microphone settings.'));
            }
        },
        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
                clearInterval(this.durationTimer);
                this.durationTimer = null;
                if (this.mediaRecorder.stream) {
                    this.mediaRecorder.stream.getTracks().forEach((track) => track.stop());
                }
            }
        },
        cancelRecording() {
            this.isRecording = false;
            this.canPreviewVoice = false;
            this.isPlayingVoice = false;
            this.duration = 0;
            this.timePlayed = 0;
            setTimeout(() => {
                this.audioBlob = null;
                this.audioUrl = '';
            }, 50);
            clearInterval(this.durationTimer);
            this.durationTimer = null;
            if (this.mediaRecorder.stream) {
                this.mediaRecorder.stream.getTracks().forEach((track) => track.stop());
            }
            this.setExpandActionsChat();
        },
        togglePreviewVoice() {
            const audio = this.$refs.voiceRef;
            this.$refs.voiceRef.addEventListener('ended', () => {
                clearInterval(this.durationTimer);
                this.isPlayingVoice = false;
                this.timePlayed = this.duration;
                this.$refs.audioProgressRef.style.animationName = 'none';
            });
            if (audio.paused) {
                audio.play();
                this.isPlayingVoice = true;
                if (this.timePlayed > 0) {
                    this.durationTimer = setInterval(() => {
                        this.timePlayed -= 1;
                    }, 1000);
                } else {
                    this.timePlayed = this.duration;
                }
                this.$refs.audioProgressRef.style.animationName = 'waveform-progress-animation';
            } else {
                audio.pause();
                this.isPlayingVoice = false;
                clearInterval(this.durationTimer);
            }
        },
        sendVoiceMessage() {
            this.stopRecording()
            setTimeout( async() => {
                if (this.audioBlob) {
                    if (this.loadingSendMessage)  {
                        return;
                    }
                    this.loadingSendMessage = true;
                    const formData = new FormData();
                    formData.append('room_id', this.room_id)
                    formData.append('file', this.audioBlob, 'audio.mp3');
                    formData.append('duration', this.duration)
                    formData.append('parent_message_id', this.replyMessage ? this.replyMessage.id : 0)
                    try {
                        const response = await sendVoice(formData);
                        this.doAddMessage(response)
                        this.cancelRecording()
                        this.cancelReplyMessage()
                        this.scrollToEnd()
                    } catch (error) {
                        this.showError(error.error);
                    } finally {
                        this.loadingSendMessage = false;
                    }
                }
            }, 100);
        },
        handleVisibilityChange() {
            if (document.visibilityState === 'hidden') {
                const audio = this.$refs.voiceRef;
                if(audio){
                    audio.pause();
                }
                this.isPlayingVoice = false;
                clearInterval(this.durationTimer);
                this.setCurrentAudioPlaying()
                this.stopRecording()
                this.clearStoreActiveRoomInterval();
            } else if (document.visibilityState === 'visible') {
                this.startStoreActiveRoomInterval();
            }
        },
        async handleStoreActiveRoom(roomId){
            try {
                await storeActiveRoom(roomId)
            } catch (error) {
                this.showError(error.error)
            }
        },
        startStoreActiveRoomInterval() {
            this.clearStoreActiveRoomInterval();
            if (document.visibilityState === 'visible') {
                this.handleStoreActiveRoom(this.room_id);
                this.storeActiveRoomInterval = setInterval(() => {
                    this.handleStoreActiveRoom(this.room_id);
                }, 5000);
            }
        },
        clearStoreActiveRoomInterval() {
            if (this.storeActiveRoomInterval) {
                clearInterval(this.storeActiveRoomInterval);
                this.storeActiveRoomInterval = null;
            }
        },
        async fetchAiSuggestion() {
            try {
                const response = await aiSuggestion(this.room_id)
                this.chat.content = response;
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>