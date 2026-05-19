<template>
    <TabsMenu :menus="pageReportMenus" @select="navigateTo" type="secondary" class="mb-base-2" />
    <router-view></router-view>
</template>

<script>
import TabsMenu from '@/components/menu/TabsMenu.vue'

export default {
    components: { TabsMenu },
    data() {
        return {
            currentRouteName: this.$router.currentRoute.value.name
        }
    },
    computed:{
        pageReportMenus(){
			return [
				{ icon: 'info', name: this.$t('Page Overview'), isShow: true, isActive: this.currentRouteName === 'user_pages_overview', tab: 'user_pages_overview'},
				{ icon: 'users', name: this.$t('Manage Users'), isShow: true, isActive: this.currentRouteName === 'user_pages_manage_users', tab: 'user_pages_manage_users'},
				{ icon: 'bell', name: this.$t('Notify Settings'), isShow: true, isActive: this.currentRouteName === 'user_pages_notify_settings', tab: 'user_pages_notify_settings'}
			]
		}
    },
    methods: {
        navigateTo(routeName) {
            this.$router.push({ name: routeName });
        }
    }
}
</script>