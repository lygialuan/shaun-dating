<template>
    <div>
        <slot v-if="countriesList.length > 1 && showLabel"><label class="block mb-1">{{ $t('Location') }} </label></slot>
        <BaseSelect v-if="countriesList.length > 1" class="mb-base-2" v-model="selectedCountryId" :error="error?.country_id" :options="countriesList" optionLabel="name" optionValue="id" :placeholder="$t('Country')" :disabled="disabled" />
        <BaseSelect v-if="statesList.length > 1" class="mb-base-2" v-model="selectedStateId" :error="error?.state_id" :options="statesList" optionLabel="name" optionValue="id" :placeholder="$t('State')" :disabled="disabled" />
        <BaseSelect v-if="citiesList.length > 1" class="mb-base-2" v-model="selectedCityId" :error="error?.city_id" :options="citiesList" optionLabel="name" optionValue="id" :placeholder="$t('City')" :disabled="disabled" />
        <BaseInputText v-if="selectedCountryId" class="mb-base-2" v-model="zipCode" :error="error?.zip_code" :placeholder="$t('Zip Code')" :disabled="disabled" />
        <BaseInputText v-if="showAddress && selectedCountryId" v-model="address" :error="error?.address" :placeholder="$t('Address')" :disabled="disabled" />
    </div>
</template>

<script>
import { getCountriesList, getStatesListByCountry, getCitesListByState } from '@/api/country';
import BaseSelect from '@/components/inputs/BaseSelect.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'

export default {
    components:{ BaseSelect, BaseInputText },
    props: {
        modelValue: {
			type: Object,
			default: null
		},
        showAddress: {
            type: Boolean,
            default: true
        },
        showLabel: {
            type: Boolean,
            default: true
        },
        error: {
			type: Object,
			default: null
		},
        disabled: {
            type: Boolean,
            default: false
        }
    },
    data(){
        return {
            countriesList: [],
            statesList: [],
            citiesList: [],
            selectedCountryId: this.modelValue?.country_id,
            selectedStateId: this.modelValue?.state_id,
            selectedCityId: this.modelValue?.city_id,
            zipCode: this.modelValue?.zip_code,
            address: this.modelValue?.address,
        }
    },
    async mounted(){
        try {
            await this.getCountriesList()

            if (this.selectedCountryId) {
                await this.getStatesListByCountryId(this.selectedCountryId)
            }

            if (this.selectedStateId) {
                await this.getCitesListByStateId(this.selectedStateId)
            }
        } finally {
            this.$emit('ready') 
        }
    },
    computed: {
        locationInfo(){
            return {
                country_id: this.selectedCountryId,
                state_id: this.selectedStateId,
                city_id: this.selectedCityId,
                zip_code: this.zipCode,
                address: this.address,
                address_full: this.getFullAddress()
            }
        }
    },
    watch: {
        selectedCountryId(){
            this.getStatesListByCountryId(this.selectedCountryId).then(() => {
                this.selectedStateId = null
                this.selectedCityId = null
            })
            this.$emit('update:modelValue', this.locationInfo);
        },
        selectedStateId(){
            this.selectedCityId = null
            if(this.selectedStateId){
                this.getCitesListByStateId(this.selectedStateId)
            } else {
                this.citiesList = []
            }
            this.$emit('update:modelValue', this.locationInfo);
        },
        selectedCityId(){
            if(this.selectedCityId){
                this.$emit('update:modelValue', this.locationInfo);
            }
        },
        zipCode(){
            this.$emit('update:modelValue', this.locationInfo);
        },
        address(){
            this.$emit('update:modelValue', this.locationInfo);
        }
    },
    methods: {
        async getCountriesList(){
            try {
                const response = await getCountriesList();
                this.countriesList = [{id: 0, name: this.$t('Country')}, ...response]
            } catch (error) {
                console.log(error);
            }
        },
        async getStatesListByCountryId(countryId){
            this.statesList = {id: 0, name: this.$t('State')}
            if(countryId){
                try {
                    const response = await getStatesListByCountry(countryId);
                    this.statesList = [this.statesList, ...response]
                } catch (error) {
                    console.log(error);
                }
            }
        },
        async getCitesListByStateId(stateId){
            this.citiesList = {id: 0, name: this.$t('City')}
            if(stateId){
                try {
                    const response = await getCitesListByState(stateId);
                    this.citiesList = [this.citiesList, ...response]
                } catch (error) {
                    console.log(error);
                }
            }
        },
        getFullAddress(){
            this.address_full = this.address ? this.address : '';      
            if (this.selectedCityId) {
                let city = window._.find(this.citiesList, {id: this.selectedCityId})?.name
                if (city) {
                    this.address_full += this.address_full ? ', ' + city : city;
                }
            }
            if (this.selectedStateId) {
                let state = window._.find(this.statesList, {id: this.selectedStateId})?.name
                if (state) {
                    this.address_full += this.address_full ? ', ' + state : state;
                }
            }
            if (this.zipCode) {
                this.address_full += this.address_full ? ', ' + this.zipCode : this.zipCode;
            }
            if (this.selectedCountryId) {
                let country = window._.find(this.countriesList, {id: this.selectedCountryId})?.name
                if (country) {
                    this.address_full += this.address_full ? ', ' + country : country;
                }
            }
            return this.address_full
        }
    },
    emits: ['update:modelValue', 'ready']
}
</script>