import { defineStore } from 'pinia'
import localData from '../utility/localData'
import { getInit } from '../api/utility';
import { reactive } from 'vue';

export const useAppStore = defineStore('app', {
    // convert to a function
    state: () => ({
        config: null,
        menus: null,
        layouts: null,
        gateways: null,
        darkmode: localData.get('darkmode','auto'),
        isMobile: false,
        errorLayout : false,
        currentUrl: null,
        baseZIndex: '1101',
        systemMode: null,
        currentRouter: null,
        openedModalCount: 0,
        screen: reactive({
            width: window.innerWidth,
            height: window.innerHeight,
            breakpoints: {
                xs: 480,
                sm: 640,
                md: 768,
                lg: 1024,
                xl: 1280,
                xxl: 1536,
            },
            xs: window.innerWidth < 480,
            sm: window.innerWidth < 640,
            md: window.innerWidth < 768,
            lg: window.innerWidth < 1024,
            xl: window.innerWidth < 1280,
            xxl: window.innerWidth < 1536,
        }),
        isOpenSidebar: false
    }),
    actions: {
        async getInit() {
            await getInit().then((response) => {
                this.config = response.data.data.config
                this.menus = response.data.data.menus
                this.layouts = response.data.data.layouts
                this.gateways = response.data.data.gateways
            });
        },
        setErrorLayout(value) {
            this.errorLayout = value
        },
        setDarkmode(darkmode){
            this.darkmode = darkmode
        },
        setCurrentUrl(url){
            this.currentUrl = url
        },
        setCurrentRouter(router){
            this.currentRouter = router
        },
        setBaseZIndex(){
            setTimeout(() => {
                this.baseZIndex = getComputedStyle(document.querySelector('.p-overlay-mask')).getPropertyValue("z-index")
            }, 100);
        },
        updateSystemMode() {
			this.systemMode = (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) ? 'dark' : 'light';
		},
        setOpenedModalCount(status = true){
            status ? this.openedModalCount++ : this.openedModalCount--
        },
        detectMobile() {
            const isMobile = /android|iPhone|iPad|iPod|webOS/i.test(navigator.userAgent);
            this.isMobile = isMobile
        },
        updateScreenSize() {
            this.screen.width = window.innerWidth;
            this.screen.height = window.innerHeight;
            this.screen.xs = this.screen.width < this.screen.breakpoints.xs;
            this.screen.sm = this.screen.width < this.screen.breakpoints.sm;
            this.screen.md = this.screen.width < this.screen.breakpoints.md;
            this.screen.lg = this.screen.width < this.screen.breakpoints.lg;
            this.screen.xl = this.screen.width < this.screen.breakpoints.xl;
            this.screen.xxl = this.screen.width < this.screen.breakpoints.xxl;
        },
        setIsOpenSidebar(status){
            this.isOpenSidebar = status
            if(status){
                document.body.classList.add('overflow-hidden');
            } else {
                document.body.classList.remove('overflow-hidden');
            }
        }
    },
    persist: false
  })