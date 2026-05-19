<template>
    <h1 v-if="userConfig" class="page-title mb-base-2">
        <router-link :to="{ name: 'group_profile', params: { id: userConfig.group.id, slug: userConfig.group.slug }}" class="text-inherit">{{ userConfig.group.name }}</router-link>
    </h1>
    <Component v-if="userConfig" :is="groupComponent" :user-config="userConfig" />
</template>

<script>
import { getUserManageConfig } from '@/api/group'
import GroupMyPendingPosts from '@/pages/group_profile/GroupMyPendingPosts.vue'

export default {
    props: ['id', 'tab'],
    data(){
        return{
            currentTab: this.tab,
            userConfig: null
        }
    },
    computed:{
        groupComponent() {
			return GroupMyPendingPosts
		}
    },
    mounted(){
        this.handleGetUserConfig(this.id)
    },
    methods: {
        async handleGetUserConfig(groupId){
            try {
                const response = await getUserManageConfig(groupId)
                this.userConfig = response
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>