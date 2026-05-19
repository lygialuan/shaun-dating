<template>
    <div v-if="isMobile" class="main-content-section">
		<div v-if="!currentTab">
			<div class="settings-list">
				<router-link
                    v-for="item in visibleListsMenu"
                    :key="item.name"
                    class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
                    :to="{ name: item.name }"
                >
                    {{ item.label }}
                    <BaseIcon name="caret_right" size="16" />
                </router-link>
			</div>
		</div>
		<div v-if="currentTab">
			<router-view></router-view>
		</div>
	</div>
    <div v-else class="main-content-section p-0">
        <div class="flex">
            <div class="main-content-section-left flex-1 py-4 md:border-e border-divider dark:border-white/10 rounded-s-base-lg">
                <div class="settings-list">
                    <router-link
                        v-for="item in visibleListsMenu"
                        :key="item.name"
                        class="main-content-menu-item settings-list-item flex justify-between items-center px-5 py-base-2 text-main-color dark:text-white"
                        :to="{ name: item.name }"
                        :class="{'router-link-exact-active': isActive(item.name)}"
                    >
                        {{ item.label }}
                    </router-link>
                </div>
            </div>
            <div class="main-content-section-right flex-2 min-w-0 p-4">
                <router-view></router-view>
            </div>
        </div>
	</div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components:{
        BaseIcon
    },
    data(){
        return{
            currentTab: this.$route.path.split("/")[2],
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config', 'isMobile']),
        listMenu(){
            return [
                { label: this.$t('Lists'), name: this.isMobile ? 'list_index' : 'list_lists' },
                { label: this.$t('Stories'), name: 'list_stories' },
                { label: this.$t('Profiles'), name: 'list_page', isShow: this.config.user_page.enable && !this.user.is_page },
            ]
        },
        visibleListsMenu() {
            return this.listMenu.filter(item => item.isShow || typeof(item.isShow) == 'undefined');
        },
        listTitle(){
			const titles = {
                follower: this.$t('Followers'),
                following: this.$t('Following'),
                block_member: this.$t('Blocked'),
                stories: this.$t('Stories'),
                pages: this.$t('Profiles'),
                vibbs: this.$t('Vibbs'),
				groups: this.$t('Groups')
            };
            return titles[this.currentTab] || this.$t('Lists');
		}
    },
    methods:{
        isActive(routeName){
            return ['list_following', 'list_follower', 'list_block_member'].includes(this.$router.currentRoute.value.name) && routeName === 'list_lists'
        }
    }
}
</script>