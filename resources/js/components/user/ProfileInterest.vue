<template>
    <div class="bg-white rounded-[18px] p-4 mt-4 dark:bg-dark-form-surface" v-if="(owner || (sortedAttributes && sortedAttributes.length > 0)) && originInterestAttributes && originInterestAttributes.length > 0">
        <div class="flex justify-between items-center mb-2">
            <div class="flex flex-row space-x-1">
                <h3 class="font-semibold mb-2">{{ $t('Interests') }}</h3>
                <span class="font-bold text-red-500" v-if="config.present_profile_is_active && config.present_profile_interests && owner">+{{ config.present_profile_interests }}%</span>
            </div>
            <button v-if="owner">
                <BaseSelectAttributes :label="$t('Add interests')" @update:items="onUpdateAttributes" v-model="values" :attribute-options="originInterestAttributes" :options="attributeValues" :att="'interest'"/>
            </button>
        </div>
        <div v-if="sortedAttributes && sortedAttributes.length > 0" class="flex flex-wrap gap-2 sm:gap-3 text-sm text-gray-600">
            <template v-for="item in sortedAttributes">
                <div v-if="item.name" :key="item.id" class="flex items-center gap-2 bg-gray-100 p-2 rounded-lg max-w-full dark:bg-[#2C2C2C] dark:text-dark-text-base-gray font-normal">
                <div
                    v-if="item.icon"
                    :style="{
                        mask: `url(${item.icon}) center / contain no-repeat`,
                        WebkitMask: `url(${item.icon}) center / contain no-repeat`
                    }"
                    class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 bg-gray-700 dark:bg-white">
                </div>
                    <span class="truncate">
                        {{ item.name }}
                    </span>
                </div>
            </template>
        </div>
        <div v-else-if="owner" class="text-sm text-gray-600 leading-relaxed dark:text-dark-text-base-gray font-normal">
            {{  $t("Add interests")  }}
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app'
import { useDatingStore } from '@/store/dating'
import BaseSelectAttributes from '@/components/inputs/BaseSelectAttributes.vue';

export default {
    components: { 
        BaseSelectAttributes
    },
    props: {
        user: {
            type: Object,
            default: null
        },
        owner: {
            type: Boolean,
            default: false
        }
    },
    data() {
        return {
            values: this.user.interest_attributes?.map(x => x.id) ?? [],
            attributeValues: [],
            selectedAttributes: this.user.interest_attributes ?? [],
        }
    },
    computed:{
		...mapState(useAppStore, ['config']),
		...mapState(useDatingStore, ['originInterestAttributes']),
        sortedAttributes() {
            if (!this.selectedAttributes?.length) return []
            return [...this.selectedAttributes].sort((a, b) => {
                const catA = a.category_name || ''
                const catB = b.category_name || ''

                if (catA !== catB) {
                    return catA.localeCompare(catB)
                }

                const nameA = a.name || ''
                const nameB = b.name || ''

                return nameA.localeCompare(nameB)
            })
        }
	},
    methods: {
        onUpdateAttributes(items) {
            this.selectedAttributes = items;
        }
    }
}
</script>