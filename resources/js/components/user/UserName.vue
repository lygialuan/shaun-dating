<template>
    <div v-if="truncate" class="flex items-center gap-1 max-w-full font-semibold min-w-0">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline-block min-w-0 max-w-full">
            <button v-if="router" class="base-username flex items-center w-full min-w-0 overflow-hidden text-inherit text-left" @click="handleOpenProfile(user)">
                <span class="truncate flex-1 min-w-0">{{ user.name }}</span>
                <span v-if="user.gender && showGender" class="shrink-0">, {{ user.gender }}</span>
                <span v-if="user.age && showAge && user.can_view_age && !showGender" class="shrink-0">, {{ user.age }}</span>
                <span v-if="user.age && showAge && user.can_view_age && showGender" class="shrink-0 ml-1">{{ user.age }}</span>
            </button>
            <div v-else class="base-username flex text-inherit">
                <span class="truncate">{{user.name}} </span>
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="w-4 h-4 bg-white/90 border border-white/60 rounded-full" :src="config.userVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Account'), showDelay: 2500} : null"/>
            <img v-if="user.is_page" class="w-4 h-4 bg-white/90 border border-white/60 rounded-full" :src="config.userPageVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Page'), showDelay: 2500} : null"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="w-4 h-4" :src="config.user_page.featureBadgeIcon" v-tooltip="!isMobile ? {value: $t('Featured Page'), showDelay: 2500} : null"/>
        <span v-if="user.badge && showBadge" class="inline-block text-xxs font-normal leading-none max-w-[200px] truncate p-1 border rounded" 
            :style="{'background-color': user.badge.background_color, 'border-color': user.badge.border_color, 'color': user.badge.text_color}">
            {{ user.badge.badge_name }}
        </span>
    </div>
    <div v-else class="inline break-word font-semibold">
        <UserInfoPopover :user-props="user" :active-popover="activePopover" class="inline">
            <router-link v-if="user.id && router" :to="{name: 'profile', params: { user_name: user.user_name, tab:tab }}" :target="target" class="base-username inline text-inherit">
                {{user.name}}
            </router-link>
            <div v-else class="base-username inline text-inherit">
                {{user.name}}
            </div>
        </UserInfoPopover>
        <template v-if="config.userVerifyEnable && user.is_verify">
            <img v-if="!user.is_page" class="inline w-4 h-4 ms-1" :src="config.userVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Account'), showDelay: 2500} : null"/>
            <img v-if="user.is_page" class="inline w-4 h-4 ms-1" :src="config.userPageVerifyBadgeIcon" v-tooltip="!isMobile ? {value: $t('Verified Page'), showDelay: 2500} : null"/>
        </template>
        <img v-if="user.is_page && user.is_page_feature" class="inline w-4 h-4 ms-1" :src="config.user_page.featureBadgeIcon" v-tooltip="!isMobile ? {value: $t('Featured Page'), showDelay: 2500} : null"/>
        <span v-if="user.badge && showBadge" class="inline-block text-xxs font-normal leading-none align-text-bottom max-w-[200px] truncate p-1 border rounded ms-1" 
            :style="{'background-color': user.badge.background_color, 'border-color': user.badge.border_color, 'color': user.badge.text_color}">
            {{ user.badge.badge_name }}
        </span>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import { defineAsyncComponent } from 'vue';
const UserInfoPopover = defineAsyncComponent(() => import('@/components/user/UserInfoPopover.vue'));

export default {
    components: { UserInfoPopover },
    inject: {
        dialogRef: { default: null }
    },
    props: {
        user: {
            type: Object,
            default: null
        },
        target: {
            type: String,
            default: ''
        },
        activePopover: {
            type: Boolean,
            default: true
        },
        router: {
            type: Boolean,
            default: true
        },
        truncate: {
            type: Boolean,
            default: true
        },
        tab: {
            type: String,
            default: ''
        },
        showBadge: {
            type: Boolean,
            default: true
        },
        showAge: {
            type: Boolean,
            default: false
        },
        showGender: {
            type: Boolean,
            default: false
        }
    },
    computed:{
		...mapState(useAppStore, ['config', 'isMobile'])
	},
    methods: {
        handleOpenProfile(user) {
            if (document.querySelector('.p-dialog-profile')) return
            this.openProfile({ user })
        }
    }
}
</script>