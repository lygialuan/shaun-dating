<template>
    <div class="w-full" :class="divClasses">
        <MultiSelect v-model="value" optionLabel="name" display="chip" optionValue="id" filter @change="handleOnChange($event)" :invalid="error" v-bind="selectAttrs" @filter="handleFilter($event)" @before-show="handleBeforeShow($event)" @show="handleShow($event)" />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import MultiSelect from 'primevue/multiselect';
export default {
    inheritAttrs: false,
    components: { MultiSelect },
    props: {
        modelValue: {
			type: [Array],
			default: null
		},
        error: {
            type: String,
			default: null
		},
        emptyMessage: {
            type: String,
            default: ''
        }
    },
    data(){
        return {
            value: this.modelValue
        }
    },
    computed: {
        divClasses() {
            return this.$attrs.class || '';
        },
        selectAttrs() {
            const rest = { ...this.$attrs };
            return rest;
        }
    },
    watch:{
        modelValue(newValue){
            this.value = newValue
        }
    },
    methods: {
        handleOnChange(event) {
            this.value = event.value;
            this.$emit('update:modelValue', event.value);
            this.$emit('change', event.value);
        },
        handleFilter(event){
            this.$emit('filter', event);
        },
        handleShow(event){
            this.$emit('show', event);
        },
        handleBeforeShow(event){
            this.$emit('before-show', event);
        },
    },
    emits: ['update:modelValue', 'change', 'filter', 'show', 'before-show']
}
</script>