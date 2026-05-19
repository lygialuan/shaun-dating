<template>
    <div class="space-y-base-2">
        <RuleItem v-for="rule in rulesList" :key="rule.id" :item="rule" />
        <BaseButton @click="handleAcceptRules" fluid>{{ $t('Accept & Join') }}</BaseButton>
    </div>
</template>
<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import RuleItem from '@/components/lists/RuleItem.vue'

export default {
    components: { BaseButton, RuleItem },
    inject: ['dialogRef'],
    data() {
		return {
			rulesList: this.dialogRef.data.rulesList
        }
	},
    methods: {
        handleAcceptRules(){
            this.dialogRef.close({accepted: true})
        }
    }
    
}
</script>