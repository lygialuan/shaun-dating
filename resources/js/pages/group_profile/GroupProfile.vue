<template>
    <div v-if="groupInfo">
        <div class="header-profile">
            <GroupHeader />
            <div class="header-profile-menus px-4 dark:bg-dark-form-base dark:border-dark-form-base">
                <TabsMenu v-if="groupInfo.canView" :menus="groupMenus" @select="changeTab" class-item="flex-1" class="border-t border-divider dark:border-white/10" />
            </div>
        </div>
        <Component :is="groupComponent" :group-info="groupInfo" />
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useGroupStore } from '@/store/group'
import { useAppStore } from '@/store/app'
import { changeUrl } from '@/utility'
import GroupHeader from './GroupHeader.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue'
import GroupFeeds from '@/pages/group_profile/GroupFeeds.vue'
import GroupInfo from '@/pages/group_profile/GroupInfo.vue'
import GroupMedia from '@/pages/group_profile/GroupMedia.vue'
import GroupMembers from '@/pages/group_profile/GroupMembers.vue'

export default {
    props: ['data', 'params', 'position'],
    components: { 
        GroupHeader,
        TabsMenu 
    },
    data() {
		return {
			currentTab: this.params.tab ? this.params.tab : '',
            currentSlug: this.params.slug
		}
	},
    computed:{
        ...mapState(useGroupStore, ['groupInfo']),
        ...mapState(useAppStore, ['currentRouter']),
        groupMenus(){
			return [
				{ icon: 'feeds', name: this.$t('Feeds'), isShow: true, isActive: this.currentTab === '', tab: ''},
				{ icon: 'info', name: this.$t('Info'), isShow: true, isActive: this.currentTab === 'info', tab: 'info'},
				{ icon: 'media', name: this.$t('Media'), isShow: true, isActive: this.currentTab === 'media', tab: 'media'},
				{ icon: 'users', name: this.$t('Members'), isShow: true, isActive: this.currentTab === 'members', tab: 'members'}
			]
		},
        groupComponent() {
			switch(this.currentTab){
                case 'info':
                    return GroupInfo;
                case 'media':
                    return GroupMedia;
                case 'members':
                    return GroupMembers;
				default: 
					return this.groupInfo.canView ? GroupFeeds : GroupInfo;
			}
		}
    },
    watch: {
        groupInfo(newVal){
            if(newVal && newVal.slug !== this.currentSlug) {
                return this.$router.push({ 'name': 'permission' })
            }
        },
        currentRouter(newVal){
            this.currentTab = newVal.params.tab || ''
        }
    },
    mounted(){
        this.handleGetGroupDetail(this.params.id)
        this.handleGetGroupRules(this.params.id)
    },
    unmounted() {
		this.setGroupInfo()
        this.setGroupRulesList()
	},
    methods:{
        ...mapActions(useGroupStore, ['handleGetGroupDetail', 'setGroupInfo', 'handleGetGroupRules', 'setGroupRulesList']),
        changeTab(name) {
			this.currentTab = name
			let groupUrl = this.$router.resolve({
				name: 'group_profile',
				params: { id: this.params.id, slug: this.params.slug }
			});
			changeUrl(groupUrl.fullPath + (name != '' ? '/' + name : ''))
		}
    }
}
</script>