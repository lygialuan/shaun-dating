<template>
    <div v-if="config != null && config.cookieEnable && config.cookieLink != '' && !cookieHide"
		class="cookies-warning bg-white p-base-7 fixed bottom-0 inset-x-0 z-[1000] dark:bg-slate-800 dark:text-white">
		<h5 class="text-base-lg font-extrabold mb-base-2">{{ $t('Cookies on') }} {{ config.siteName }}</h5>
		<p>{{ $t('This site uses cookies to store your information on your computer.') }}</p>
		<div class="flex gap-base-2 mt-base-2">
			<BaseButton :href="config.cookieLink" target="_blank">{{ $t('Read more') }}</BaseButton>
			<BaseButton @click=acceptCookies>{{ $t('Accept') }}</BaseButton>
		</div>
	</div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import localData from '@/utility/localData';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton },
    data(){
        return {
            cookieHide: localData.get('cookie_hide', false)
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
    },
    methods: {
        acceptCookies() {
			this.cookieHide = true;
			localData.set('cookie_hide', true)
		}
    },
};
</script>