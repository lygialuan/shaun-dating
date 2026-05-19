<template>
    <h1 v-if="adminConfig" class="page-title mb-base-2 truncate">
        <router-link :to="{ name: 'group_profile', params: { id: adminConfig.group.id, slug: adminConfig.group.slug }}" class="text-inherit">{{ adminConfig.group.name }}</router-link>
    </h1>
    <TabsMenu :menus="groupReportMenus" @select="changeTab" type="secondary" class="mb-base-2" />
    <Component v-if="adminConfig && adminConfig.group.status === 'active'" :is="groupComponent" :admin-config="adminConfig" :sub-tab="sub_tab" @updated="handleGetAdminConfig(id)" />
</template>

<script>
import { getAdminManageConfig } from '@/api/group'
import { changeUrl } from '@/utility'
import TabsMenu from '@/components/menu/TabsMenu.vue'
import GroupInsights from '@/pages/group_manage/GroupInsights.vue'
import GroupManageMembers from '@/pages/group_manage/GroupManageMembers.vue'
import GroupManagePosts from '@/pages/group_manage/GroupManagePosts.vue'
import GroupManageSettings from '@/pages/group_manage/GroupManageSettings.vue'

export default {
    props: ['id', 'tab', 'sub_tab'],
    components: { TabsMenu },
    data(){
        return{
            currentTab: this.tab ? this.tab : '',
            adminConfig: null
        }
    },
    computed:{
        groupReportMenus(){
			return [
				{ icon: 'info', name: this.$t('Insights'), isShow: true, isActive: this.currentTab === '', tab: ''},
				{ icon: 'users', name: this.$t('Manage Members'), isShow: true, isActive: this.currentTab === 'groups_manage_members', tab: 'groups_manage_members', badge: this.shortenNumber(this.adminConfig?.member_request_count)},
                { icon: 'feeds', name: this.$t('Manage Posts'), isShow: true, isActive: this.currentTab === 'groups_manage_posts', tab: 'groups_manage_posts', badge: this.shortenNumber(this.adminConfig?.post_pending_count) },
				{ icon: 'bell', name: this.$t('Settings'), isShow: true, isActive: this.currentTab === 'groups_manage_settings', tab: 'groups_manage_settings'}
			]
		},
        groupComponent() {
			switch(this.currentTab){
                case 'groups_manage_members':
                    return GroupManageMembers;
                case 'groups_manage_posts':
                    return GroupManagePosts;
                case 'groups_manage_settings':
                    return GroupManageSettings;
				default: 
					return GroupInsights;
			}
		}
    },
    watch: {
        adminConfig(newVal){
            if(newVal.group.status !== 'active'){
                return this.$router.push({ 'name': 'permission' })
            }
        }
    },
    mounted(){
        this.handleGetAdminConfig(this.id)
    },
    methods: {
        changeTab(name) {
			this.currentTab = name
			let groupUrl = this.$router.resolve({
				name: 'groups_manage',
				params: { id: this.id }
			});
			changeUrl(groupUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        async handleGetAdminConfig(groupId){
            try {
                const response = await getAdminManageConfig(groupId)
                this.adminConfig = response
            } catch (error) {
                this.$router.push({ 'name': 'permission' })
            }
        }
    }
}
</script>