<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Compare plans') }}</h3>
            <div class="flex" v-if="popupModal">
                <button class="ml-auto text-left">
                    <BaseIcon name="close" @click="closeModal"/>
                </button>
            </div>
        </div>
        <p class="mb-5 dark:text-dark-text-base-gray">{{ $t("Please choose the appropriate package to subscribe. If you have an active subscription and decide to switch to another package to subscribe. The system will not refund and unused days will not be added to the new package. It's best to cancel your current package and wait for the cycle to end to switch to a new package.") }}</p>
        <CurrentPlan v-if="currentPackage" :subscription="currentPackage.subscription" @cancel="handleCancelSubscription" @resume="handleResumeSubscription" class="mb-5" />
        <Loading v-if="!subscriptionConfig"/>
        <template v-else>
            <div v-if="subscriptionConfig.packages.length">
                <div class="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-5 mb-5">
                    <PackageBox v-for="subscriptionPackage in subscriptionConfig.packages" :key="subscriptionPackage.id" :data="subscriptionPackage" :current-plan="currentPackage" :badgeStyle="{backgroundColor: subscriptionConfig.highlight_background_color, color: subscriptionConfig.highlight_text_color, text: subscriptionConfig.highlight_text}" @select="handleSelectPackage" @update="getCurrentPackpage" />
                </div>
                    <a
                        class="text-blue-500 hover:text-blue-600 hover:underline cursor-pointer"
                        @click="showComparison"
                    >
                        {{$t('View comparison table')}}
                    </a>
                </div>
            <div v-else class="text-center">{{ $t('No packages available') }}</div>
        </template>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { getSubscriptionConfig, getCurrentSubscription, storeSubscription, storeTrialSubscription } from '@/api/membership'
import { cancelSubscription, resumeSubscription } from '@/api/subscription'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { useUtilitiesStore } from '@/store/utilities'
import { createLinkPayment } from '@/api/gateway_recurring'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import Loading from '@/components/utilities/Loading.vue'
import PackageBox from '@/components/membership/PackageBox.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import CurrentPlan from '@/components/membership/CurrentPlan.vue'
import ComparisonTableModal from '@/components/modals/ComparisonTableModal.vue';
import GetwaysModal from '@/components/modals/GetwaysModal.vue';

export default {
    components: { BaseIcon, Loading, PackageBox, CurrentPlan },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return {
            currentPackage: null,
            subscriptionConfig: null,
            planSelected: [],
            popupModal: this.dialogRef?.data?.popupModal ?? false,
        }
    },
    mounted(){
        if(!this.config.membership.enable || this.user.is_moderator){
            return this.$router.push({ 'name': 'permission' })
        } 
        this.getConfig()
        this.getCurrentPackpage()
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        ...mapActions(useAuthStore, ['updateCurrentPlan', 'me']),
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        async getConfig(){
            try {
                const response = await getSubscriptionConfig()
                this.subscriptionConfig = response
            } catch (error) {
                this.showError(error)
            }
        },
        async getCurrentPackpage(){
            try {
                const response = await getCurrentSubscription()
                this.currentPackage = response
            } catch (error) {
                this.showError(error)
            }
        },
        async handleSelectPackage(selectedInfo, type = 0){
            const gateways = selectedInfo.plan.gateways || []
                
            if(gateways.length===0) return this.showError(this.$t('Payment method is not available. Please contact admin for detail'))

            if (!selectedInfo.plan.trial_day && gateways.length > 0 && !type) {
                if (gateways.length === 1) {
                    if(gateways[0].type === 'wallet'){
                        this.handleSelectPackage(selectedInfo, gateways[0].type)
                    }else{
                        const response = await createLinkPayment({ plan_id: selectedInfo.plan.id, gateway_recurring_id: gateways[0].id, flex_form_id: gateways[0].flex_form_id })
                        window.location.href = response.url
                    }
                } else {
                    this.showGateways(selectedInfo)
                }
            }else{
                const passwordDialog = this.$dialog.open(PasswordModal, {
                    props: {
                        header: this.$t('Enter Password'),
                        class: 'password-modal',
                        modal: true,
                        dismissableMask: true,
                        draggable: false
                    },
                    emits: {
                        onConfirm: async (data) => {
                            if (data.password) {
                                try {
                                    if(selectedInfo.plan.trial_day){
                                        await storeTrialSubscription({
                                            plan_id: selectedInfo.plan.id,
                                            password: data.password
                                        })                             
                                    } else {
                                        await storeSubscription({
                                            plan_id: selectedInfo.plan.id,
                                            password: data.password
                                        })
                                    }
                                    this.getCurrentPackpage()
                                    this.getConfig()
                                    this.updateCurrentPlan(selectedInfo.package.name)
                                    this.pingNotification()
                                    this.me()
                                    this.showSuccess(this.$t('Select package successfully.'))
                                    passwordDialog.close()
                                    this.dialogRef.close(true)
                                } catch (error) {
                                    this.showError(error.error)
                                    passwordDialog.close()
                                }
                            }
                        }
                    }
                })
            }
        },
        async handleCancelSubscription(subscriptionId){
            try {
                await cancelSubscription(subscriptionId)
                this.getCurrentPackpage()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleResumeSubscription(subscriptionId){
            try {
                await resumeSubscription(subscriptionId)
                this.getCurrentPackpage()
            } catch (error) {
                this.showError(error.error)
            }
        },
        closeModal(){
            this.dialogRef.close()
        },
        showComparison: function (){
            this.$dialog.open(ComparisonTableModal, {
                props: {
                    showHeader: false,
                    modal: true,
                    draggable: false,
                    class: 'p-dialog-lg'
                },
                onClose: (options) => {
                    if (options?.data== true) {
                        this.clickClose()
                    }
                }
            });
        },
        showGateways(selectedInfo) {
            this.$dialog.open(GetwaysModal, {
                props: {
                    header: this.$t('Your order'),
                    modal: true,
                    draggable: false,
                    class: 'p-dialog-md'
                },
                data: {
                    plan: selectedInfo.plan
                },
                onClose: async (options) => {
                    if (!options?.data) return
                    const selected = options.data.selected
                    if (selected.type === 'wallet') {
                        this.handleSelectPackage(selectedInfo, selected)
                    } else {
                        try {
                            const response = await createLinkPayment({
                                plan_id: selectedInfo.plan.id,
                                gateway_recurring_id: selected.id,
                                flex_form_id: selected.flex_form_id
                            })
                            window.location.href = response.url
                        } catch (e) {
                            console.error(e)
                        }
                    }
                }
            });
        }
    }
}
</script>