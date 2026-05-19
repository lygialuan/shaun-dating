<template>
    <form @submit.prevent="savePhoneNumber(phoneNumber)" class="space-y-base-2">
        <BaseInputText v-model="phoneNumber" autofocus />
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { storePhoneNumberPage } from '@/api/page'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            phoneNumber: this.dialogRef.data.pagePhoneNumber
        }
    },
    components: { BaseInputText, BaseButton },
    methods: {
        async savePhoneNumber(phoneNumber){
            try {
                await storePhoneNumberPage(phoneNumber)
                this.dialogRef.close({phoneNumber: this.phoneNumber});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>