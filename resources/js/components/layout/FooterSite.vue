<template>
	<div class="main-footer-menu py-4 text-sub-color dark:text-gray-300 dark:border-white/10">
		<FooterMenu v-if="showFooter" />
		<span class="mt-3 text-sm">{{$t('Copyright by Sitename')}}</span>
		&middot;
		<button @click="showMenuLanguage()" class="font-bold text-primary-color dark:text-dark-primary-color text-sm">{{ currentLanguage }}</button>
	</div>
</template>
<script>
import { mapState } from 'pinia'
import { checkPopupBodyClass } from '@/utility'
import LanguageMenuModal from '@/components/modals/LanguageMenuModal.vue'
import FooterMenu from '@/components/menu/FooterMenu.vue';
import { useAuthStore } from '../../store/auth';
import { useAppStore } from '../../store/app'

export default {
	components: { FooterMenu },
	data(){
        return{
            languages: [],
			showFooter : true
        }
    },
	computed: {
		...mapState(useAuthStore, ['user', 'authenticated']),
		...mapState(useAppStore, ['config']),
		currentLanguage(){
            return this.languages[this.user.language]
        }
	},
	mounted(){
		if ((this.config.emailVerify && this.user.email_verified == 0) || (this.config.photosVerify && this.user.photos_verified == 0) || (this.config.identityVerify && this.user.identity_verified == 0)) {
			this.showFooter = false
		}
		this.languages = this.config.languages
	},
	methods: {		
		showMenuLanguage(){
			this.$dialog.open(LanguageMenuModal, {
				props:{
					class: 'language-menu-modal p-dialog-xxs',
					modal: true,
					dismissableMask: true,
					showHeader: false,
					draggable: false
				},
				onClose: () => {
					checkPopupBodyClass();
				}
			})
		}
	}
}
</script>