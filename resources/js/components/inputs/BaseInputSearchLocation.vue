<template>
    <div class="w-full flex flex-col gap-1 relative" :class="$attrs.class">
        <label v-if="label" class="text-sm font-medium">{{ label }}</label>
        <div class="flex flex-wrap items-center gap-2 rounded-lg border px-2 py-2 transition focus-within:ring-2 dark:border-dark-form-surface">
            <span v-for="(item, index) in model" :key="`${item.id}-${index}`" class="flex items-center gap-1 rounded-md bg-gray-100 px-2 py-1 text-sm dark:bg-dark-form-surface">
                {{ item.name }}
                <BaseIcon @click="remove(index)" name="close" :size="12" class="cursor-pointer"/>
            </span>

            <input v-if="multiple || model.length === 0" v-model="input" type="text" class="flex-1 min-w-[120px] border-none outline-none text-sm dark:bg-dark-form-base" :placeholder="placeholder"
                @keydown.enter.prevent="addByInput"
                @blur="addByInput"
            />
        </div>
        <div v-if="filteredSuggestions.length" class="absolute top-full left-0 z-50 mt-1 w-full rounded-lg border bg-white shadow max-h-48 overflow-y-auto dark:bg-dark-form-surface dark:border-dark-form-surface">
            <div v-for="item in filteredSuggestions" :key="item.id" class="cursor-pointer px-3 py-2 text-sm hover:bg-gray-100 dark:hover:bg-dark-form-base" @mousedown.prevent="selectSuggestion(item)">
                {{ item.name }}
            </div>
        </div>
    </div>
</template>

<script>
import { suggestionLocations } from '@/api/dating';
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    inheritAttrs: false,
    components: { BaseIcon },
    props: {
        modelValue: {
            type: Array,
            default: () => []
        },
        multiple: {
            type: Boolean,
            default: false
        },
        label: String,
        placeholder: {
            type: String,
            default: ''
        }
    },
    emits: ['update:modelValue', 'change'],
    data() {
        return {
            input: '',
            suggestions: []
        }
    },
    watch: {
        input(newVal) {
            if (!newVal || newVal.trim().length < 2) {
                this.suggestions = []
                return
            }

            clearTimeout(this.debounceTimer)

            this.debounceTimer = setTimeout(() => {
                this.getSuggestionLocations(newVal)
            }, 400) 
        }
    },
    computed: {
        model: {
            get() {
                return Array.isArray(this.modelValue) ? this.modelValue : []
            },
            set(value) {
                this.$emit('update:modelValue', value)
            }
        },
        filteredSuggestions() {
            if (!this.input) return []
            const keyword = this.input.toLowerCase()
            return this.suggestions
                .filter(item =>
                    item.name.toLowerCase().includes(keyword)
                )
                .filter(item =>
                    !this.model.some(m => m.name === item.name)
                )
        }
    },
    methods: {
        addByInput() {
            const value = this.input.trim()
            if (!value) return
            if (this.model.some(m => m.name === value)) return this.input = ''
            const item = { id: 0, name: value }
            const newValue = this.multiple ? [...this.model, item] : [item]
            this.model = newValue
            this.input = ''
            this.$emit('change', newValue)
        },
        selectSuggestion(item) {
            const newValue = this.multiple ? [...this.model, item] : [item]
            this.model = newValue
            this.input = ''
            this.$emit('change', newValue)
        },
        remove(index) {
            const copy = [...this.model]
            copy.splice(index, 1)
            this.model = copy
            this.$emit('change', copy)
        },
        async getSuggestionLocations(keyword){
            try {
                const response = await suggestionLocations(keyword);
                this.suggestions = response
            } catch (error) {
                console.log(error);
            }
        },
    }
}
</script>
