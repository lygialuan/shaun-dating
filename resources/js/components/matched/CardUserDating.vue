<template>
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 sm:gap-4 p-2 md:p-0">
        <AvatarDating v-for="userInfo in users" :key="userInfo.id" :user="userInfo" @click="handleOpenProfile(userInfo)" :action="action" :allowSeeProfilePrivate="this.user.permissions['dating.allow_see_profile_who_view_you_privately']"/>
        <SkeletonExploreList v-if="loading"/>
    </div>
    <div v-if="users.length == 0 && !loading">
        <EmptyDataUsersPageExplore v-if="isPageExplore"/>
        <EmptyDataUsers v-else/>
    </div>
</template>

<script>
import { mapState } from "pinia";
import { useAuthStore } from '@/store/auth'
import AvatarDating from '@/components/user/AvatarDating.vue'
import SkeletonExploreList from '@/components/skeletons/SkeletonExploreList.vue'
import EmptyDataUsersPageExplore from '@/components/user/EmptyDataUsersPageExplore.vue'
import EmptyDataUsers from '@/components/user/EmptyDataUsers.vue'

export default {
    components: { 
		AvatarDating,
		SkeletonExploreList,
        EmptyDataUsersPageExplore,
        EmptyDataUsers
	},
    props: {
        users: {
            type: Array,
            default: null
        },
        loading: {
            type: Boolean,
            default: false
        },
        isPageExplore: {
            type: Boolean,
            default: true
        },
        action: {
            type: String,
            default: ''
        },
        hiddenSwipe: {
            type: Boolean,
            default: false
        },
    },
    computed: {
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        handleOpenProfile(user) {
            if (!this.canOpenProfile(user)) return

            return this.openProfile({
                user,
                hiddenSwipe: this.hiddenSwipe
            })
        },
        canOpenProfile(user) {
            const isViewedMe = this.action === 'viewed_me'
            const isPrivateProfile = user?.can_prowse_profile_privately
            const hasPermission = this.checkPermission('dating.allow_see_profile_who_view_you_privately')

            if (isViewedMe && isPrivateProfile && !hasPermission) {
                return false
            }

            return true
        }
    }
}
</script>