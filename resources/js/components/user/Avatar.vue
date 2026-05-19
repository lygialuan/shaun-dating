<template>
    <UserInfoPopover :userProps="user" :activePopover="activePopover" :style="{width: `${size}px`, height: `${size}px`}" class="flex-shrink-0">
        <div class="relative w-full h-full">
            <button v-if="router" @click="handleOpenProfile(user)">
                <img :src="user.avatar" class="rounded-full object-cover object-center" :style="{width: `${size}px`, height: `${size}px`}"/>
                <div v-if="border" class="absolute inset-0 rounded-full border border-media-inner dark:border-media-inner-dark"></div>
            </button>
            <div v-else>
                <img :src="user.avatar" class="rounded-full object-cover object-center" :style="{width: `${size}px`, height: `${size}px`}"/>
                <div v-if="border" class="absolute inset-0 rounded-full border border-media-inner dark:border-media-inner-dark"></div>
            </div>
            <span v-if="progress" class="absolute flex items-center justify-center border-2 border-[#2C2C2C] bg-[#2C2C2C] text-white rounded-md z-10 p-2" :class="isSmall ? 'top-7 -end-1 text-[8px] w-[23px] h-[14px]' : 'hidden'">{{ progress + '%' }}</span>
        </div>
   </UserInfoPopover>
</template>
<script>
import { defineAsyncComponent } from 'vue';
const UserInfoPopover = defineAsyncComponent(() => import('@/components/user/UserInfoPopover.vue'));

export default {
    components: { UserInfoPopover },
    props: {
        user: {
            type: Object,
            default: null
        },
        size: {
            type: Number,
            default: 40
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        border: {
            type: Boolean,
            default: true
        },
        router: {
            type: Boolean,
            default: true
        },
        tab: {
            type: String,
            default: ''
        },
        progress: {
            type: Number,
            default: 0
        },
        showProgressBadge: {
            type: Boolean,
            default: false
        },
        hiddenOpen: {
            type: Boolean,
            default: false
        }
    },
    computed: {
        isSmall(){
            return this.size == 50
        }
    },
    methods: {
        handleOpenProfile(user){
            if(this.hiddenOpen) return
			this.openProfile({ user })
		},
    }
}
</script>