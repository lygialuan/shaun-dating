<template>
    <div class="main-content-section-header px-base-2 md:px-0">
        <h3 class="main-content-section-header-title">{{ $t('Groups') }}</h3>
        <BaseButton v-if="authenticated" @click="handleCreateGroup">{{ $t('Create new group') }}</BaseButton>
    </div>
    <TabsMenu :menus="groupMenus" @select="changeTab" class="mb-base-2"/>
    <div class="main-content-section-body">
        <Component :is="groupComponent" :categoryId="$route.query.category_id" />
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { changeUrl } from '@/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import YourFeeds from '@/pages/groups/YourFeeds.vue'
import GroupAll from '@/pages/groups/GroupAll.vue'
import GroupSuggest from '@/pages/groups/GroupSuggest.vue'
import GroupJoined from '@/pages/groups/GroupJoined.vue'
import GroupExplore from '@/pages/groups/GroupExplore.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue';

export default {
    props: ['data', 'params', 'position'],
    components: { BaseButton, TabsMenu },
    data(){
        return{
            currentTab: this.params.tab ? this.params.tab : ''
        }
    },
    computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
        groupMenus(){
            return [
                { name: this.$t('Explore'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Your Feeds'), tab: 'your_feeds', isActive: this.currentTab === 'your_feeds', isShow: this.authenticated },
                { name: this.$t('All Groups'), tab: 'all', isActive: this.currentTab === 'all' },
                { name: this.$t('For you'), tab: 'suggest', isActive: this.currentTab === 'suggest', isShow: this.authenticated },
                { name: this.$t('Joined'), tab: 'joined', isActive: this.currentTab === 'joined', isShow: this.authenticated }
            ]
        },
        groupComponent() {
			switch(this.currentTab){
				case 'your_feeds':
					return YourFeeds;
				case 'all':
					return GroupAll
				case 'suggest':
					return GroupSuggest
                case 'joined':
					return GroupJoined
				default: 
					return GroupExplore;
			}
		}
    },
    methods:{
        changeTab(name) {
			this.currentTab = name
			let pageUrl = this.$router.resolve({
				name: 'groups'
			});
			changeUrl(pageUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        handleCreateGroup() {
            if (this.user) {
				let permission = 'group.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'group_create' })
                }
			}
        }
    }
}
</script>
