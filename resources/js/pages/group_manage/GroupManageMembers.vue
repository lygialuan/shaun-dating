<template>
    <div class="main-content-section">
        <TabsMenu :menus="subMenus" @select="changeTab" class="mb-4"/>
        <Component v-if="adminConfig" :is="manageMemberComponent" :admin-config="adminConfig" @updated="handleUpdateData" />
    </div>
</template>

<script>
import { changeUrl } from '@/utility'
import Moderators from './group_manage_member/Moderators.vue';
import PendingRequests from './group_manage_member/PendingRequests.vue';
import Members from './group_manage_member/Members.vue';
import Bans from './group_manage_member/Bans.vue';
import TabsMenu from '@/components/menu/TabsMenu.vue';

export default {
    props: ['adminConfig', 'subTab'],
    components: { TabsMenu },
    data(){
        return{
            currentTab: this.subTab ? this.subTab : ''
        }
    },
    computed: {
        subMenus(){
            return [
                { name: this.$t('Admin & Moderators'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Members'), tab: 'members', isActive: this.currentTab === 'members', badge: this.shortenNumber(this.adminConfig.member_without_admin) },
                { name: this.$t('Pending Requests'), tab: 'pending_requests', isActive: this.currentTab === 'pending_requests', badge: this.shortenNumber(this.adminConfig.member_request_count) },
                { name: this.$t('Bans'), tab: 'bans', isActive: this.currentTab === 'bans', badge: this.shortenNumber(this.adminConfig.block_count) },
            ]
        },
        manageMemberComponent() {
			switch(this.currentTab){
                case 'members':
                    return Members;
                case 'pending_requests':
                    return PendingRequests;
                case 'bans':
                    return Bans;
				default: 
					return Moderators;
			}
		}
    },
    methods: {
        changeTab(name) {
			this.currentTab = name
			let groupUrl = this.$router.resolve({
				name: 'groups_manage',
				params: { tab: 'groups_manage_members' }
			});
			changeUrl(groupUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        handleUpdateData(){
            this.$emit('updated');
        }
    },
    emits: ['updated']
}
</script>