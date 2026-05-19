<template>
    <div class="w-full" :class="divClasses">
        <SelectPrimevue 
            fluid 
            :emptyMessage="emptyMessage ? emptyMessage : $t('No results found')" 
            :invalid="!!error" 
            v-bind="selectAttrs"
        />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import SelectPrimevue from 'primevue/select';

export default {
    inheritAttrs: false,
    components: { SelectPrimevue },
    props: {
        error: {
            type: String,
			default: null
		},
        emptyMessage: {
            type: String,
            default: ''
        }
    },
    computed: {
        divClasses() {
            return this.$attrs.class || '';
        },
        selectAttrs() {
            const rest = { ...this.$attrs };
            delete rest.class;
            return rest;
        }
    }
}
</script>