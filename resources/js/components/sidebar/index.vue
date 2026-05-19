<template>
    <ul class="sidebar-user-menu-list">
        <SidebarMenuItem 
            v-for="(menuItem, index) in menus.main" 
			:key="index" 
			:item="menuItem"
        />
    </ul>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import SidebarMenuItem from './SidebarMenuItem.vue';

export default {
    components: {
        SidebarMenuItem
    },
    computed: {
		...mapState(useAppStore, ['menus'])
    }
};
</script>
