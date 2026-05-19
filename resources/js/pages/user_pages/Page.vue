<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Profile') }}</h3>
            <BaseButton v-if="! user.is_page" @click="createPage">{{ $t('Create new page') }}</BaseButton>
        </div>
        <TabsMenu :menus="pageMenus" @select="changeTab" class="mb-4"/>
        <div class="main-content-section-body">
            <Component :is="pageComponent" :categoryId="$route.query.category_id"/>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { changeUrl } from '@/utility'
import BaseButton from '@/components/inputs/BaseButton.vue'
import UserPagesAll from '@/pages/user_pages/UserPagesAll.vue'
import UserPagesTrending from '@/pages/user_pages/UserPagesTrending.vue'
import UserPagesSuggest from '@/pages/user_pages/UserPagesSuggest.vue'
import UserPagesFollowing from '@/pages/user_pages/UserPagesFollowing.vue'
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
		...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config']),
        pageMenus(){
            return [
                { name: this.$t('All'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Trending'), tab: 'trending', isActive: this.currentTab === 'trending' },
                { name: this.$t('For you'), tab: 'suggest', isActive: this.currentTab === 'suggest' },
                { name: this.$t('Following'), tab: 'following', isActive: this.currentTab === 'following' }
            ]
        },
        pageComponent() {
			switch(this.currentTab){
				case 'trending':
					return UserPagesTrending;
				case 'suggest':
					return UserPagesSuggest
				case 'following':
					return UserPagesFollowing
				default: 
					return UserPagesAll;
			}
		}
    },
    mounted(){
        if(!this.config.user_page.enable){
            this.setErrorLayout(true)
        }
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        changeTab(name) {
			this.currentTab = name
			let pageUrl = this.$router.resolve({
				name: 'user_pages'
			});
			changeUrl(pageUrl.fullPath + (name != '' ? '/' + name : ''))
		},
        createPage() {
            if (this.user) {
				let permission = 'user_page.allow_create'
                if(this.checkPermission(permission)){
                    this.$router.push({ 'name': 'user_pages_create' })
                }
			}
        }
    }
}
</script>