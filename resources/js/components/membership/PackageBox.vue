<template>
    <div class="flex flex-col gap-base-2 border rounded-base-lg pt-5 px-5 pb-16 relative" :class="isCurrentPlan ? 'bg-light-yellow border-dark-yellow dark:bg-dark-form-base dark:border-white/10' : 'bg-transparent border-primary-color dark:border-dark-primary-color'">
        <div class="flex flex-wrap items-center gap-base-1 leading-none">
            <h5 class="flex-1 text-base-lg font-extrabold leading-tight">{{ data.name }}</h5>
            <div v-if="data.is_highlight" :style="{backgroundColor: badgeStyle.backgroundColor, color: badgeStyle.color}" class="rounded p-1 text-base-xs self-start">
                {{ badgeStyle.text }}
            </div>
        </div>
        <div class="font-medium">
            <p v-if="selectedPlan.trial_day" class="text-lg">{{ $filters.numberShortener(selectedPlan.trial_day, $t('[number] day trial then'), $t('[number] days trial then')) }}</p>
            <p class="text-xl">{{ selectedPlan.description }}</p>
        </div>
        <BaseSelect v-model="selectedPlanId" :options="data.plans" optionLabel="name" optionValue="id"  />
        <div class="whitespace-pre-wrap">{{ data.description }}</div>
        <div class="flex gap-base-2 absolute left-5 bottom-4 right-5">
            <template v-if="isCurrentPlan">
                <BaseButton v-if="currentPlan.subscription.can_resume" @click="handleResumePlan(currentPlan)" fluid>{{ $t('Resume') }}</BaseButton>
                <BaseButton v-if="currentPlan.subscription.can_cancel" @click="handleCancelPlan(currentPlan)" fluid>{{ $t('Cancel') }}</BaseButton>
            </template>
            <BaseButton v-else @click="handleSelectPlan(data, selectedPlan)" fluid>
                {{ selectedPlan.trial_day ? $t('Start Trial') : $t('Select') }}
            </BaseButton>
        </div>
    </div>
</template>

<script>
import { cancelSubscription, resumeSubscription } from '@/api/subscription'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'

export default {
    props: ['data', 'badgeStyle', 'currentPlan'],
    components: { BaseButton, BaseSelect },
    data(){
        return{
            selectedPlanId: this.data.plans[0].id
        }
    },
    computed:{
        selectedPlan(){
            return this.data.plans.find(plan => plan.id === this.selectedPlanId)
        },
        isCurrentPlan(){
            return this.currentPlan?.plan_id === this.selectedPlanId
        }
    },
    methods:{
        handleSelectPlan(selectedPackage, selectedPlan){
            this.$emit('select', { package: selectedPackage, plan: selectedPlan });
        },
        async handleResumePlan(plan){
			this.$confirm.require({
                message: this.$t('Do you want to resume this subscription?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: async () => {
                    try {
						await resumeSubscription(plan.subscription.id)
						this.$emit('update', plan.subscription.id)
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		},
        async handleCancelPlan(plan){
			this.$confirm.require({
                message: this.$t('Your subscription will be canceled at the end of your billing period on') + ' ' + plan.subscription.next_payment + '. ' + this.$t('You can change your mind anytime before this date.'),
                header: this.$t('Cancel Subscription?'),
                acceptLabel: this.$t('Cancel Subscription'),
                rejectLabel: this.$t('Keep Subscription'),
                accept: async () => {
                    try {
						await cancelSubscription(plan.subscription.id)
                        this.$emit('update', plan.subscription.id)
					} catch (error) {
						this.showError(error.error)
					}
                }
            })
		}
    },
    emits: ['select', 'update']
}
</script>