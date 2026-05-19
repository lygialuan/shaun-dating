<template>
    <div class="w-full" :class="divClasses">
        <DatePicker 
            v-bind="inputAttrs" 
            :invalid="error"
            :dateFormat="dateFormat"
            fluid
        />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import DatePicker from 'primevue/datepicker';

export default {
    inheritAttrs: false,
    components: { DatePicker },
    props: {
		error: {
			type: String,
			default: null
		},
        dateFormat: {
            type: String,
            default: 'yy-mm-dd'
        }
    },
    computed: {
        divClasses() {
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