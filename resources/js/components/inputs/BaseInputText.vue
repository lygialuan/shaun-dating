<template>
    <div class="w-full" :class="divClass">
        <div :class="{'p-input-icon-left': left_icon, 'p-input-icon-right': right_icon}">
            <BaseIcon v-if="left_icon" :name="left_icon" size="18" class="p-inputtext-icon text-input-icon-color dark:text-slate-400"/>
            <InputText 
                ref="baseInputText" 
                v-bind="inputAttrs"
                fluid
                :invalid="!!error"
                v-tooltip.focus.bottom="isMobile && tooltip_mobile" 
            />
            <BaseIcon v-if="right_icon" :name="right_icon" size="18" class="p-inputtext-icon text-input-icon-color dark:text-slate-400" />
        </div>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import InputText from 'primevue/inputtext';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAppStore } from '../../store/app'

export default {
    inheritAttrs: false,
    components: { InputText, BaseIcon },
    props: {
        left_icon: {
			type: String,
			default: null
		},
        right_icon: {
			type: String,
			default: null
		},
        tooltip_mobile: {
            type: String,
            default: null
        },
        inputClass: {
            type: String,
            default: null
        },
        error: {
			type: String,
			default: null
		}
    },
    computed: {
        ...mapState(useAppStore, ['isMobile']),
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