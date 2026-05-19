<template>
    <div v-if="type === 'primary'" 
        class="main-content-menu flex whitespace-nowrap overflow-x-auto"
        :class="`main-content-menu-${type}`"
    >
        <template v-if="router">
            <router-link v-for="(menu, index) in visibleMenus" :key="index" 
                class="main-content-menu-item text-center"
                :class="classItem"
                :to="menu.tab"
                :ref="$route.name === menu.tab.name ? 'activeMenuItem' : null"
            >
                <div class="main-content-menu-item-wrap inline-flex items-center leading-5 px-3 py-2 border-b-2">
                    {{ `${menu.name}${menu.badge ? ' - ' + menu.badge : ''}` }}
                </div>
            </router-link>
        </template>
        <template v-else>
            <div v-for="(menu, index) in visibleMenus" :key="index" 
                class="main-content-menu-item text-center cursor-pointer"
                :class="[
                    classItem,
                    { 'active font-bold text-primary-color dark:text-dark-primary-color': menu.isActive }
                ]"
                @click="handleClickTabItem(menu.tab)"
                :ref="menu.isActive ? 'activeMenuItem' : null"
            >
                <div
                    class="main-content-menu-item-wrap inline-flex items-center leading-5 px-3 py-2 border-b-2"
                    :class="menu.isActive ? 'border-primary-color dark:border-dark-primary-color' : 'border-transparent'"
                >
                    {{ `${menu.name}${menu.badge ? ' - ' + menu.badge : ''}` }}
                </div>
            </div>
        </template>
    </div>
    <div v-else-if="type === 'secondary'" 
        class="main-content-menu relative group"
        :class="`main-content-menu-${type}`"
    >
        <div ref="tabsPrimaryRef" class="main-content-menu-tab flex px-base-2 gap-base-2 overflow-x-auto whitespace-nowrap scrollbar-hidden md:px-0">
            <template v-if="router">
                <router-link 
                    v-for="(menu, index) in visibleMenus" 
                    :key="index" 
                    class="main-content-menu-tab-item flex flex-1 items-center justify-center text-center rounded-base-lg gap-base-2 px-base-2 py-2 text-base-sm cursor-pointer md:py-base-2 bg-white text-main-color dark:bg-slate-800 dark:text-white" 
                    :class="classItem"
                    :to="menu.tab"
                    :ref="$route.name === menu.tab.name ? 'activeMenuItem' : null"
                >
                    <BaseIcon v-if="menu.icon" :name="menu.icon" />
                    {{ menu.name }}
                    <span v-if="menu.badge" :class="`inline-flex justify-center items-center leading-none p-1 h-6 min-w-6 text-primary-color border rounded-full dark:text-dark-primary-color ${ menu.isActive ? 'bg-white border-white' : 'bg-transparent border-primary-color dark:border-dark-primary-color'}`">{{ menu.badge }}</span>
                </router-link>
            </template>
            <template v-else>
                <div 
                    v-for="(menu, index) in visibleMenus" 
                    :key="index" 
                    class="main-content-menu-tab-item flex flex-1 items-center justify-center text-center rounded-base-lg gap-base-2 px-base-2 py-2 text-base-sm cursor-pointer md:py-base-2" 
                    :class="[
                        classItem,
                        menu.isActive ? 'active bg-primary-color text-white dark:bg-dark-form-base' : 'bg-white dark:bg-dark-form-surface'
                    ]"
                    @click="handleClickTabItem(menu.tab)"
                    :ref="menu.isActive ? 'activeMenuItem' : null"
                >
                    <BaseIcon v-if="menu.icon" :name="menu.icon" />
                    {{ menu.name }}
                    <span v-if="menu.badge" :class="`inline-flex justify-center items-center leading-none p-1 h-6 min-w-6 text-primary-color border rounded-full dark:text-dark-primary-color ${ menu.isActive ? 'bg-white border-white' : 'bg-transparent border-primary-color dark:border-dark-primary-color'}`">{{ menu.badge }}</span>
                </div>
            </template>
        </div>
        <div ref="xBar" @mousedown="onXBarMouseDown" class="absolute start-0 bottom-0 h-base-1 bg-scrollbar-thumb rounded-lg cursor-pointer dark:bg-scrollbar-thumb-dark" :class="[isMobile ? 'opacity-0' : 'transition-opacity opacity-0 group-hover:opacity-100', isXBarClicked ? 'opacity-100' : '']"></div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    props: {
        menus: {
            type: Array,
            default: () => []
        },
        type: {
            type: String,
            default: 'primary'
        },
        router: {
            type: Boolean,
            default: false
        },
        classItem: {
            type: String,
            default: ''
        }
    },
    components: {
		BaseIcon
	},
    data(){
        return {
            isXBarClicked: false,
        }
    },
    computed: {
        ...mapState(useAppStore, ['isMobile']),
        ...mapState(useAuthStore, ['user']),
        visibleMenus() {
            return this.menus.filter(menu => menu.isShow || typeof(menu.isShow) == 'undefined');
        }
    },
    mounted() {
        this.scrollToActiveMenuItem();
        if (this.$refs.tabsPrimaryRef?.offsetParent) {
            this.moveBar()
        }
        this.$refs.tabsPrimaryRef?.addEventListener('scroll', this.onScroll)
    },
    unmounted(){
        this.$refs.tabsPrimaryRef?.removeEventListener('scroll', this.onScroll)
    },
    methods: {
        handleClickTabItem(tab){
            this.$emit('select', tab);
        },
        scrollToActiveMenuItem() {
            const activeMenuItem = this.router ? this.$refs.activeMenuItem?.[0]?.$el : this.$refs.activeMenuItem?.[0];
            if (activeMenuItem) {
                activeMenuItem.scrollIntoView({ block: 'nearest', inline: 'center' });
            }
        },
        requestAnimationFrame(f) {
            let frame = window.requestAnimationFrame || this.timeoutFrame;

            return frame(f);
        },
        onMouseMoveForXBar(e) {
            let deltaX = e.pageX - this.lastPageX;

            this.lastPageX = e.pageX;

            this.frame = this.requestAnimationFrame(() => {
                this.$refs.tabsPrimaryRef.scrollLeft += deltaX / this.scrollXRatio;
            });
        },
        onDocumentMouseMove(e) {
            if (this.isXBarClicked) {
                this.onMouseMoveForXBar(e);
            }
        },
        onScroll () {
			this.moveBar()
		},
        moveBar(){
            let totalWidth = this.$refs.tabsPrimaryRef.scrollWidth;
            let ownWidth = this.$refs.tabsPrimaryRef.clientWidth;

            this.scrollXRatio = ownWidth / totalWidth;
            if(this.$refs.xBar){
				this.$refs.xBar.style.cssText = 'height: 0';
				if(this.scrollXRatio < 1){
					const widthPercent = Math.max(this.scrollXRatio * 100, 10) + '%';
                    let positionPercent;
                    if (this.user.rtl) {
                        const scrollLeft = Math.abs(this.$refs.tabsPrimaryRef.scrollLeft);
                        positionPercent = (scrollLeft / totalWidth) * 100 + '%';
                        this.$refs.xBar.style.cssText = `width:${widthPercent}; right:${positionPercent}; left:auto;`;
                    } else {
                        positionPercent = (this.$refs.tabsPrimaryRef.scrollLeft / totalWidth) * 100 + '%';
                        this.$refs.xBar.style.cssText = `width:${widthPercent}; left:${positionPercent}; right:auto;`;
                    }
				}
			}

		},
        bindDocumentMouseListeners() {
            if (!this.documentMouseMoveListener) {
                this.documentMouseMoveListener = (e) => {
                    this.onDocumentMouseMove(e);
                };

                document.addEventListener('mousemove', this.documentMouseMoveListener);
            }

            if (!this.documentMouseUpListener) {
                this.documentMouseUpListener = (e) => {
                    this.onDocumentMouseUp(e);
                };

                document.addEventListener('mouseup', this.documentMouseUpListener);
            }
        },
        unbindDocumentMouseListeners() {
            if (this.documentMouseMoveListener) {
                document.removeEventListener('mousemove', this.documentMouseMoveListener);
                this.documentMouseMoveListener = null;
            }

            if (this.documentMouseUpListener) {
                document.removeEventListener('mouseup', this.documentMouseUpListener);
                this.documentMouseUpListener = null;
            }
        },
        onXBarMouseDown(e) {
            this.isXBarClicked = true;
            this.$refs.xBar.focus();
            this.lastPageX = e.pageX;

            this.bindDocumentMouseListeners();
            e.preventDefault();
        },
        onDocumentMouseUp() {
            this.unbindDocumentMouseListeners();
            this.isXBarClicked = false;
        },
    },
    emits: ['select']
}
</script>