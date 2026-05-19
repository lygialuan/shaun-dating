<template>
    <div>{{ $t('Featured your page with') + ' ' + packageInfo.description }}</div>
    <div>{{ $t('Your current balance') + ': ' + user.wallet_balance + ' ' + config.wallet.tokenName}} </div>
    <div>{{ $t('Don’t forget to deposit to update your balance to make sure that subscription is active') }}</div>
    <BaseButton class="mt-base-2 w-full" @click="handleClickFeaturePage(packageInfo.id)">{{ $t('Featured now!') }}</BaseButton>
</template>
<script>
import { mapState} from 'pinia'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import { storeFeaturePage } from '@/api/page'
import { checkPopupBodyClass } from '@/utility/index'
import Constant from '@/utility/constant'
import BaseButton from '@/components/inputs/BaseButton.vue'
import PasswordModal from '@/components/modals/PasswordModal.vue'

export default {
    components : { BaseButton },
    inject: ['dialogRef'],
    data(){
        return{
            packageInfo: this.dialogRef.data.packageInfo
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    methods:{
        async handleClickFeaturePage(){
            const passwordDialog = this.$dialog.open(PasswordModal, {
				props: {
					header: this.$t('Enter Password'),
					class: 'password-modal',
					modal: true,
					dismissableMask: true,
					draggable: false
				},
                emits: {
					onConfirm: async (data) => {
						if (data.password) {
							try {
                                await storeFeaturePage({
                                    package_id: this.packageInfo.id,
                                    password: data.password
                                })
                                this.showSuccess(this.$t('Feature Successfully.'))
                                this.dialogRef.close();
                                passwordDialog.close()
                                checkPopupBodyClass();
                                window.location.href = window.siteConfig.siteUrl + '/' + Constant.MENTION + this.user.user_name
                            } catch (error) {
                                this.showError(error.error)
                                passwordDialog.close()
                                checkPopupBodyClass();
                            }
						}
					}
				}
			})
        }
    }
}
</script>