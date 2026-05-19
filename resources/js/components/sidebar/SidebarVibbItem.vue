<template>
    <button
        class="sidebar-user-menu-list-item-wrap flex items-center text-start gap-4 p-2 w-full transition-colors text-main-color lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color"
        @click="handleOpenVibb"
    >
		<SidebarMenuIcon :icon="item.icon" />
		<span class="flex-1 font-semibold">{{ item.name }}</span>
    </button>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import SidebarMenuIcon from "./SidebarMenuIcon.vue";

export default {
    components: { SidebarMenuIcon },
    props: ["item"],
    computed:{
        ...mapState(useAppStore, ['setOpenedModalCount'])
    },
    methods:{
        handleOpenVibb() {
            this.openVibb()
        }
    }
};
</script>
