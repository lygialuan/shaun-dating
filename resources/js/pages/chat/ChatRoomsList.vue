<template>
    <div class="mb-base-2" v-if="!hiddenSearch">
        <input type="text" @input="searchRoom(searchKeyword)" v-model="searchKeyword" :placeholder="$t('Search')" class="room_search_bar w-full bg-transparent border border-divider rounded-base-10xl px-base-2 py-base-1 placeholder-sub-color appearance-none outline-none dark:bg-dark-form-surface dark:border-white/10 dark:placeholder:text-slate-300">
    </div>
    <template v-if="haveSearchRoomsList">
        <div v-if="searchRoomsList.length > 0">
            <ChatRoomItem v-for="chatRoom in searchRoomsList" :key="chatRoom.id" :chatRoom="chatRoom" :room_id="Number(room_id)" :showAction="false" :has-router="hasRouter" @updateRoomId="updateRoomId"/>
        </div>
        <div v-else class="text-center">{{$t('No Room Found')}}</div>
    </template>
    <div v-else class="chat_rooms_list flex-1 h-full overflow-auto scrollbar-thin">
        <SkeletonRoomsList v-if="loadingRoomsList"/>
        <template v-else>
            <template v-if="roomsList.length > 0">
                <ChatRoomItem v-for="chatRoom in roomsList" :show-icon-chat="showIconChat" :key="chatRoom.id" :chatRoom="chatRoom" :room_id="Number(room_id)" :has-router="hasRouter" @updateRoomId="updateRoomId"/>
                <InfiniteLoading v-if="loadmoreRoomsList" @infinite="loadMoreRooms">				
                    <template #spinner>
                        <Loading />
                    </template>
                    <template #complete><span></span></template>
                </InfiniteLoading>
            </template>
            <div v-else>
                <EmptyDataUsers v-if="showIconChat"/>
                <div class="text-center" v-else>{{$t('No Chat Rooms Available')}}</div>
            </div>
        </template>
    </div>
</template>

<script>
import { mapActions, mapState } from 'pinia';
import { getSearchRoomsList, getSearchRequestRoomsList } from '@/api/chat'
import { useChatStore } from '@/store/chat';
import { useAppStore } from '@/store/app';
import Loading from '@/components/utilities/Loading.vue';
import InfiniteLoading from 'v3-infinite-loading'
import ChatRoomItem from '@/pages/chat/ChatRoomItem.vue';
import SkeletonRoomsList from '@/components/skeletons/SkeletonRoomsList.vue'
var typingTimer = null;
import EmptyDataUsers from '@/components/user/EmptyDataUsers.vue'

export default {
    components: { Loading, InfiniteLoading, ChatRoomItem, SkeletonRoomsList, EmptyDataUsers },
    props: ['type', 'room_id', 'hasRouter', 'hiddenSearch', 'showIconChat'],
    data(){
        return{
            currentPage: 1,
            searchKeyword: '',
            searchRoomsList: [],
            haveSearchRoomsList: false
        }
    },
    mounted(){
        if(this.type === 'chat_requests'){
            this.getRequestRoomsList(this.currentPage)
        }else{
            this.getRoomsList(this.currentPage)
        }
    },
    computed:{
        ...mapState(useChatStore, ['loadingRoomsList', 'roomsList', 'loadmoreRoomsList']),
        ...mapState(useAppStore, ['screen', 'config'])
    },
    watch:{
        type(){
            if(this.type === 'chat_requests'){
                this.getRequestRoomsList(this.currentPage)
            }else{
                this.getRoomsList(this.currentPage)
            }
        }
    },
    created(){
        this.interval = setInterval(() =>{
        this.pingRoomOnline()}, this.config.notificationInterval)
	},
    unmounted(){
        if(!this.screen.md){
            this.clearRoomList();
        }
        clearInterval(this.interval)
    },
    methods: {
        ...mapActions(useChatStore, ['getRoomsList', 'getRequestRoomsList', 'clearRoomList', 'pingRoomOnline']),
        loadMoreRooms($state){
            if(this.type === 'chat_requests'){
                this.getRequestRoomsList(++this.currentPage).then((response) => {
                    if(response.has_next_page){
                        $state.loaded()
                    }else{
                        $state.complete()
                    }
                })	
            }else{
                this.getRoomsList(++this.currentPage).then((response) => {
                    if(response.has_next_page){
                        $state.loaded()
                    }else{
                        $state.complete()
                    }
                })	
            }         							
        },
        updateRoomId(roomId){
            this.$emit('updateRoomId', roomId)
            this.searchKeyword = ''
            this.haveSearchRoomsList = false
        },
        searchRoom(searchKeyword){
			clearTimeout(typingTimer);
			typingTimer = setTimeout(() => {
                if(searchKeyword){
                    if(this.type === 'chat_requests'){
                        this.getSearchRequestRoomsList(searchKeyword)
                    }else{
                        this.getSearchRoomsList(searchKeyword)
                    }
                }else{
                    this.haveSearchRoomsList = false
                }      
            }, 400);
		},
        async getSearchRoomsList(keyword){
            try {
                const response = await getSearchRoomsList(keyword)
                this.searchRoomsList = response
                this.haveSearchRoomsList = true
            } catch (error) {
                console.log(error)
            }
        },
        async getSearchRequestRoomsList(keyword){
            try {
                const response = await getSearchRequestRoomsList(keyword)
                this.searchRoomsList = response 
                this.haveSearchRoomsList = true
            } catch (error) {
                console.log(error)
            }
        }
    },
    emits: ['updateRoomId']
}
</script>