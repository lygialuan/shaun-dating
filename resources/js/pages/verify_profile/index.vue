<template>
    <div class="main-content-section bg-white rounded-none md:rounded-base-lg p-5 mb-base-2 dark:bg-dark-form-base dark:border-dark-form-base">
        <div class="flex flex-wrap items-center justify-between gap-1 mb-base-2">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white">{{$t('Verify Your Edentity')}}</h3>
        </div>
        <p class="mb-base-2 dark:text-dark-text-base-gray">{{$t("To keep your account secure, we need to verify your identity. Please follow the steps below to upload a valid photo ID. This helps us confirm your information and protect your account from unauthorized access.")}}</p>
        <div class="mb-base-2">
            <h4 class="text-base font-bold mb-base-2">{{ $t('How to take the photos') }}</h4>
            <ul class="list-disc ps-5 dark:text-dark-text-base-gray">
                <li>{{ $t('Take the photos in a room with enough light') }}</li>
                <li>{{ $t('Select the highest quality setting on your device') }}</li>
                <li>{{ $t("Have your driver's license, ID card or passport on hand") }}</li>
                <li>{{ $t('Make sure that your whole document is in the frame, as well as your face - nothing can be censored') }}</li>
            </ul>
        </div>
        <div>
            <BaseInputFile ref="baseInputFile" @upload-file="uploadVerificationDocuments" multiple accept="*/*" class="mb-2 bg-[#F5F5F5] text-black border-[#D9D9D9] p-2 dark:bg-dark-body dark:text-white">{{$t('Upload Verification Documents')}}</BaseInputFile>
            <div v-if="myVerificationDocuments.length > 0">
                <div v-for="(myVerificationDocument, index) in myVerificationDocuments" :key="index">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <a class="truncate block" :href="myVerificationDocument.file_url" target="_blank">{{myVerificationDocument.name}}</a>
                    </div>
                </div>
            </div>
            <div v-if="uploadedVerificationDocuments.length > 0">
                <div v-for="(uploadedVerificationDocument, index) in uploadedVerificationDocuments" :key="index">
                    <div class="flex items-center gap-base-2 mb-base-2">
                        <a class="truncate block" :href="uploadedVerificationDocument.file_url" target="_blank">{{uploadedVerificationDocument.name}}</a>
                        <button @click="removeDocument(uploadedVerificationDocument.id)">
                            <BaseIcon name="close" class="text-base-red" size="20" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex gap-base-2 flex-col" v-if="this.config.identityVerify && !this.user.is_verify" >
            <BaseButton @click="storeDocuments()" :disabled="uploadedVerificationDocuments.length === 0">{{$t('Submit')}}</BaseButton>
            <div class="mt-base-2 text-center">{{$t('Switch account?')}}&nbsp;<button class="text-primary-color dark:text-dark-primary-color font-bold" @click="logout()">{{$t('Logout')}}</button></div>
        </div>
        <div class="flex gap-base-2" v-else>
            <BaseButton @click="storeDocuments()" :disabled="uploadedVerificationDocuments.length === 0">{{$t('Submit')}}</BaseButton>
            <BaseButton type="outlined" :to="{name: 'home'}">{{$t('Cancel')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { getVerificationDocuments, uploadVerificationDocuments, deleteVerificationDocuments, storeVerificationDocuments } from '@/api/verify'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';
import BaseInputFile from '@/components/inputs/BaseInputFile.vue'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';

export default {
    components: { BaseButton, BaseIcon, BaseInputFile },
    data() {
        return {
            myVerificationDocuments: [],
            uploadedVerificationDocuments: []
        }
    },
    mounted() {
        if((this.config.userVerifyEnable && !this.user.is_verify) || (this.config.identityVerify && !this.user.identity_verified)){
            this.getMyVerificationDocuments()
        }else{
            this.$router.push({ name: 'home' })
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useAuthStore, ['user'])
    },
    methods: {
        async getMyVerificationDocuments(){
            try {
                const response = await getVerificationDocuments()
                this.myVerificationDocuments = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async uploadVerificationDocuments(files){
			this.startUploadImages(files.target.files)
		},
        async startUploadImages(uploadedFiles, clipboard){
			if (typeof clipboard === 'undefined') {
                clipboard = false
            }
			for( var i = 0; i < uploadedFiles.length; i++ ){
				if (clipboard) {
					// Skip content if not image
					if (uploadedFiles[i].type.indexOf("image") == -1) continue;
				}
				var checkUpload = true
				if (! clipboard) {
					checkUpload = this.checkUploadedData(uploadedFiles[i], 'user_verify')
				}
				if(checkUpload){
					let formData = new FormData()
                    var blob = uploadedFiles[i]
                    if (clipboard) {
                        blob = uploadedFiles[i].getAsFile();
                    }
                    formData.append('file', blob)
					try {
						const response = await uploadVerificationDocuments(formData);
						this.uploadedVerificationDocuments.push(response);
					} catch (error) {
						this.showError(error.error)
					}	
				}
			}			
		},
        async removeDocument(documentId){
			try {
				await deleteVerificationDocuments({
					id: documentId
				});
				this.uploadedVerificationDocuments = this.uploadedVerificationDocuments.filter(document => document.id !== documentId);
			} catch (error) {
				this.showError(error.error)
			}
		},
        async storeDocuments(){
            try {
                await storeVerificationDocuments({
                    files: this.uploadedVerificationDocuments.map(x => x.id)
                })
                this.myVerificationDocuments = [...this.myVerificationDocuments, ...this.uploadedVerificationDocuments]
                this.uploadedVerificationDocuments = []
                this.showSuccess(this.$t('Your request has been submitted.'))
                this.$refs.baseInputFile.clearSelectedFile()
            } catch (error) {
                this.showError(error.error)
            }
        },
        async logout() {
            try {
                await useAuthStore().logout();
                window.location.href = `${window.siteConfig.siteUrl}/login`;
            } catch (error) {
                this.showError(error.error)
            }
        }
    },
}
</script>