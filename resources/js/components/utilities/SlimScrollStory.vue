<template>
    <div>
        <div class="relative">
            <div v-show="user.rtl ? !isNextButtonDisabled : !isPrevButtonDisabled" @click="onPrevButtonClick">
                <slot name="prev" v-if="!isMobile">
                    <button class="absolute top-1/2 -translate-y-1/2 -left-10 flex items-center justify-center w-8 h-8 bg-white border border-gray-300 hover:bg-gray-300 rounded-full text-main-color dark:bg-slate-800 dark:border-slate-800 dark:hover:bg-gray-700 dark:text-white z-30"><BaseIcon size="14" name="arrow_circle_left_nobg"/></button>
                </slot>
            </div>
            <div class="slimscroll-content flex" :style="{gap: `${gap}px`, padding: `${padding}px`}" ref="slimScrollContent" @scroll="onScroll">
                <slot></slot>
            </div>
            <div v-show="user.rtl ? !isPrevButtonDisabled : !isNextButtonDisabled" @click="onNextButtonClick">
                <slot name="next" v-if="!isMobile">
                    <button class="absolute top-1/2 -translate-y-1/2 -right-10 flex items-center justify-center w-8 h-8 border border-gray-300 hover:bg-gray-300 rounded-full text-main-color dark:bg-slate-800 dark:border-slate-800 dark:hover:bg-gray-700 dark:text-white z-30"><BaseIcon size="14" name="arrow_circle_right_nobg"/></button>
                </slot>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '@/store/app'

export default {
    components: { BaseIcon },
    props: {
        gap: {
			type: [Number, String],
			default: 10
		},
        padding: {
			type: [Number, String],
			default: 0
		}
    },
	data(){
		return {
			isPrevButtonDisabled: true,
            isNextButtonDisabled: true
		}
	},
    mounted() {
        this.updateButtonState()
        window.addEventListener("resize", () => {
			this.updateButtonState();
		})
    },
    updated(){
        this.updateButtonState()
        window.addEventListener("resize", () => {
			this.updateButtonState();
		})
    },
    computed:{
        ...mapState(useAppStore, ['isMobile']),
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        onScroll(event) {
            this.updateButtonState();
            event.preventDefault();
        },
		updateButtonState() {
            const content = this.$refs.slimScrollContent;
            if (!content) return; 
            
            let { scrollLeft, scrollWidth } = content;

            setTimeout(() => {
                const width = Math.ceil(content.getBoundingClientRect().width);
                this.isPrevButtonDisabled = scrollLeft === 0;
                this.isNextButtonDisabled = Math.abs(parseInt(scrollLeft)) >= scrollWidth - width;
            }, 200);
        },
		onPrevButtonClick() {
            const content = this.$refs.slimScrollContent;
            const width = Math.ceil(content.getBoundingClientRect().width) * 0.67;
            const pos = content.scrollLeft - width;

            content.scrollLeft = pos;
        },
        onNextButtonClick() {
            const content = this.$refs.slimScrollContent;
            const width = Math.ceil(content.getBoundingClientRect().width) * 0.67;
            const pos = content.scrollLeft + width;
            const lastPos = content.scrollWidth - width;

            content.scrollLeft = pos >= lastPos ? lastPos : pos;
        }
    }
}
</script>