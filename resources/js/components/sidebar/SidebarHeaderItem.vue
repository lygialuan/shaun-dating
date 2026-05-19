<template>
    <button
        class="sidebar-user-menu-list-item-wrap flex items-center text-start gap-4 p-2 w-full transition-colors text-main-color lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color"
        @click="handleToggleSubMenu"
    >
		<SidebarMenuIcon :icon="item.icon" />
		<span class="flex-1 font-semibold">{{ item.name }}</span>
        <BaseIcon v-if="item.childs.length" name="more_vertical" class="sidebar-main-menu-item-icon-more"/>
    </button>
    <OverlayPanel v-if="item.childs.length" :ref="`subMenuRef_${key}`" class="sub-menu-overlay">
        <SidebarMenuDropdown
            :items="item.childs"
        />
    </OverlayPanel>
</template>

<script>
import { uuidv4 } from '@/utility';
import SidebarMenuIcon from "./SidebarMenuIcon.vue";
import BaseIcon from '@/components/icons/BaseIcon.vue'
import SidebarMenuDropdown from "./SidebarMenuDropdown.vue";
import OverlayPanel from 'primevue/overlaypanel';

export default {
    components: { SidebarMenuIcon, BaseIcon, SidebarMenuDropdown, OverlayPanel },
    props: ["item"],
    data(){
        return{
            key: uuidv4()
        }
    },
    watch:{
        '$route'(){
            this.handleHideSubMenu()
        }
    },
    mounted() {
        window.addEventListener('scroll', this.handleHideSubMenu);
    },
    unmounted() {
        window.removeEventListener('scroll', this.handleHideSubMenu);
    },
    methods:{
        handleToggleSubMenu(event){
            const overlayPanel = this.$refs[`subMenuRef_${this.key}`];
            if (overlayPanel) {
                overlayPanel.toggle(event);
            }
        },
        handleHideSubMenu() {
            const overlayPanel = this.$refs[`subMenuRef_${this.key}`];
            if (overlayPanel) {
                overlayPanel.hide();
            }
        }
    }
};
</script>
