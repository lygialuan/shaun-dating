<template>
    <component :is="notificationContentComponent"
            :href="href ? notificationLink : null"
            :to="!href ? notificationLink : null"
            class="notification-item flex gap-x-base-2 text-left mb-3 rounded-md text-main-color dark:text-white cursor-pointer"
            :class="{'notification-item-active': !notificationItem.is_seen}"
            @click="markSeen(notificationItem)">
        <Avatar :user="notificationItem.from" :activePopover="false" />
        <div class="flex-1" :class="{'opacity-70': notificationItem.is_seen}">
            <div class="notification-item-message leading-tight mb-1">
                <b v-if="!notificationItem.is_system" class="notification-item-name">{{ notificationItem.from.name + ' ' }}</b>
                <slot name="text"></slot>
            </div>
            <div class="mb-1"><slot></slot></div>
            <p class="notification-item-date text-xs"
            :class="!notificationItem.is_seen ? 'font-semibold text-primary-color dark:text-dark-primary-color' : 'text-sub-color dark:text-slate-400'">
                {{ notificationItem.created_at }}
            </p>
        </div>
        <div class="self-center ps-base-1">
            <span class="notification-item-dot inline-block h-base-2 w-base-2 rounded-full"
                :class="{'bg-primary-color dark:bg-dark-primary-color': !notificationItem.is_seen}">
            </span>
        </div>
    </component>
</template>
<script>
import { mapState } from 'pinia';
import { useAuthStore } from '@/store/auth';
import { markSeenNotification } from '@/api/notification';
import Avatar from '@/components/user/Avatar.vue'

export default {
    components: { Avatar },
    props: {
        notificationItem: {
			type: Object,
			default: null
		},
        notificationLink: {
			default: null
		},
        href: {
			type: Boolean,
			default: false
		},
        profile: {
			type: Boolean,
			default: false
		},
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        notificationContentComponent(){
            return this.notificationLink ? (this.href ? 'a' : 'router-link') : 'div'
        }
    },
    methods:{
        async markSeen(notificationItem){
            try {
                if(this.profile){
                    this.openProfile({ user: this.user })
                }
                if(!notificationItem.is_seen){
                    await markSeenNotification({
                        id: notificationItem.id
                    })
                }
            } catch (error) {
                //this.showError(error.error)
            }
        }
    }
}
</script>