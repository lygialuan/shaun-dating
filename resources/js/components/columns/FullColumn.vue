<template>
    <div v-if="$slots.top" id="top" class="flex-1 min-w-0 mb-base-2">
        <slot name="top"></slot>
    </div>
    <div class="flex gap-base-2 flex-col md:flex-row">
        <div v-if="$slots.center" id="center" class="flex-2 min-w-0">
            <slot name="center"></slot>
        </div>
        <template v-if="$slots.right">
            <div v-if="screen.md" id="right" class="flex-1 min-w-0" :class="{'hidden md:block': !keepRightColumn}">
                <slot name="right"></slot>
            </div>
            <StickySidebar v-else id="right" class="flex-1 min-w-0 hidden md:block" :topSpacing="authenticated ? 88 : 96" :bottomSpacing="50">
                <slot name="right"></slot>
            </StickySidebar>
        </template>
    </div>
    <div v-if="$slots.bottom" id="bottom" class="flex-1 min-w-0 mt-base-2">
        <slot name="bottom"></slot>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import StickySidebar from "vue3-sticky-sidebar";

export default {
    props: { 
        keepRightColumn: {
            type: Boolean,
            default: false
        }
    },
    components: {
        StickySidebar
    },
    computed: {
        ...mapState(useAuthStore, ['user', 'authenticated']),
        ...mapState(useAppStore, ['screen'])
    }
}
</script>