<template>
    <CurrentPlan v-if="currentPackage" :subscription="currentPackage.subscription" @cancel="handleCancelSubscription" @resume="handleResumeSubscription" class="mb-5" />
    <Loading v-if="!subscriptionConfig"/>
    <template v-else>
        <div v-if="subscriptionConfig.packages.length">
            <div class="grid grid-cols-[repeat(auto-fill,minmax(280px,1fr))] gap-5 mb-5">
                <PackageBox v-for="subscriptionPackage in subscriptionConfig.packages" :key="subscriptionPackage.id" :data="subscriptionPackage" :current-plan="currentPackage" :badgeStyle="{backgroundColor: subscriptionConfig.highlight_background_color, color: subscriptionConfig.highlight_text_color, text: subscriptionConfig.highlight_text}" @select="handleSelectPackage" @update="getCurrentPackpage" />
            </div>
            <div v-if="subscriptionConfig.compares.length" class="relative overflow-x-auto">
                <table class="w-full whitespace-nowrap text-center">
                    <thead class="s-table-header bg-table-header-color dark:bg-dark-web-wash">
                        <tr>
                            <th scope="col" class="p-3 text-start rounded-l-base sticky left-0 bg-inherit max-w-xs whitespace-pre-wrap min-w-40">{{$t('Features')}}</th>
                            <th v-for="subscriptionPackage in subscriptionConfig.packages" :key="subscriptionPackage.id" scope="col" class="p-3 w-44 text-center last:rounded-r-base whitespace-pre-wrap">{{ subscriptionPackage.name }}</th>
                        </tr>
                    </thead>
                    <tbody class="s-table-body">
                        <tr v-for="(subscriptionCompare, index) in subscriptionConfig.compares" :key="index" class="odd:bg-white odd:dark:bg-gray-800 even:bg-light-blue even:dark:bg-gray-900">
                            <td class="p-3 text-start font-semibold rounded-l-base sticky left-0 bg-inherit max-w-xs whitespace-pre-wrap min-w-40">
                                {{ subscriptionCompare.name }}
                            </td>
                            <td v-for="(packageCompare, index) in subscriptionCompare.packages" :key="index" class="p-3 text-center last:rounded-r-base w-44 whitespace-pre-wrap">
                                <div v-if="packageCompare.type == 'text'">
                                    {{ packageCompare.value }}
                                </div>
                                <template v-else>
                                    <div v-if="packageCompare.value == 1" class="text-base-green">
                                        <BaseIcon name="check_circle"/>
                                    </div>
                                    <div v-else class="text-base-red">
                                        <BaseIcon name="x_circle"/>
                                    </div>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div v-else class="text-center">{{ $t('No packages available') }}</div>
    </template>
</template>

<script>
import { mapActions } from 'pinia'
import { getSubscriptionConfig, getCurrentSubscription, storeSubscription, storeTrialSubscription } from '@/api/membership'
import { cancelSubscription, resumeSubscription } from '@/api/subscription'
import { useAuthStore } from '@/store/auth'
import { useUtilitiesStore } from '@/store/utilities'
import Loading from '@/components/utilities/Loading.vue'
import PackageBox from '@/components/membership/PackageBox.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'
import CurrentPlan from '@/components/membership/CurrentPlan.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { Loading, PackageBox, CurrentPlan, BaseIcon },
    data() {
        return {
            currentPackage: null,
            subscriptionConfig: null,
            planSelected: []
        }
    },
    mounted(){
        this.getConfig()
        this.getCurrentPackpage()
    },
    methods:{
        ...mapActions(useAuthStore, ['updateCurrentPlan']),
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
        async handleSelectPackage(selectedInfo){
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
                                this.showSuccess(this.$t('Select package successfully.'))
                                passwordDialog.close()
                                this.$emit('update')
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                            }
                        }
                    }
                }
			})
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
        }
    },
    emits: ['update']
}
</script>