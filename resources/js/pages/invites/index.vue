<template>
    <div class="main-content-section">
        <TabsMenu :menus="inviteMenus" @select="changeTab" class="justify-center border-b border-divider dark:border-white/10" />
        <Component :is="inviteComponent"/>
    </div>
</template>
<script>
import { changeUrl } from '@/utility';
import TabsMenu from '@/components/menu/TabsMenu.vue'
import Invite from './Invite.vue'
import YourReferrals from './YourReferrals.vue'

export default {
    props: ['tab'],
    components: {
        TabsMenu
    },
    data() {
        return {
            currentTab: this.tab ? this.tab : ''
        }
    },
    computed: {
        inviteMenus(){
			return [
				{ name: this.$t('Invite'), isActive: this.currentTab === '', tab: ''},
                { name: this.$t('Your referrals'), isActive: this.currentTab === 'your_referrals', tab: 'your_referrals'},
			]
		},
        inviteComponent() {
            switch (this.currentTab) {
                case "invite":
                    return Invite;
                case "your_referrals":
                    return YourReferrals;
                default:
                    return Invite;
            }
        }
    },
    methods: {
        changeTab(name) {
            this.currentTab = name
            let userUrl = this.$router.resolve({
                name: 'invites',
                params: { 'tab': this.currentTab }
            });
            changeUrl(userUrl.fullPath)
        }
    }
}
</script>