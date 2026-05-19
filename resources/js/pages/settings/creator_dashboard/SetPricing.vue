<template>
    <div>
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('How much do creators earn?') }}</h3>
        <div class="mb-4">
            <p>{{ $filters.textTranslate(this.$t('Creators receive [fee]% of the revenue on their earnings.'), { fee: 100 - this.config.paid_content.commission_fee}) }}</p>
            <p>{{ $filters.textTranslate(this.$t('The remaining [remaining_fee]% covers referral payments, payment processing, hosting, support, and all other services.'), { remaining_fee: this.config.paid_content.commission_fee}) }}</p>
            <p>{{ $t('Please set the fee below, leave blank if you want to disable trial, daily,...') }}</p>
        </div>
        <div class="grid grid-cols-2 gap-5">
            <div class="col-span-2">
                <label class="block mb-1">{{ $t('Trial Period (in days)') }}</label>
                <BaseInputNumber v-model="trialDays" />
            </div>
            <template v-if="packages">
                <div v-for="(packageType, key) in packageTypes" :key="key" class="col-span-2 md:col-span-1">
                    <label class="block mb-1">{{ packageType.label }}</label>
                    <BaseSelect
                        v-model="selectedPackages[key]"
                        :options="packages[key]"
                        :disabled="packages[key].length === 0"
                        optionLabel="description"
                        optionValue="id"
                        class="mb-1"
                    />
                    <div v-if="packages[key].length" class="flex items-center gap-2">
                        <BaseRadio
                            v-model="defaultPackage"
                            :inputId="key"
                            name="packages"
                            :value="key"
                        />
                        <label :for="key">{{ $t('Default selected') }}</label>
                    </div>
                </div>
            </template>
            <BaseButton :loading="loadingSave" @click="handleSavePackage" class="col-span-2">{{ $t('Save Changes') }}</BaseButton>
            <div class="col-span-2">{{ $t('Changes only affect new subscribers. Old subscribers will not be affected.') }}</div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import { getPaidContentPackages, storeUserPackage } from '@/api/paid_content'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'

export default {
    components: { 
        BaseInputNumber,
        BaseSelect,
        BaseButton,
        BaseRadio
    },
    data(){
        return {
            trialDays: null,
            packages: null,
            selectedPackages: {
                monthly: null,
                quarterly: null,
                biannual: null,
                annual: null,
            },
            defaultPackage: null,
            loadingSave: false
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        packageTypes() {
            return {
                monthly: { label: this.$t('Monthly Price') },
                quarterly: { label: this.$t('Quarterly Price') },
                biannual: { label: this.$t('Biannual Price') },
                annual: { label: this.$t('Annual Price') },
            };
        },
    },
    mounted(){
        this.handleGetPackages()
    },
    methods:{
        async handleGetPackages(){
            try {
                const response = await getPaidContentPackages()
                this.packages = response.all
                this.selectedPackages = {
                    monthly: response.current.monthly,
                    quarterly: response.current.quarterly,
                    biannual: response.current.biannual,
                    annual: response.current.annual,
                }
                this.defaultPackage = response.default
                this.trialDays = response.paid_content_trial_day || null
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleSavePackage(){
            this.loadingSave = true
            try {
                await storeUserPackage({
                    paid_content_trial_day: this.trialDays,
                    monthly_id: this.selectedPackages.monthly,
                    quarterly_id: this.selectedPackages.quarterly,
                    biannual_id: this.selectedPackages.biannual,
                    annual_id: this.selectedPackages.annual,
                    default: this.defaultPackage,
                });
                this.showSuccess(this.$t('Packages saved successfully.'));
                this.$emit('update')
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingSave = false
            }
        }
    },
    emits: ['update']
}
</script>