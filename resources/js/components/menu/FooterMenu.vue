<template>
    <ul class="main-footer-menu-list mb-4">
        <li v-for="(item, index) in menus.footer" :key="index">
            <!-- Check exist url -->
            <template v-if="item.url">
                <!-- Check Menu Item Internal Link -->
                <router-link v-if="item.type == 'internal'" :to="item.url" :target="item.is_new_tab ? '_blank': ''" class="text-sub-color dark:text-gray-300 text-sm">
                    {{item.name}}              
                </router-link>
                <!-- Check Menu Item Outbound Link -->
                <a v-else :href="item.url" :target="item.is_new_tab ? '_blank': ''" class="text-sub-color dark:text-gray-300 text-sm">
                    {{item.name}}
                </a>
            </template>
            <!-- Check Header Menu Item -->
            <template v-else>
                {{item.name}}
            </template>
        </li>
    </ul>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '../../store/app';
export default {
    computed: {
        ...mapState(useAppStore, ['menus'])
    }
}
</script>