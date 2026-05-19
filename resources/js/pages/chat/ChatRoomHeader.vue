<template>
    <div v-if="roomInfo">
        <div class="chat-header-wrap flex items-center gap-3 px-5 py-base-2 rounded-t-none md:rounded-t-base-lg">
            <router-link :to="{ name: this.$route.name, force: true}" class="text-inherit rtl:rotate-180 md:hidden">
                <BaseIcon name="arrow_left" class="chat-header-wrap-icon align-middle"></BaseIcon>						
            </router-link>
            <div class="flex items-center flex-1 min-w-0">
                <Avatar v-for="member in getOthersMemberInRoom(roomInfo.members)" :key="member.id" :user="member" :activePopover="false" />
                <div class="mx-base-2 min-w-0">
                    <UserName v-for="member in getOthersMemberInRoom(roomInfo.members)" :key="member.id" :user="member" :activePopover="false" class="text-base-lg" />
                </div>
                <div v-if="roomInfo.is_online" class="block w-base-2 h-base-2 min-w-[0.625rem] bg-base-green rounded-full"></div>
            </div>
            <button v-if="roomInfo.status === 'accepted'" @click="openDropdownMenu()">
                <BaseIcon name="more_horiz_outlined" class="chat-header-wrap-icon text-sub-color dark:text-slate-400"></BaseIcon>                
            </button>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { checkPopupBodyClass } from '@/utility';
import { useAuthStore } from '@/store/auth';
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'
import ChatOptionsMenu from '@/pages/chat/ChatOptionsMenu.vue'

export default {
    props: ['roomInfo'],
    components: { BaseIcon, Avatar, UserName },
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    methods:{
        openDropdownMenu(){
            this.$dialog.open(ChatOptionsMenu, {
                data: {
                    room: this.roomInfo
                },
                props:{
                    showHeader: false,
                    class: 'dropdown-menu-modal',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                },
                onClose: () => {
                    checkPopupBodyClass();
                }
            });
        },
    }
}
</script>