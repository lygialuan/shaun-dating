<template>
     <div class="flex items-center justify-center h-full overflow-hidden">
        <div 
            ref="vibbContainer" 
            class="vibb-container scrollbar-hidden md:px-4"
            :class="{'fixed inset-0 z-10': screen.md}"
            :style="{ 'overflow-y': (showVibbComment && screen.md) ? 'hidden' : 'scroll' }"
            @scroll="onScroll">
            <div v-if="loading" class="h-full flex items-center justify-center">
                <Loading />
            </div>
            <template v-else>
                <template v-if="vibbsList.length">
                    <VibbItem v-if="currentVibb" :item="currentVibb" />
                    <VibbItem v-for="vibb in filteredVibbs" :key="vibb.id" :item="vibb" />
                    <InfiniteLoading @infinite="handleLoadInfinite">
                        <template #spinner><Loading /></template>
                        <template #complete><span></span></template>
                    </InfiniteLoading>
                </template>
            </template>
        </div>
        <div class="flex h-full">
            <div class="hidden md:flex flex-col gap-y-base-2 items-center justify-center pe-4">
                <button class="vibb-action-button w-12 h-12 flex items-center justify-center bg-white shadow-md rounded-full relative hover:bg-hover dark:bg-slate-800 dark:text-white dark:shadow-slate-600 transition-all duration-300 ease-linear" :class="isAtTop ? 'invisible opacity-0 top-[64px]' : 'visible opacity-100 top-0 z-10'" @click="handleScrollUp">
                    <BaseIcon name="arrow_up" />
                </button>
                <button class="vibb-action-button w-12 h-12 flex items-center justify-center bg-white shadow-md rounded-full relative hover:bg-hover dark:bg-slate-800 dark:text-white dark:shadow-slate-600 transition-all duration-300 ease-linear" :class="isAtBottom ? 'invisible opacity-0 -top-[64px]' : 'visible opacity-100 top-0 z-10'" @click="handleScrollDown">
                    <BaseIcon name="arrow_down" />
                </button>
            </div>
            <VibbComment />
        </div>
    </div>
</template>

<script>
import { mapState } from "pinia";
import { useVibbStore } from "@/store/vibb";
import { useAppStore } from "@/store/app";
import VibbItem from '@/components/vibb/VibbItem.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'
import VibbComment from "@/components/vibb/VibbComment.vue";
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { VibbItem, InfiniteLoading, Loading, VibbComment, BaseIcon },
    props:{
        loading: {
            type: Boolean,
            default: true
        },
        vibbsList: {
            type: Array,
            default: () => []
        },
        currentVibb: {
            type: Object,
            default: null
        }
    },
    data(){
        return {
            isAtTop: true,
            isAtBottom: true
        }
    },
    computed: {
        ...mapState(useVibbStore, ['showVibbComment']),
        ...mapState(useAppStore, ['screen', 'isMobile']),
        filteredVibbs(){
            return this.vibbsList.filter(vibb => vibb.id != this.currentVibb?.id);
        }
    },
    watch:{
        vibbsList(newVal, oldVal){
            if(oldVal.length !== newVal.length){
                this.isAtBottom = this.vibbsList.length > 1 ? false : true
            }
        },
        deletedPost(){
            this.updateButtonState()
            if(this.isAtBottom){
                this.handleScrollUp()
            } else {
                this.handleScrollDown()
            }
        }
    },
    methods:{
        onScroll() {
            this.updateButtonState();
        },
        updateButtonState() {
            const vibbContainer = this.$refs.vibbContainer;
            const scrollTop = vibbContainer.scrollTop || 0;
            const scrollHeight = vibbContainer.scrollHeight || 0;
            const clientHeight = vibbContainer.clientHeight || 0;
            this.isAtTop = scrollTop <= 1;
            this.isAtBottom = scrollHeight - scrollTop === clientHeight;
        },
        handleScrollUp() {
            const container = this.$refs.vibbContainer;
            if (container) {
                container.scrollTo({
                    top: container.scrollTop - container.clientHeight, 
                    behavior: 'smooth'
                });
            }
        },
        handleScrollDown() {
            const container = this.$refs.vibbContainer;
            if (container) {
                container.scrollTo({
                    top: container.scrollTop + container.clientHeight,
                    behavior: 'smooth'
                });
            }
        },
        handleLoadInfinite(state){
            this.$emit('load-more', state)
        }
    },
    emits: ['load-more']
}
</script>
