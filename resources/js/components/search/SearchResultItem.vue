<template>
    <router-link 
        :to="to" 
        class="flex items-center mb-4 text-sm last:mb-0 text-inherit"
        @click="onClick"
    >
        <slot name="icon"></slot>
        <div class="flex-1 mx-base-2 global-search-suggestion-box-title min-w-0">
            <slot name="main"></slot>
            <slot name="sub"></slot>
        </div>
    </router-link>
</template>

<script>
export default {
    props: {
        to: Object,
        onClick: { type: Function, default: null }
    }
}
</script>