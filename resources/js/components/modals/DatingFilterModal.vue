<template>
    <div class="flex flex-col gap-3">
        <div class="flex flex-row justify-between items-center"> 
            <h3 class="font-bold text-xl">{{ $t("Narrow your search") }}</h3>
            <span class="font-bold text-sm cursor-pointer" @click="clearData">{{ $t('Clear Filter') }}</span>
        </div>
        <BaseInputSearchLocation v-model="location" :placeholder="$t('Enter location')" :label="$t('Location')"/>
        <BaseSegmented v-model="gender" :options="optionsGender" :label="$t('I am Interested in')" class="mb-base-2"/>
        <BaseInputRange v-if="initAgeRange" :step="1" :label="$t('Age Range')" :value_min="selectedAge?.min" :value_max="selectedAge?.max" :min="initAgeRange?.min" :max="initAgeRange?.max" v-on:childToParent="onAgeRangeChange"/>
        <div class="space-x-2">
            <BaseCheckbox v-model="verifiedProfiles" value="show"/>
            <label>{{ $t('Only show verified profiles') }}</label>
        </div>
        <div class="space-y-2">
            <span class="text-base font-bold mb-base-2 cursor-pointer" @click="showAdvancedFilter">{{ $t("Advanced Filter") }}</span>
            <template v-if="isAdvancedFilter">
                <div v-for="(attribute, index) in initAttributes" :key="`attribute_value_${attribute.id}`" class="mb-base-2">
                    <div v-if="attribute.attribute_values.length > 0" class="space-y-1">
                        <template v-if="attribute.id === 1">
                            <BaseInputRange
                                :step="1"
                                :label="attribute.name"
                                :min="initAttributeRange[attribute.id]?.min"
                                :max="initAttributeRange[attribute.id]?.max"
                                :value_min="selectedAttributeRange[attribute.id]?.min"
                                :value_max="selectedAttributeRange[attribute.id]?.max"
                                @childToParent="val => onAttributeRangeChange(attribute.id, val)"
                                :text_min="getAttributeLabel(attribute, selectedAttributeRange[attribute.id]?.min)"
                                :text_max="getAttributeLabel(attribute, selectedAttributeRange[attribute.id]?.max)"
                            />
                        </template>
                        <template v-else>
                            <div>{{ attribute.name }}</div>
                            <BaseMultiSelect v-model="attributeValues[index]" :options="attribute?.attribute_values" :placeholder="$t('Select attribute')" class="w-full"/>
                        </template>
                    </div>
                </div>
                <div v-for="(interest_attribute, index) in initInterestAttributes" :key="`interest_attribute_value_${interest_attribute.id}`" class="mb-base-2">
                    <div v-if="interest_attribute.attribute_values.length > 0" class="space-y-1">
                        <div>{{ interest_attribute.name }}</div>
                        <BaseMultiSelect v-model="interestAttributeValues[index]" :options="interest_attribute?.attribute_values" :placeholder="$t('Select attribute')" class="w-full"/>
                    </div>
                </div>
            </template>
        </div>
        <BaseButton @click="handleApplyFilter()">{{ $t('Search') }}</BaseButton>
        <BaseButton type="transparent" @click="cancel()" fluid>{{$t('Cancel')}}</BaseButton>
    </div>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { saveFilter } from '@/api/dating';
import BaseInputRange from '@/components/inputs/BaseInputRange.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import BaseMultiSelect from '@/components/inputs/BaseMultiSelect.vue';
import BaseSegmented from '@/components/inputs/BaseSegmented.vue';
import BaseInputSearchLocation from '@/components/inputs/BaseInputSearchLocation.vue';
import BaseCheckbox from '@/components/inputs/BaseCheckbox.vue';

export default {
    components: { BaseInputRange, BaseButton, BaseMultiSelect, BaseSegmented, BaseInputSearchLocation, BaseCheckbox },
    inject: ['dialogRef'],
    data() {
        return {
            filterParams: this.dialogRef.data?.filterParams,
            initPriceRange: null,
            initAgeRange: this.dialogRef.data?.ageRange,
            initAttributes: this.dialogRef.data?.originAttributes,
            selectedAge: {
                min: this.dialogRef.data.filterParams.age?.min,
                max: this.dialogRef.data.filterParams.age?.max
            },
            attributeValues: [],
            gender: this.dialogRef.data.filterParams.gender,
            optionsGender: [],
            location: this.dialogRef.data.filterParams.location,
            verifiedProfiles: this.dialogRef.data.filterParams.verifiedProfiles,
            isAdvancedFilter: this.dialogRef.data.filterParams.isAdvancedFilter,
            initInterestAttributes: this.dialogRef.data?.originInterestAttributes,
            interestAttributeValues: [],
            selectedAttributeRange: {},    
            initAttributeRange: {},    
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user'])
    },
    mounted() {
        this.initAgeRange = {
            min: this.dialogRef.data.ageRange?.min,
            max: this.dialogRef.data.ageRange?.max,
        }
        this.selectedAge = {
            min: this.dialogRef.data.filterParams.age?.min,
            max: this.dialogRef.data.filterParams.age?.max
        }
        
        this.initAttributes = this.dialogRef.data?.originAttributes;
        if (this.filterParams.attributeValues && this.filterParams.attributeValues.length > 0) {
            this.attributeValues = this.initAttributes.map((i) => {
                let selected = [];
                for (const value of i.attribute_values) {
                    if (this.dialogRef.data.filterParams.attributeValues.indexOf(value.id) != -1) {
                        selected.push(value.id);
                    }
                } 
                return selected;
            });
        }
        else {
            this.attributeValues = [];
        }

        this.initInterestAttributes = this.dialogRef.data?.originInterestAttributes;
        if (this.filterParams.interestAttributeValues && this.filterParams.interestAttributeValues.length > 0) {
            this.interestAttributeValues = this.initInterestAttributes.map((i) => {
                let selected = [];
                for (const value of i.attribute_values) {
                    if (this.dialogRef.data.filterParams.interestAttributeValues.indexOf(value.id) != -1) {
                        selected.push(value.id);
                    }
                } 
                return selected;
            });
        }
        else {
            this.interestAttributeValues = [];
        }
        
        this.optionsGender = [
            { id: 0, name: this.$t('Everyone') },
            ...(this.user.genders || [])
        ]

        this.initAttributes.forEach(attr => {
            if(attr.id != 1) return
            if (attr.attribute_values?.length) {
                const ids = attr.attribute_values.map(v => v.id)
                this.initAttributeRange[attr.id] = {min: Math.min(...ids), max: Math.max(...ids)}
                this.selectedAttributeRange[attr.id] = {min: this.initAttributeRange[attr.id].min, max: this.initAttributeRange[attr.id].max}
                if (this.attributeValues?.length > 0) {
                    const values = this.attributeValues.flat().map(Number)
                    const { min, max } = this.initAttributeRange[attr.id]
                    const valid = values.filter(v => v >= min && v <= max)
                    this.selectedAttributeRange[attr.id] = valid.length ? { min: Math.min(...valid), max: Math.max(...valid) } : { min, max }
                }
            }
        })
    },
    watch: {
        selectedUserId(newVal, oldVal) {
            const filteredUserId = newVal.filter(item => !oldVal.includes(item));
            if (filteredUserId.length) {
                this.selectedUserId = filteredUserId
            }
        }
    },
    methods: {
        ...mapActions(useAuthStore, ['setDatingSearchHistory']),
        onCurrencyRangeChange(value) {
            this.selectedPriceRange = value;
        },
        onAgeRangeChange(value) {
            this.selectedAge = value;
        },
        async handleApplyFilter() {
            try {
                const heightAllIds = this.getAttributeValueIds(1)
                let otherAttrIds = this.attributeValues.flat().map(Number)
                otherAttrIds = otherAttrIds.filter(id => !heightAllIds.includes(id))
                const heightSelected = this.filterRange(this.selectedAttributeRange[1].min, this.selectedAttributeRange[1].max, heightAllIds)
                const attributeValues = Array.from(new Set([...otherAttrIds,...heightSelected]))

                if (heightSelected.length) {
                    this.attributeValues[0] = heightSelected
                }

                const payload = {
                    location: this.location,
                    gender: this.gender ?? 0,
                    age: this.selectedAge,
                    verifiedProfiles: this.verifiedProfiles,
                    isAdvancedFilter: this.isAdvancedFilter ?? false,
                    attributeValues: this.isAdvancedFilter ? attributeValues.join(',') : [],
                    attributeValuesFilter: this.isAdvancedFilter ? this.attributeValues : [],
                    interestAttributeValues: this.isAdvancedFilter ? this.interestAttributeValues.flat().join(',') : [],
                    interestAttributeValuesFilter: this.isAdvancedFilter ? this.interestAttributeValues : [],
                }
                await saveFilter({data: payload})
                this.setDatingSearchHistory(payload)
                this.dialogRef.close(payload)
            } catch (error) {
                this.showError(error?.error)
            }
        },
        clearData() {
            this.selectedAge = {
                min: 18,
                max: 28
            }
            this.gender = 0;
            this.location = this.user.dating_addresses_id ? [{
                id: this.user.dating_addresses_id,
                name: this.user.dating_addresses_fulltext
            }] : [];
            this.verifiedProfiles = false;
            this.isAdvancedFilter = false;
            this.attributeValues = [];
            this.interestAttributeValues = [];
        },
        cancel(){
            this.dialogRef.close()
        },
        showAdvancedFilter(){
            if (this.user) {
				let permission = 'dating.allow_use_advanced_filter'
                if(this.checkPermission(permission)){
                    this.isAdvancedFilter = !this.isAdvancedFilter
                }
			}
        },
        onAttributeRangeChange(attributeId, value) {
            this.selectedAttributeRange[attributeId] = value
        },
        getAttributeLabel(attribute, id) {
            return attribute.attribute_values.find(v => v.id === id)?.name || ''
        },
        getAttributeValueIds(attributeId) {
            const attr = this.initAttributes.find(a => a.id === attributeId)
            return attr ? attr.attribute_values.map(v => Number(v.id)) : []
        },
        filterRange(min, max, availableIds) {
            return availableIds.filter(
                id => id >= Number(min) && id <= Number(max)
            )
        }
    }
}
</script>
