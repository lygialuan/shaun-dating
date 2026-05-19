<template>
    <div class="w-full" :class="divClass">
        <TextareaPrimevue 
			v-bind="inputAttrs"
			fluid
			:invalid="!!error"
			:rows="rows"
			:class="[`resize-none ${classInput}`]" 
		/>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import TextareaPrimevue from 'primevue/textarea'

export default {
	inheritAttrs: false,
    components: { TextareaPrimevue },
    props: {
		error: {
			type: String,
			default: null
		},
		classInput: {
			type: String,
			default: ''
		},
		rows: {
			type: Number,
			default: 3
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