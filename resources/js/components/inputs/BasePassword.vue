<template>
    <div>
        <div class="p-input-icon-right" :class="{'p-input-icon-left': left_icon, 'p-disabled': disabled}">
            <BaseIcon v-if="left_icon" :name="left_icon" size="18" class="p-inputtext-icon text-input-icon-color dark:text-slate-400"/>
            <InputText 
                ref="basePassword" 
                :type="inputType" 
                v-bind="inputAttrs"
                fluid
                :invalid="!!error"
                :disabled="disabled"
                :autofocus="autofocus"
            />
            <BaseIcon v-if="right_icon" :name="passwordIcon" size="18" @click="showHiddenPassword()" class="p-inputtext-icon text-input-icon-color dark:text-slate-400 cursor-pointer" />
        </div>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import InputText from 'primevue/inputtext';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { InputText, BaseIcon },
    data() {
        return {
            passwordIcon: 'eye_slash',
            inputType: 'password'
        }
    },
    props: {
		error: {
			type: String,
			default: null
		},
        left_icon: {
			type: [String, Boolean],
			default: 'key'
		},
        right_icon: {
			type: Boolean,
			default: true
		},
        disabled: {
            type: Boolean,
            default: false
        },
        autofocus: {
            type: Boolean,
			default: false
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
    },
    mounted() {
		if(this.autofocus){
            this.$nextTick(() => {
                const input = this.$refs.basePassword.$el;
                if (input && input.focus) {
                    input.focus();
                }
            });
        }
	},
    methods: {
        showHiddenPassword() {
            if (this.inputType == 'password') {
                this.inputType = 'text'
                this.passwordIcon = 'eye'
            } else {
                this.inputType = 'password'
                this.passwordIcon = 'eye_slash'
            }
        }
    }
}
</script>