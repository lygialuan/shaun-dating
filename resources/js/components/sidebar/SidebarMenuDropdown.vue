<template>
    <div class="w-[300px]">
        <ul class="sidebar-user-menu-child-list">
            <SidebarMenuItem v-for="(menuItem, index) in items" :key="index" :item="menuItem" />
        </ul>
    </div>
</template>

<script>
import { defineAsyncComponent } from 'vue';
const SidebarMenuItem = defineAsyncComponent(() => import('./SidebarMenuItem.vue'));

export default {
    props: {
        items: Array
    },
    components: {
        SidebarMenuItem
    }
};
</script>
