<template>
    <div class="main-content-section fixed inset-0 z-[999] h-[100svh] p-0 m-0 lg:relative lg:h-chat lg:z-0">
        <div class="flex flex-col h-full">
            <div v-if="chatbotProvider" class="flex items-center gap-3 p-base-2 lg:p-5 shadow-sm dark:shadow-slate-600 relative">
                <div class="w-6">
                    <router-link v-if="screen.lg" :to="{ name: 'home' }" class="inline-block text-inherit">
                        <BaseIcon name="caret_left" />
                    </router-link>
                </div>
                <div class="flex-1 text-center">
                    <div class="text-2xl font-bold">{{ chatbotProvider.name }}</div>
                </div>
                <DropdownMenu>
                    <template v-slot:dropdown-button>
                        <BaseIcon name="more_horiz_outlined" />
                    </template>
                    <template v-slot:dropdown-content>
                        <ul class="text-sm min-w-[150px]">
                            <li class="rounded hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-white">
                                <button @click="handleClearChatbotHistory" class="block w-full text-start p-2">{{$t('Clear history')}}</button>
                            </li>                                
                        </ul>
                    </template>
                </DropdownMenu>
            </div>
            <div ref="messagesListRef" class="flex-1 space-y-4 overflow-auto p-base-2 lg:p-5 ai-chat-mask-gradient">
                <span ref="topMessagesRef" class="absolute top-5">&nbsp;</span>
                <InfiniteLoading @infinite="loadmoreMessages">
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    class="w-fit max-w-10/12 p-base-2 rounded-t-2xl overflow-x-auto"
                    :class="message.role === 'user' ?
                    'bg-primary-color border-primary-color text-white ms-auto rounded-bl-2xl rounded-br dark:bg-dark-primary-color dark:border-dark-primary-color' :
                    'bg-chat-incoming-background-color border-chat-incoming-border-color text-main-color rounded-br-2xl rounded-bl dark:bg-dark-message dark:border-dark-message dark:text-white'"
                    >
                    <div v-html="parseMarkdown(message.content)"></div>
                </div>
                <div v-if="loadingSend" class="loader-dots"></div>
                <span ref="bottomMessagesRef"></span>
            </div>
            <div class="px-base-2 pb-base-2 lg:px-5 lg:pb-5">
                <div class="bg-web-wash border border-divider px-4 py-3 rounded-lg w-full relative dark:bg-dark-web-wash dark:border-white/10">
                    <BaseTextarea
                        v-model="messageContent"
                        classInput="!border-none !px-0 !shadow-none !bg-transparent"
                        autoResize
                        autofocus
                        :rows="1"
                        :placeholder="$t('Ask anything')"
                        @keydown="handleKeydown"
                    />
                    <div class="flex gap-3">
                        <div class="flex-1"></div>
                        <BaseButton :loading="loadingSend" :disabled="!messageContent" icon="send_message" class="rounded-full" @click="handleSendMessage(messageContent)" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { isVisible } from '@/utility';
import { getChatbotProvider, getChatbotMessages, sendChatbotMessage, clearChatbotHistory } from '@/api/chatbot'
import { marked } from 'marked'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Constant from '@/utility/constant'

export default {
    components: {
        InfiniteLoading,
        Loading,
        BaseTextarea,
        BaseButton,
        DropdownMenu,
        BaseIcon
    },
    data(){
        return{
            chatbotProvider: null,
            messages: [],
            page: 1,
            messageContent: '',
            loadingSend: false
        }
    },
    computed: {
		...mapState(useAppStore, ['config', 'screen'])
	},
    watch: {
        messages: {
            handler: function() {
                if(isVisible(this.$refs.topMessagesRef, this.$refs.messagesListRef)){
                    this.resetScroll()
                }
                if(isVisible(this.$refs.bottomMessagesRef, this.$refs.messagesListRef)){
                    this.scrollToEnd()
                }
            },
            deep: true
        }
    },
    mounted(){
        if(this.config.chatbot.enable){
            this.getChatbotProvider()
            this.handleGetMessages(this.page)
            this.scrollToEnd()
            marked.setOptions({
                mangle: false,
                headerIds: false
            });
        } else {
            this.setErrorLayout(true)
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        resetScroll(){
            var parentEl = this.$refs.messagesListRef
            var prevHeight = this.$refs.messagesListRef.scrollHeight
            this.$nextTick(() => {
                if (this.$refs.messagesListRef) {
                    this.$refs.messagesListRef.scrollTop = parentEl.scrollHeight - prevHeight
                }
            })
        },
        scrollToEnd(){
            setTimeout(() => {
                this.$nextTick(() => {
                    if (this.$refs.messagesListRef) {
                        this.$refs.messagesListRef.scrollTop = this.$refs.messagesListRef.scrollHeight
                    }
                })
            }, 100);
        },
        async handleGetMessages(page){
            if(this.config.chatbot.enable){
                try {
                    const response = await getChatbotMessages(page)
                    if(page === 1){
                        this.messages = []
                    }
                    this.messages = window._.concat(window._.reverse(response), this.messages);
                    return response
                } catch (error) {
                    console.log(error);
                }
            }
        },
        async handleSendMessage(message){
            if(this.loadingSend) return;
            this.loadingSend = true
            this.messages.push({role: 'user', content: message})
            this.messageContent = ''
            this.scrollToEnd()
            try {
                const response = await sendChatbotMessage({
                    context: [],
                    message: message
                })
                this.messages.push({content: response.response})
                this.scrollToEnd()
            } catch (error) {
                if (error.error.code == Constant.RESPONSE_CODE_MEMBERSHIP_PERMISSION) {
                    this.showPermissionPopup('chatbot', error.error.message);
                } else {
                    this.showError(error.error);
                }
                this.messages.pop();
            } finally {
                this.loadingSend = false
            }
        },
        loadmoreMessages($state){
            this.handleGetMessages(++this.page).then((response) => {
                if(response.length === 0){
                    $state.complete()
                }else{
                    $state.loaded()
                }
            })
        },
        handleKeydown(e){
            if (e.key === 'Enter') {
                if(!e.shiftKey){
                    e.preventDefault()
                    if(this.messageContent){
                        this.handleSendMessage(this.messageContent)
                    }
                }
            }
        },
        async getChatbotProvider(){
            try {
                const response = await getChatbotProvider()
                this.chatbotProvider = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleClearChatbotHistory(){
            this.$confirm.require({
                message: this.$t('Do you want to clear all chat history?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
					try {
                        const response = await clearChatbotHistory()
                        this.messages = []
                        this.showSuccess(response.message)
                    } catch (error) {
                        this.showError(error.error)
                    }
                }
            })
        },
        parseMarkdown(markdownText) {
            return marked(markdownText);
        }
    }
}
</script>
