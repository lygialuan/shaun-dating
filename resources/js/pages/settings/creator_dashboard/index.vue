<template>
    <template v-if="paidContentConfig">
        <template v-if="canCreatePaidContent">
            <TabsMenu :menus="creatorDashboardTabs" @select="changeTab" class="mb-4" />
            <Component :is="creatorDashboardComponent" />
        </template>
        <div v-else class="flex flex-col items-start gap-4">
            <div>{{ $t('To access your Creator Dashboard, please complete the following steps:') }}</div>
            <button
                v-for="item in checklistItems"
                :key="item.field"
                class="flex gap-2 items-center cursor-pointer"
                :class="{'text-base-green dark:text-base-green': paidContentConfig[item.field]}"
                @click="handleGoToDashboardStep(item.step)"
            >
                <BaseIcon :name="paidContentConfig[item.field] ? 'check_circle' : 'x_circle'" />
                <span>{{ item.title }}</span>
            </button>
            <BaseButton :to="{ name: 'creator_setting_steps' }">{{ $t('Start Now') }}</BaseButton>
        </div>
    </template>
</template>

<script>
import { mapState } from 'pinia'   
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { getPaidContentConfig } from '@/api/paid_content'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import SetPricing from './SetPricing.vue';
import EarningReport from './EarningReport.vue';
import Subscribers from './Subscribers.vue'

export default {
    props: ['tab'],
    components: {
        BaseIcon,
        TabsMenu,
        BaseButton
    },
    data(){
        return{
            currentTab: this.tab || '',
            paidContentConfig: null
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user']),
        checklistItems() {
            const items = [
                {
                    field: 'check_profile',
                    title: this.$t('Update your profile picture and profile cover'),
                    step: 1
                },
                {
                    field: 'check_price',
                    title: this.$t('Setup subscription pricing'),
                    step: 2
                }
            ]
            if(!this.user.is_moderator){
                items.push({
                    field: 'check_membership',
                    title: this.$t('Upgrade membership'),
                    step: 3
                });
            }
            if (this.config?.userVerifyEnable && this.config?.paid_content.require_verify) {
                items.push({
                    field: 'check_verify',
                    title: this.$t('Verify your profile'),
                    step: 4
                });
            }
            return items;
        },
        canCreatePaidContent() {
            return this.paidContentConfig 
                ? Object.values(this.paidContentConfig).every(value => value === true)
                : false;
        },
        creatorDashboardTabs(){
            return [
                { name: this.$t('Earning Report'), tab: '', isActive: this.currentTab === '' },
                { name: this.$t('Subscribers'), tab: 'subscribers', isActive: this.currentTab === 'subscribers' },
                { name: this.$t('Set Pricing'), tab: 'set_pricing', isActive: this.currentTab === 'set_pricing' },
            ]
        },
        creatorDashboardComponent() {
			switch(this.currentTab){
                case 'subscribers':
                    return Subscribers;
                case 'set_pricing':
                    return SetPricing;
				default: 
					return EarningReport;
			}
		}
    },
    mounted(){
        this.handleGetConfig()
    },
    methods:{
        changeTab(name) {
            this.$router.push({ name: 'setting_creator_dashboard', params: { tab: name } })
		},
        async handleGetConfig(){
            try {
                const response = await getPaidContentConfig()
                this.paidContentConfig = response
            } catch (error) {
                this.showError(error)
            }
        },
        handleGoToDashboardStep(step){
            if(step === 3 && !this.config.membership.enable){
                return this.showPermissionPopup('paid_content.allow_create')
            }
            this.$router.push({ name: 'creator_setting_steps', params: { step: step } });
        }
    }
}
</script>