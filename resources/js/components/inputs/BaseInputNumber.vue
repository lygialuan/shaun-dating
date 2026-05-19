<template>
    <div class="w-full" :class="divClasses">
        <InputNumber
            ref="baseInputNumber"
            v-bind="inputAttrs" 
            :invalid="error"
            :useGrouping="useGrouping"
            :maxFractionDigits="maxFractionDigits"
            :autofocus="autofocus"
            fluid
            @input="onInput"
        >
            <slot></slot>
        </InputNumber>
        <small v-if="error" class="p-error">{{ error }}</small>
    </div>
</template>

<script>
import InputNumber from 'primevue/inputnumber';

export default {
    inheritAttrs: false,
    components: {
        InputNumber
    },
    props: {
        error: {
			type: String,
			default: null
		},
        useGrouping: {
            type: Boolean,
			default: false
        },
        maxFractionDigits: {
            type: Number,
            default: 5
        },
        autofocus: {
            type: Boolean,
            default: false
        }
    },
    computed:{
        divClasses() {
            return this.$attrs.class || '';
        },
        inputAttrs() {
            const rest = { ...this.$attrs };
            delete rest.class;
            return rest;
        }
    },
    mounted(){
        if (this.autofocus) {
            this.$nextTick(() => {
                const input = this.$refs.baseInputNumber.$refs.input.$el;
                if (input && input.focus) {
                    input.focus();
                }
            });
        }
    },
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.value);
        }
    }
}
</script>