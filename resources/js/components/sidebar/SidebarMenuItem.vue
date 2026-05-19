<template>
    <li
        class="sidebar-user-menu-list-item">
        <component
            :is="menuComponent"
            :item="item"
        />
    </li>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import SidebarHeaderItem from "./SidebarHeaderItem.vue";
import SidebarLinkItem from "./SidebarLinkItem.vue";
import SidebarLogoutItem from "./SidebarLogoutItem.vue";
import SidebarVibbItem from "./SidebarVibbItem.vue";
import SidebarSwipeItem from "./SidebarSwipeItem.vue";

export default {
    props: ["item"],
    components: {
        SidebarLogoutItem,
        SidebarVibbItem,
        SidebarHeaderItem,
        SidebarLinkItem,
        SidebarSwipeItem
    },
    computed: {
        ...mapState(useAuthStore, ['authenticated']),
        menuComponent() {
            if (this.item.alias === "logout" && this.authenticated) return "SidebarLogoutItem";
            if (this.item.alias === "vibb") return "SidebarVibbItem";
            if (this.item.alias === "swipe") return "SidebarSwipeItem";
            if (this.item.is_header) return "SidebarHeaderItem";
            return "SidebarLinkItem";
        },
    }
};
</script>
