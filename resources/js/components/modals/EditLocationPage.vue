<template>
    <form @submit.prevent="saveAddress(location)" class="space-y-base-2">
        <BaseSelectLocation v-model="location" />
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseSelectLocation from '@/components/inputs/BaseSelectLocation.vue'
import { storeAddressPage } from '@/api/page'

export default {
    components: { BaseButton, BaseSelectLocation },
    inject: ['dialogRef'],
    data(){
        return{
            location: this.dialogRef.data.pageLocation
        }
    },
    methods: {
        async saveAddress(location){
            try {
                await storeAddressPage({
                    country_id: location.country_id,
                    state_id: location.state_id,
                    city_id: location.city_id,
                    zip_code: location.zip_code,
                    address: location.address
                })
                this.dialogRef.close({location: this.location});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>