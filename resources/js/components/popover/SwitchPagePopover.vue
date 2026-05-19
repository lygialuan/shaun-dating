<template>
    <Teleport to="body">
        <div v-if="selectedPage" class="bg-main-bg flex justify-center items-center p-4 fixed inset-0 z-[1200] dark:bg-dark-web-wash">
            <div class="flex flex-col items-center gap-5 text-center">
                <div class="relative cursor-pointer">
                    <BaseIcon name="circle_switch" size="100" svgClass="animate-spin-slow" class="relative z-10"/>
                    <img :src="selectedPage.avatar" alt="" class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-20 h-20 rounded-full object-cover object-center">
                </div>
                <div class="text-lg font-bold">{{ $t('Switching to') + ' ' + selectedPage.name + ' ...'}}</div>
            </div>
        </div>
    </Teleport>
</template>

<script>
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { mapState } from 'pinia'
import { useUtilitiesStore } from '@/store/utilities'
export default {
    components: { BaseIcon },
    computed: {
        ...mapState(useUtilitiesStore, ['selectedPage']),
    }
}
</script>