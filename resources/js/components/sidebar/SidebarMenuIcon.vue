<template>
    <div
        v-if="icon"
        :style="iconStyle"
        class="sidebar-main-menu-item-icon w-6 h-6 bg-main-color dark:bg-gray-300"
    ></div>
</template>

<script>
export default {
    props: ["icon"],
    computed: {
        iconStyle() {
            return {
                mask: `url(${this.icon}) center center / contain no-repeat`,
                WebkitMask: `url(${this.icon}) center center / contain no-repeat`,
            };
        },
    },
};
</script>
