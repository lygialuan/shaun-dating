<template>
    <div v-if="subscription" class="flex flex-col gap-1 bg-light-blue p-3 rounded-base dark:bg-[#434343]">
		<div class="flex gap-4">
			<div class="flex-1 flex gap-2">
				<BaseIcon v-if="hasBackListener" name="caret_left" class="cursor-pointer" @click="handleBack" />
				<p class="font-semibold">{{ $t('Package') }} : {{ subscription.name }}</p>
			</div>
			<span class="text-white text-base-xs px-2 py-1 rounded-base self-start" :class="subscription.status == 'cancel' ? 'bg-base-red' : 'bg-base-green'">{{ subscription.status_text }}</span>
		</div>
		<p>{{ $t('Created date') + ': ' + subscription.created_at }}</p>
		<template v-if="subscription.type == 'user_subscriber' && subscription.subject">
			<div>{{ $t('Creator') + ': '}}<span class="inline-block"><UserName :activePopover="false" :user="subscription.subject"/></span> </div>
		</template>
		<template v-if="subscription.status === 'active'">
			<p>{{ subscription.trial }}</p>
			<p>{{ $t('Subscription renewal date') + ': ' + subscription.next_payment }}</p>
			<p>{{ $t('Price') + ': ' + subscription.price }}</p>
			<p>{{ $t('Payment method') + ': ' + subscription.gateway }}</p>
		</template>
		<template v-if="subscription.status === 'cancel'">
			<p>{{ $t('Subscription will stop at') + ': ' + subscription.next_payment }}</p>
		</template>
		<div v-if="['active', 'cancel'].includes(subscription.status)">
			<BaseButton v-if="subscription.can_cancel" @click="doCancelSubscription(subscription.id)" size="sm">{{ $t('Cancel subscription') }}</BaseButton>
			<BaseButton v-if="subscription.can_resume" @click="doResumeSubscription(subscription.id)" size="sm">{{ $t('Resume subscription') }}</BaseButton>
		</div>
	</div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import UserName from '@/components/user/UserName.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    props: ['subscription'],
    components: { BaseButton, UserName, BaseIcon },
	computed: {
		hasBackListener() {
			return !!(this.$.vnode && this.$.vnode.props && this.$.vnode.props.onBack);
		}
	},
    methods:{
        async doCancelSubscription(id){
			this.$confirm.require({
                message: this.$t('Your subscription will be canceled at the end of your billing period on') + ' ' + this.subscription.next_payment + '. ' + this.$t('You can change your mind anytime before this date.'),
                header: this.$t('Cancel Subscription?'),
                acceptLabel: this.$t('Cancel Subscription'),
                rejectLabel: this.$t('Keep Subscription'),
                accept: () => {
                    this.$emit('cancel', id)
                }
            })
		},
		async doResumeSubscription(id){
			this.$confirm.require({
                message: this.$t('Do you want to resume this subscription?'),
                header: this.$t('Please confirm'),
                acceptLabel: this.$t('Ok'),
                rejectLabel: this.$t('Cancel'),
                accept: () => {
					this.$emit('resume', id)
                }
            })
		},
		handleBack(){
			this.$emit('back')
		}
    },
	emits: ['cancel', 'resume', 'back']
}
</script>