<template>
    <template v-if="menus.profile.length">
        <div class="p-3">
            <h3 class="sidebar-user-menu-title text-sub-color dark:text-slate-400">{{ $t('Profile Menu') }}</h3>
        </div>
        <ul class="sidebar-user-menu-list">
            <SidebarMenuItem 
                v-for="(menuItem, index) in menus.profile" 
                :key="index" 
                :item="menuItem"
            />
        </ul>
    </template>
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
