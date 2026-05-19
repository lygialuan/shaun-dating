<template>
    <div class="w-full flex flex-col space-y-2" :class="$attrs.class">
        <label v-if="label">{{ label }}</label>
        <div class="grid grid-cols-3 gap-2">
            <BaseButton  v-for="item in options" :key="item.id" @click="onSelect(item.id)" :type="model === item.id ? 'primary' : 'outlined'">
                {{ item.name }}
            </BaseButton>
        </div>
        <small v-if="error" class="p-error block mt-1">
            {{ error }}
        </small>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    inheritAttrs: false,
    components: { BaseButton },
    props: {
        modelValue: {
            type: [String, Number],
            default: null
        },
        options: {
            type: Array,
            required: true
        },
        error: {
            type: String,
            default: null
        },
        label: {
            type: String,
            default: null
        }
    },
    emits: ['update:modelValue', 'change'],
    computed: {
        model: {
            get() {
                return this.modelValue;
            },
            set(value) {
                this.$emit('update:modelValue', value);
            }
        }
    },
    methods: {
        onSelect(value) {
            if (this.model === value) return;
            this.model = value;
            this.$emit('change', value);
        }
    }
}
</script>
