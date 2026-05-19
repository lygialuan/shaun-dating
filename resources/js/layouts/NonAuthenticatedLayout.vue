<template>
    <template v-if="this.checkMobileApp">
        <router-view :key="$route.path" />
    </template>
    <template v-else>
        <HeaderNonLogin v-if="!this.isNonLoginPage && !this.checkOfflinePage" />
        <div v-if="error" class="max-w-container w-full mx-auto p-0">
            <NotFound />
        </div>
        <div v-else class="max-w-container w-full mx-auto p-0">
            <div class="flex flex-col justify-between items-center py-3"
                :class="this.isNonLoginPage || this.checkOfflinePage ? 'h-screen' : 'h-screen-non-login'">
                <div v-if="this.isNonLoginPage || this.checkOfflinePage">&nbsp;</div>
                <div class="w-full">
                    <router-view :key="$route.path" />
                </div>
                <div v-if="this.checkOfflinePage">&nbsp;</div>
                <FooterSite v-if="!this.checkOfflinePage && this.$route.name != 'home'" class="text-center px-base-2" />
            </div>
        </div>
    </template>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { checkOffline, checkMobileApp } from '@/utility/index'
import FooterSite from '@/components/layout/FooterSite.vue';
import HeaderNonLogin from '@/components/layout/HeaderNonLogin.vue';
import NotFound from '@/components/utilities/NotFound.vue';

export default {
    components: {
        FooterSite,
        HeaderNonLogin,
        NotFound
    },
    data() {
		return {
			error: false
		};
	},
    computed: {
        ...mapState(useAppStore, ['errorLayout']),
        checkOfflinePage() {
			return checkOffline()
		},
        checkMobileApp() {
			return checkMobileApp()
		},
        isNonLoginPage() {
            return ['login', 'signup', 'recover', 'two_factor_authentication'].includes(this.$route.name)
        }
    },
    watch: {
        errorLayout(error) {
			this.error = error
		},
    },
}
</script>