<template>
    <div class="loading-wrapper fixed inset-0 bg-main-bg flex justify-center items-center p-10 z-[1000] dark:bg-dark-web-wash">
        <img class="loading-img max-w-[250px]" :src="darkmode === 'on' ? siteLogoDarkMode : siteLogo" alt="">
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAppStore } from '../store/app'

export default {
    computed: {
		...mapState(useAppStore, ['darkmode'])
	},
    data(){
        return {
            'siteLogo' : window.siteConfig.siteLogo,
            'siteLogoDarkMode' : window.siteConfig.siteLogoDarkMode,
        }
    }
}
</script>