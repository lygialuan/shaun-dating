<template>
    <div class="base-input-tel w-full" :class="divClass">
        <VueTelInput
            v-bind="inputAttrs"
            :mode="mode"
            :inputOptions="inputOptions"
        />
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import { VueTelInput } from 'vue-tel-input';
import 'vue-tel-input/vue-tel-input.css';

export default {
    inheritAttrs: false,
    components: { VueTelInput },
    props: {
		error: {
			type: String,
			default: null
		},
        mode: {
			type: String,
			default: 'international'
		},
        autofocus: {
            type: Boolean,
			default: false
        },
        placeholder: {
			type: String,
			default: null
		}
    },
    data(){
        return{
            inputOptions:{
                placeholder: this.placeholder,
                autofocus: this.autofocus
            }
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
