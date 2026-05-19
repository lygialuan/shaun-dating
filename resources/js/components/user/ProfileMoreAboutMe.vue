<template>
    <div class="bg-white rounded-[18px] p-4 mt-4 dark:bg-dark-form-surface" v-if="owner || (selectedAttributes && selectedAttributes.length > 0)">
        <div class="flex justify-between items-center mb-2">
            <div class="flex flex-row space-x-1">
                <h3 class="font-semibold mb-2">{{ $t('More about me') }}</h3>
                <span class="font-bold text-red-500" v-if="config.present_profile_is_active && config.present_profile_more_about && owner">+{{ config.present_profile_more_about }}%</span>
            </div>
            <button v-if="owner">
                <BaseSelectAttributes :label="$t('Add more about me')" @update:items="onUpdateAttributes" v-model="values" :attribute-options="originAttributes" :options="attributeValues" :att="'aboutme'"/>
            </button>
        </div>
        <ul class="space-y-2 text-sm text-gray-700 dark:text-dark-text-base-gray font-normal" v-if="groupedAttributes.length > 0">
            <li v-for="item in groupedAttributes" :key="item.category_name" class="flex gap-2 items-center">
                <template v-if="item.category_name">
                    <div
                        v-if="item.icon"
                        :style="{
                            mask: `url(${item.icon}) center / contain no-repeat`,
                            WebkitMask: `url(${item.icon}) center / contain no-repeat`
                        }"
                        class="w-4 h-4 sm:w-5 sm:h-5 shrink-0 bg-gray-700 dark:bg-white">
                    </div>
                    <span> {{ item.category_name }}: {{ item.names.join(', ') }}</span>
                </template>
            </li>
        </ul>
        <div v-else-if="owner" class="text-sm text-gray-600 leading-relaxed dark:text-dark-text-base-gray font-normal">
            {{  $t("Add more about me info")  }}
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
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
            values: this.user.attributes?.map(x => x.id) ?? [],
            attributeValues: [],
            selectedAttributes: this.user.attributes ?? [],
        }
    },
    computed:{
		...mapState(useAppStore, ['config']),
		...mapState(useDatingStore, ['originAttributes']),
        groupedAttributes() {
            if (!this.selectedAttributes) return []

            const map = {}

            this.selectedAttributes.forEach(item => {
                if (!item.category_name) return

                if (!map[item.category_name]) {
                    map[item.category_name] = {
                        category_name: item.category_name,
                        icon: item.icon,
                        names: []
                    }
                }

                map[item.category_name].names.push(item.name)
            })

            return Object.values(map)
        }
	},
    methods: {
        onUpdateAttributes(items) {
            this.selectedAttributes = items;
        }
    }
}
</script>