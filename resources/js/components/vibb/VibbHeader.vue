<template>
    <div class="vibb-header absolute top-3 start-4 end-4 flex flex-wrap items-center justify-between gap-2 text-white z-[1001] dark:text-white">
        <div class="flex-1">
            <button @click="handleBack">
                <BaseIcon name="caret_left" size="32"/>
            </button>
        </div>
        <TabsMenu :menus="vibbMenus" @select="handleChangeTab" class="vibb-menu flex-3 flex justify-center text-white" />
        <div class="flex-1 text-end">
            <template v-if="showUploadVideo">
                <button v-if="screen.md" @click="createVibb()">
                    <BaseIcon name="plus_circle_fill" size="32"/>
                </button>
                <BaseButton v-else @click="createVibb()">{{ $t("Create new vibb") }}</BaseButton>
            </template>
        </div>
    </div>
</template>

<script>
import { mapState } from "pinia";
import { useAppStore } from "@/store/app";
import BaseButton from "@/components/inputs/BaseButton.vue";
import BaseIcon from '@/components/icons/BaseIcon.vue'
import TabsMenu from '@/components/menu/TabsMenu.vue';

export default {
    components: {
        BaseButton,
        BaseIcon,
        TabsMenu
    },
    props: {
        type: {
            type: String,
            default: ''
        }
    },
    data(){
        return {
            showUploadVideo: false
        }
    },
    computed: {
        ...mapState(useAppStore, ['screen', 'config']),
        vibbMenus(){
			return [
				{ name: this.$t('For you'), isShow: true, isActive: this.type === '', tab: ''},
				{ name: this.$t('Following'), isShow: true, isActive: this.type === 'following', tab: 'following'}
			]
		},
    },
    mounted(){
        this.showUploadVideo = this.config.ffmegEnable;
    },
    methods: {
        handleBack(){
            this.$emit('back')
        },
        handleChangeTab(tab){
            this.$emit('change_tab', tab)
        }
    }
}
</script>