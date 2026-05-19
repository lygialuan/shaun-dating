<template>
	<div v-if="usersList.length > 0" class="boxes-list flex flex-wrap gap-1">
		<div v-for="userItem in usersList" :key="userItem.id" class="boxes-list-item inline-flex items-center p-1 border border-secondary-box-color rounded-base dark:border-white/30">
            <Avatar :user="userItem" :activePopover="false" :size="24" :target="target" :router="router" />
            <router-link :to="{name: 'profile', params: {user_name: userItem.user_name}}" :class="{ 'pointer-events-none': !router }" class="text-xs text-inherit font-bold ms-base-1 me-2" :target="target">{{userItem.name}}</router-link>
            <button @click="handleRemoveUser(userItem)" class="leading-none">
                <BaseIcon name="close" size="16"></BaseIcon>
            </button>
        </div>
	</div>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Avatar from '@/components/user/Avatar.vue'

export default {
    components: { BaseIcon, Avatar },
    props: {
        usersList: {
            type: Array,
            default: () => []
        },
        target: {
            type: String,
            default: ''
        },
        router: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        async handleRemoveUser(user) {
            this.$emit('remove_user', user)
        }
    },
    emits: ['remove_user']
}
</script>