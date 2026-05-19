<template>
    <form @submit.prevent="saveReviewStatus(currentStatus)" class="space-y-base-2">
        <div class="flex gap-base-2">
            <div>
                <div class="font-bold">{{ $t('Allow others to view and leave reviews on your Page?') }}</div>
                <div>{{ $t('Reviews are public, influence your rating and cannot be deleted. You can hide your rating and existing reviews from your Page and keep people from leaving new reviews at any time.') }}</div>
            </div>
            <BaseSwitch class="flex-shrink-0" v-model="currentStatus" />
        </div>
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseSwitch from '@/components/inputs/BaseSwitch.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { storeEnableReviewPage } from '@/api/page'

export default {
    components: { BaseSwitch, BaseButton },
    inject: ['dialogRef'],
    data(){
        return{
            currentStatus: this.dialogRef.data.currentStatus ? true : false
        }
    },
    methods: {
        async saveReviewStatus(status){
            try {
                await storeEnableReviewPage(status)
                this.dialogRef.close({enable: status});
				this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>