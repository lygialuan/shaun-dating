<template>
	<a v-if="href" :href="href" :class="buttonClass" :disabled="disabled">
		<div class="flex items-center" :class="{'opacity-0': loading, [`gap-${gapIcon}`]: $slots.default}">
			<BaseIcon v-if="icon" :name="icon" :size="iconSize" />
			<slot></slot>
			<span v-if="badge" :class="badgeClass">{{ badge }}</span>
		</div>		
		<div v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" :size="iconSize" />
		</div>
	</a>
	<router-link v-else-if="to" :to="to" :class="buttonClass" :disabled="disabled">
		<div class="flex items-center" :class="{'opacity-0': loading, [`gap-${gapIcon}`]: $slots.default}">
			<BaseIcon v-if="icon" :name="icon" :size="iconSize" />
			<slot></slot>
			<span v-if="badge" :class="badgeClass">{{ badge }}</span>
		</div>		
		<div v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" :size="iconSize" />
		</div>
	</router-link>
	<button v-else :class="buttonClass" :disabled="disabled">
		<div class="flex items-center" :class="{'opacity-0': loading, [`gap-${gapIcon}`]: $slots.default}">
			<BaseIcon v-if="icon" :name="icon" :size="iconSize" />
			<slot></slot>
			<span v-if="badge" :class="badgeClass">{{ badge }}</span>
		</div>		
		<div v-if="loading" class="absolute z-1 top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2">
			<BaseIcon name="spinner" class="spinner" :size="iconSize" />
		</div>
	</button>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
	components: {
		BaseIcon
	},
	props: {
		type: {
			type: String, // primary, outlined, secondary, secondary-outlined, danger, danger-outlined, transparent, success, warning
			default: 'primary'
		},
		size: {
			type: String, // xs, sm, md, lg
			default: 'md'
		},
		loading: {
			type: Boolean,
			default: false
		},
		href: {
			type: String,
			default: ''
		},
		to: {
			type: Object,
			default: null
		},
		disabled: {
			type: Boolean,
			default: false
		},
		icon: {
			type: String,
			default: ''
		},
		badge: {
			type: [String, Number],
            default: null
		},
		fluid: {
			type: Boolean,
			default: false
		}
	},
	computed: {
        buttonClass() {
            return `btn btn-${this.type} btn-size-${this.size} ${this.disabled ? 'opacity-50 pointer-events-none' : 'hover:bg-hover'} ${this.$slots.default ? '' : 'btn-icon-only'} ${this.fluid ? 'btn-block' : ''} ${this.loading ? 'pointer-events-none' : ''}`
        },
		badgeClass(){
			return `btn-badge btn-badge-${this.size} inline-flex justify-center items-center leading-none border rounded-full`
		},
		iconSize(){
			switch (this.size) {
				case 'xs':
					return '16';
				case 'sm':
					return '20';
				case 'md':
					return '24';
				case 'lg':
					return '26';
				default:
					return '22';
			}
		},
		gapIcon(){
			if(this.$slots.default){
				switch (this.size) {
					case 'xs':
						return 'base-1';
					case 'sm':
						return '2';
					case 'md':
						return 'base-2';
					case 'lg':
						return '3';
					default:
						return 'base-2';
				}
			}
			return '0'
		}
    }
}
</script>
