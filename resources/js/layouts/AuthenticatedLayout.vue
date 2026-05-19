<template>
    <template v-if="noSidebar">
        <div id="mainContent" class="flex flex-col justify-between items-center bg-main-bg px-base-2 py-5 min-h-screen w-full dark:bg-dark-body dark:text-white h-screen">
            <div>&nbsp;</div>
            <div :class="!pagePhotos ? 'max-w-md w-full' : 'max-w-2xl w-full'">
                <div class="text-center mb-10">
                    <Logo />
                </div>
                <router-view :key="$route.path" />
            </div>
            <FooterSite class="text-center" />
        </div>
    </template>
    <div v-else class="flex items-stretch w-full pt-16 pb-16 lg:py-0">
        <HeaderMobile />
        <Sidebar />
        <div id="mainContent" class="main-content w-full lg:w-main-content">
            <HeaderSite v-if="!noHeader" />
            <div v-if="error" class="max-w-container w-full mx-auto p-0 md:px-6 md:py-base-2">
                <NotFound />
            </div>
            <template v-else>
                <Container v-if="layouts.header.center.length > 0" class="px-0 md:px-6 py-0">
                    <div v-for="(dataHeader, index) in layouts.header.center" :key="index">
                        <component :is="loadComponentData(dataHeader)" :data="dataHeader"></component>
                    </div>
                </Container>
                <Container class="p-0 md:px-6 md:py-base-2">
                    <router-view :key="$route.path" />
                </Container>
                <Container v-if="layouts.footer.center.length > 0" class="px-0 pt-0 pb-0 md:px-6 md:pb-6">
                    <div v-for="(dataFooter, index) in layouts.footer.center" :key="index">
                        <component :is="loadComponentData(dataFooter)" :data="dataFooter"></component>
                    </div>
                </Container>
            </template>
        </div>
        <FooterMobile />
    </div>
    <SwitchPagePopover/>
    <ChatBubble />
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { defineAsyncComponent } from "vue";
import FooterSite from '@/components/layout/FooterSite.vue';
import Sidebar from '@/components/layout/Sidebar.vue'
import HeaderSite from '@/components/layout/HeaderSite.vue'
import FooterMobile from '@/components/layout/FooterMobile.vue';
import HeaderMobile from '@/components/layout/HeaderMobile.vue';
import Container from '@/components/article/Container.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import Logo from '@/components/utilities/Logo.vue';
import SwitchPagePopover from '@/components/popover/SwitchPagePopover.vue';
import ChatBubble from '@/components/layout/ChatBubble.vue';
import NotFound from '@/components/utilities/NotFound.vue';

export default {
    components: {
        FooterSite,
        Sidebar,
		HeaderSite,
		FooterMobile,
        HeaderMobile,
        Container,
        BaseButton,
        Logo,
		SwitchPagePopover,
		ChatBubble,
        NotFound
    },
    data() {
		return {
			error: false
		};
	},
    computed: {
        ...mapState(useAppStore, ['layouts', 'errorLayout']),
        noSidebar() {
            return ['first_login', 'email_confirm', 'phone_confirm', 'photos_confirm', 'identity_confirm'].includes(this.$route.name)
        },
        noHeader() {
            return ['vibb'].includes(this.$route.name)
        },
        pagePhotos() {
            return ['photos_confirm'].includes(this.$route.name)
        },
    },
    watch: {
        errorLayout(error) {
			this.error = error
		},
    },
    methods: {
        loadComponentData(data) {
			if (data.type == 'component') {
				return defineAsyncComponent(() => import(`./components/widgets/${data.package}/${data.component}.vue`))
			} else {
				return defineAsyncComponent(() => import(`./pages/${this.$route.name}/${data.component}.vue`))
			}
		}
    }
}
</script>