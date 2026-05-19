<template>
	<a href="javascript:void(0)" @click="goToHome">
        <img :class="className" :src="logoSrc" />
    </a>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { useActionStore } from '@/store/action'

export default {
	props: {
		className: {
			type: String,
			default: 'max-h-10'
		}
	},
    data(){
        return {
            headerMobileLogo : window.siteConfig.headerMobileLogo,
            headerMobileLogoDarkMode : window.siteConfig.headerMobileLogoDarkMode,
        }
    },
    computed: {
		...mapState(useAppStore, ['darkmode', 'systemMode']),
        logoSrc(){
            if (this.darkmode === 'on' || (this.darkmode === 'auto' && this.systemMode === 'dark')) {
                return this.headerMobileLogoDarkMode;
            }
            return this.headerMobileLogo;
        }
	},
    methods: {
        ...mapActions(useActionStore, ['doSamePage']),
        goToHome(){
            if (this.$route.name == 'home') {
                this.doSamePage({status: true})
            } else {
                this.$router.push({ name: 'home' })
            }
        }
    }
}
</script>
