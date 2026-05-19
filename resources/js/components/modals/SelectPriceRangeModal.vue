<template>
    <form @submit.prevent="handleSelectPriceRange(selectedRangePrice.value)" class="space-y-base-2">
        <div v-if="rangePricesList.length" class="space-y-base-2">
            <div v-for="rangePrice in rangePricesList" :key="rangePrice.value" class="flex items-center gap-base-2">
                <BaseRadio :value="rangePrice" v-model="selectedRangePrice" :inputId="rangePrice.value.toString()" name="price_range" />
                <label :for="rangePrice.value">
                    <div>
                        <div class="font-bold">{{ rangePrice.title }}</div>
                        <div class="text-base-xs">{{ rangePrice.description }}</div>
                    </div>
                </label>
            </div>
        </div>
        <BaseButton fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import { getPriceRangePage, storePricePage } from '@/api/page'

export default {
    components: { BaseRadio, BaseButton },
    inject: ['dialogRef'],
    data(){
        return{
            rangePricesList: [],
            selectedRangePrice: this.dialogRef.data.selectedRangePrice
        }
    },
    mounted(){
        this.fetchPriceRangePage()
    },
    methods: {
        async fetchPriceRangePage(){
            try {
                const response = await getPriceRangePage()
                this.rangePricesList = response
            } catch (error) {
                console.log(error)
            }
        },
        async handleSelectPriceRange(price){
            try {
                await storePricePage(price)
                this.dialogRef.close({price: this.selectedRangePrice});
                this.showSuccess(this.$t('Your changes have been saved.'))
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>