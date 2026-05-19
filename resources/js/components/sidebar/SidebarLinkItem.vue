<template>
    <a
        :href="href"
        :target="item.is_new_tab ? '_blank' : ''"
        @click="handleClick"
        class="sidebar-user-menu-list-item-wrap flex items-center gap-4 p-2 w-full transition-colors text-main-color lg:hover:text-primary-color dark:text-gray-300 dark:lg:hover:text-dark-primary-color"
    >
        <SidebarMenuIcon :icon="item.icon" />
        <span class="flex-1 font-semibold" :class="{'!font-black': isActive}">{{ item.name }}</span>
    </a>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import SidebarMenuIcon from "./SidebarMenuIcon.vue";

export default {
    components: { SidebarMenuIcon },
    props: ["item"],
    data(){
        return{
            currentRouter: this.$router.currentRoute.value.fullPath
        }
    },
    computed: {
        ...mapState(useAppStore, ['currentUrl']),
        baseUrl() {
            return window.siteConfig.siteUrl;
        },
        href() {
            return this.item.type === "internal"
                ? this.baseUrl + this.item.url
                : this.item.url;
        },
        isActive(){
            if(this.item.url === '/explore' && this.currentRouter === '/'){
                return true;
            }
            return this.item.type === "internal"
                ? this.convertLink(this.item.url, this.item.is_core) === this.convertLink(this.currentRouter, this.item.is_core)
                : this.item.url === this.baseUrl + this.currentRouter
        }
    },
    watch: {
        '$route'(){
            this.currentRouter = this.$router.currentRoute.value.fullPath
        },
        currentUrl(){
            this.currentRouter = this.currentUrl
        }
    },
    methods: {
        handleClick(e) {
            if (!this.item.is_new_tab && this.item.type === "internal") {
                e.preventDefault();
                this.$router.push(this.item.url);
            }
        },
        convertLink(link, isCore){
            if(isCore){ // relative links
                const pathname = (new URL(link, this.baseUrl)).pathname;
                return pathname.split("/")[1]
            } else{ // absolute links
                return link.split(this.currentRouter)[1]
            }
        }
    },
};
</script>
