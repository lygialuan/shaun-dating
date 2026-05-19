<template>
	<div class="relative" :class="{'overflow-hidden': showContentWarning}" @click="handleClickContentWarningWrapper($event)">
		<div :class="{'blur-2xl': showContentWarning}" class="h-full">
			<slot></slot>
		</div>
		<template v-if="contentWarningList.length">
			<div v-if="showContentWarning" class="flex justify-center items-center px-12 py-6 absolute inset-0 z-30 bg-black-trans-5">
				<div class="flex flex-col items-center gap-2 text-center text-white">
					<BaseIcon name="eye_slash" />
					<div class="text-base-lg font-semibold truncate-3">{{ contentWarningTitle }}</div>
					<div>{{ $t('The post author flagged this post as showing sensitive content.') }}</div>
					<BaseButton @click.stop="handleToggleShow">{{ $t('Show Content') }}</BaseButton>
				</div>
			</div>
			<slot v-else name="hide-button" :toggle="handleToggleShow">
				<BaseButton @click.stop="handleToggleShow" class="absolute end-2 top-2 z-30">{{ $t('Hide Content') }}</BaseButton>
			</slot>
		</template>
	</div>
</template>

<script>
import { mapActions } from 'pinia'
import { usePostStore } from '@/store/post'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseIcon, BaseButton },
	props:{
		post:{
			type: Object,
			default: null
		},
		contentWarningList: {
			type: Array,
			default: null
		},
		buttonOffsetX: {
			type: Number,
			default: 0
		},
		buttonOffsetY: {
			type: Number,
			default: 0
		}
	},
	data(){
		return{
			showContentWarning: this.contentWarningList.length && (this.post ? this.post?.showContentWarning !== false : true)
		}
	},
	watch:{
		contentWarningList(newValue){
			this.showContentWarning = newValue.length && (this.post ? this.post?.showContentWarning !== false : true)
		},
		post: {
            handler: function() {
                this.showContentWarning = this.contentWarningList.length && (this.post ? this.post?.showContentWarning !== false : true)
            },
            deep: true
        }
	},
	computed: {
		contentWarningTitle() {
			const names = this.contentWarningList.map(item => item.name);
			return names.length === 0 ? '' : `${this.$t('Content warning')}: ${names.join(', ').replace(/, ([^,]+)$/, ` ${this.$t('and')} $1`)}`;
		}
	},
	methods: {
		...mapActions(usePostStore, ['doToggleShowContentWarning']),
		handleToggleShow(){
			this.showContentWarning = !this.showContentWarning
			if(this.post){
				this.doToggleShowContentWarning({
					subject_id: this.post.id,
					action: this.showContentWarning
				})
			}
		},
		handleClickContentWarningWrapper(e){
			if(this.showContentWarning){
				e.stopPropagation();
			}
		}
	}
}
</script>