<template>
    <div class="main-content-section">
        <h3 class="font-bold text-xl mb-base-2">{{ $t('Become a creator') }}</h3>
        <p class="mb-4">{{ $t('To become a creator, you need to complete the items below.') }}</p>
        <div v-if="paidContentConfig" class="flex flex-wrap gap-y-5 mb-4">
            <div
                v-for="(item, index) in checklistItems"
                :key="item.field"
                class="flex-1 text-center relative space-y-2 cursor-pointer"
                :class="{'font-bold': paidContentConfig[item.field]}"
                @click="handleClickStep(item.step)"
                >
                <div 
                    class="flex gap-base-2 w-10 h-10 mx-auto justify-center items-center rounded-full relative z-20"
                    :class="paidContentConfig[item.field] ? 'bg-primary-color text-white dark:bg-dark-primary-color' : (item.step === currentStep ? 'bg-secondary-color dark:bg-secondary-box-color dark:text-main-color' : 'bg-web-wash dark:bg-dark-web-wash')"
                    >
                    {{ index + 1 }}
                </div>
                <div class="min-w-[140px] px-2">{{ item.title }}</div>
                <div 
                    class="absolute top-2 w-full h-2 z-10"
                    :class="paidContentConfig[item.field] ? 'bg-primary-color dark:bg-dark-primary-color' : (item.step === currentStep ? 'bg-secondary-color dark:bg-secondary-box-color' : 'bg-web-wash dark:bg-dark-web-wash')"
                ></div>
            </div>
        </div>
        <Component :is="becomeCreatorStep" @update="handleGetConfig" />
    </div>
</template>

<script>
import { mapState } from 'pinia'   
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { getPaidContentConfig } from '@/api/paid_content'
import { changeUrl } from '@/utility/index'
import { h } from 'vue'
import Error from '@/components/utilities/Error.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import UploadAvatarCoverStep from './UploadAvatarCoverStep.vue'
import UpgradeMembershipStep from './UpgradeMembershipStep.vue'
import SetPricingStep from '@/pages/settings/creator_dashboard/SetPricing.vue'
import VerifyStep from './VerifyStep.vue'

export default {
    props: ['step'],
    components: {
        Error,
        BaseIcon
    },
    data(){
        return{
            paidContentConfig: null,
            currentStep: 1
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
        becomeCreatorStep(){
            switch(this.currentStep) {
                case 2:
                    return SetPricingStep
                case 3:
                    if(this.config.membership.enable){
                        return UpgradeMembershipStep
                    }
                    return {
                        render: () =>
                            h(Error, {}, {
                                default: () => this.config?.permissionMessages?.['paid_content.allow_create'] || ''
                            })
                    }
                case 4:
                    return VerifyStep
                default:
                    return UploadAvatarCoverStep
            }
        },
        notDoneStep() {
            if (!this.paidContentConfig) return 1;
            const notDone = this.checklistItems.find(item => !this.paidContentConfig[item.field]);
            return notDone ? notDone.step : 1;
        }
    },
    watch: {
        user(){
            this.handleGetConfig()
        }
    },
    mounted(){
        this.handleGetConfig()
    },
    methods:{
        async handleGetConfig(){
            try {
                const response = await getPaidContentConfig()
                this.paidContentConfig = response
                this.currentStep = Number(this.step) || this.notDoneStep
            } catch (error) {
                this.showError(error)
            }
        },
        handleClickStep(step){
            this.currentStep = step
            let stepUrl = this.$router.resolve({
                name: 'creator_setting_steps',
                params: { step: step }
            });
            changeUrl(stepUrl.fullPath)
        }
    }
}
</script>