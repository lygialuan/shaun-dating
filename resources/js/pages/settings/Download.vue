<template>
    <div>
        <h3 class="text-main-color text-base-lg font-extrabold mb-3 dark:text-white">{{$t('Download your Data')}}</h3>
		<Loading v-if="loadingStatus" />
		<div v-else>
			<div v-if="canRequest">
				<p class="mb-3">{{$t("Your data will be available for download in a few days from the requested date. You will receive an email when it is ready to download.")}}</p>
				<template v-if="downloadLink != ''">
					<a class="font-bold mb-3" :href="downloadLink">{{$t('Download your data')}}</a>
					<p>{{ downloadDate }}</p>
				</template>
				<BaseButton @click="requestDownloadGPDR()" fluid>{{ $t('Request Download') }}</BaseButton>
			</div>
			<div v-else class="mb-3">
				{{$t('Your data is being processed. Please wait until it is completed.')}}
			</div>
		</div>
    </div>
</template>

<script>
import { getDownload, requestDownload } from '@/api/user';
import Loading from '@/components/utilities/Loading.vue';
import PasswordModal from '@/components/modals/PasswordModal.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { Loading, BaseButton },
    data(){
		return {
			downloadLink: null,
			canRequest: null,
			downloadDate: null,
			loadingStatus: true
		}
	},
	mounted(){
        this.getDownloadStatus()
    },
	methods: {
		async getDownloadStatus() {
            try {
				const response = await getDownload()
                this.downloadLink = response.downloadLink
                this.canRequest = response.canRequest
				this.downloadDate = response.downloadDate
				this.loadingStatus = false
			} catch (error) {
                this.loadingStatus = false
			}
        },
		async requestDownloadGPDR() {
			const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false,
				},
				emits: {
                    onConfirm: async (data) => {
                        if (data.password) {
                            try {
								await requestDownload({
									password: data.password
								})
								this.showSuccess(this.$t('The file is being processed. We will send you a notification when it is done.'))
								this.canRequest = false
								passwordDialog.close()
							} catch (error) {
								this.showError(error.error.detail.user)
								passwordDialog.close()
							}
                        }
                    }
                }
			})
		}
	}
}
</script>