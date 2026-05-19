<template>
	<div class="base-color-picker">
        <input type="color" :value="modelValue" @input="onInput($event)" />
    </div>
</template>
<script>
export default {
    props: {
        modelValue: {
			type: String,
			default: '#fff'
		}
    },
    methods: {
        onInput(event) {
            this.$emit('update:modelValue', event.target.value);
            this.$emit('change', event.target.value);
        }
    },
    emits: ['update:modelValue', 'change']
}
</script>