<template>
    <form @submit.prevent="saveEmail(email)">
        <BaseInputText v-model="email" autofocus class="mb-base-2" />
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { storeEmailPage } from '@/api/page'

export default {
    inject: ['dialogRef'],
    data(){
        return{
            email: this.dialogRef.data.pageEmail
        }
    },
    components: { BaseInputText, BaseButton },
    methods: {
        async saveEmail(email){
            try {
                await storeEmailPage(email)
                this.dialogRef.close({email: this.email});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>