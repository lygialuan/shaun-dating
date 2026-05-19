<template>
    <button
        class="sidebar-user-menu-list-item-wrap flex items-center text-start gap-4 p-2 w-full transition-colors text-main-color lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color"
        @click="handleLogout"
    >
		<SidebarMenuIcon :icon="item.icon" />
		<span class="flex-1 font-semibold">{{ item.name }}</span>
    </button>
</template>

<script>
import { useAuthStore } from '@/store/auth';
import SidebarMenuIcon from "./SidebarMenuIcon.vue";

export default {
    components: { SidebarMenuIcon },
    props: ["item"],
    methods:{
        async handleLogout() {
            try {
                await useAuthStore().logout();
                window.location.href = window.siteConfig.siteUrl;
            } catch (error) {
                console.log(error);
            }
        }
    }
};
</script>
