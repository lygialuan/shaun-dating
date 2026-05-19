<template>
    <div :class="divClass">
        <InputOtp :length="length" :invalid="error" v-bind="inputAttrs" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import InputOtp from 'primevue/inputotp';

export default {
    components: { InputOtp },
    inheritAttrs: false,
    props: {
		error: {
			type: String,
			default: null
		},
        length: {
            type: Number,
            default: 6
        }
    },
    computed: {
        divClass() {
            return this.$attrs.class || '';
        },
        inputAttrs() {
            const rest = { ...this.$attrs };
            delete rest.class;
            return rest;
        }
    }
}
</script>