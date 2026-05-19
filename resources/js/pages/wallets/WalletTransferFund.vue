<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{ $t('Submit a payment request') }}</h3>
        </div>
        <form @submit.prevent="submitPaymentRequest">
            <h5 class="font-bold mb-base-2">{{ $t('Amount') }}</h5>
            <div class="flex flex-col gap-3 mb-base-2">                             
                <div class="flex flex-wrap gap-3">
                    <BaseRadio v-model="amountType" inputId="custom" name="amountType" value="custom" class="pt-2" />
                    <div class="flex-1 flex flex-wrap gap-x-3 gap-y-1">
                        <BaseInputNumber v-model="customWithdrawAmount" :placeholder="$t('Value')" class="max-w-[240px]" />
                        <label for="custom" class="pt-base-1">{{ config.wallet.tokenName + ' ~ ' + exchangeCurrency(customWithdrawAmount) }}</label>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <BaseRadio v-model="amountType" inputId="all" name="amountType" value="all" />
                    <label for="all">{{ $t('All current balance') + ' (' + exchangeTokenCurrency(user.wallet_balance) + ' ~ ' + exchangeCurrency(user.wallet_balance) + ')' }}</label>
                </div>
            </div>
            <h5 class="font-bold mb-base-2">{{ $t('Payment method') }}</h5>
            <div class="flex flex-col gap-3 mb-base-2">                             
                <div class="flex items-center gap-3" v-if="config.wallet.fundTransferPaypalEnable" >
                    <BaseRadio v-model="paymentMethodType" inputId="paypal" name="payment_method" value="paypal" />
                    <label for="paypal">{{ $t('Paypal - Minimun') + ' ' + config.wallet.fundTransferPaypalMinimum + ' ' + config.currency.code + ' - ' +  $t('Transfer fee is') + ' ' + amountFee(config.wallet.fundTransferPaypalFee) }} ({{config.wallet.fundTransferPaypalFee}}%)</label>
                </div>
                <div class="flex items-center gap-3" v-if="config.wallet.fundTransferBankEnable">
                    <BaseRadio v-model="paymentMethodType" inputId="bank" name="payment_method" value="bank" />
                    <label for="bank">{{ $t('Bank Transfer - Minimum') + ' ' + config.wallet.fundTransferBankMinimum + ' ' + config.currency.code + ' - ' +  $t('Transfer fee is') + ' ' + amountFee(config.wallet.fundTransferBankFee) }} ({{config.wallet.fundTransferBankFee}}%)</label>
                </div>
            </div>
            <div v-if="paymentMethodType === 'bank'" class="space-y-base-2 mb-base-2">
                <p>{{this.config.siteName}} {{ $t('cannot be held responsible for delays, extra costs or financial loss that arises from being provided incorrect account information, so please ensure that you double check the details with your financial institution prior to submitting a request for a Bank Wire Transfer. Please fill in your bank details as complete as possible, including') }}:</p>
                <ul class="list-disc ps-5">
                    <li>{{ $t('Your Address') }}</li>
                    <li>{{ $t("Your bank's Address") }}</li>
                    <li>{{ $t('The name of the account holder') }}</li>
                    <li>{{ $t('Your account number and if possible IBAN') }}</li>
                    <li>{{ $t("Your bank's SWIFT code") }}</li>
                </ul>
                <BaseTextarea v-model="bankDetail" :placeholder="$t('Bank Detail')" :rows="5" autoResize />
            </div>
            <div v-else-if="paymentMethodType === 'paypal'" class="space-y-base-2 mb-base-2">
                <BaseInputText v-model="paypalEmail" :placeholder="$t('Paypal Email')" class="max-w-[240px]" />
                <BaseInputText v-model="confirmPaypalEmail" :placeholder="$t('Re-type Paypal email')" class="max-w-[240px]" />
            </div>
            <BaseButton :loading="loadingRequest">{{$t('Submit Request')}}</BaseButton>
        </form>
    </div>
</template>
<script>
import { mapState, mapActions } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '../../store/app'
import BaseButton from '@/components/inputs/BaseButton.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseTextarea from '@/components/inputs/BaseTextarea.vue'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import { storeWithdraw, getWithdrawalInfo } from '@/api/wallet'
import { useUtilitiesStore } from '@/store/utilities'

export default {
    components: { BaseButton, BaseRadio, BaseInputNumber, BaseTextarea, BaseInputText },
    data(){
        return{
            amountType: 'custom',
            customWithdrawAmount: null,
            paymentMethodType: null,
            withdrawAmount: null,
            paypalEmail: null,
            confirmPaypalEmail: null,
            bankDetail: null,
            loadingRequest: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config'])
    },
    mounted() {
        if (! this.config.wallet.enable) 
        {
            return this.$router.push({ 'name': 'permission' })
        }
        if (! this.config.wallet.fundTransferEnable) {
            return this.$router.push({ 'name': 'permission' })
        }
        if (this.config.wallet.fundTransferVerifyEnable && ! this.user.is_verify) {
            return this.$router.push({ 'name': 'permission' })
        }
        let permission = 'wallet.transfer_fund'
        if (! window._.has(this.user.permissions, permission) || ! this.user.permissions[permission]) {
            return this.$router.push({ 'name': 'permission' })
        }
        this.handleGetWithdrawalInfo()
    },
    watch:{
        customWithdrawAmount(){
            if(this.amountType === 'custom'){
                this.withdrawAmount = this.customWithdrawAmount
            }
        },
        amountType(){
            if(this.amountType === 'custom'){
                this.withdrawAmount = this.customWithdrawAmount
            } else if(this.amountType === 'all'){
                this.withdrawAmount = this.formattedAmount(this.user.wallet_balance)
            }
        }
    },
    methods:{
        ...mapActions(useUtilitiesStore, ['pingNotification']),
        async submitPaymentRequest(){
            try {
                if (! this.paymentMethodType) {
                    this.showError(this.$t('Please select payment method.'))
                    return
                }
                this.loadingRequest = true

                const data = { 
					type: this.paymentMethodType,
                    amount: this.formattedAmount(this.withdrawAmount)
				}
                if(this.paymentMethodType === 'bank'){
                    data.bank_account = this.bankDetail
                } else if (this.paymentMethodType === 'paypal'){
                    data.bank_account = this.paypalEmail,
                    data.bank_account_confirmed = this.confirmPaypalEmail
                }

                await storeWithdraw(data)
                this.pingNotification()
                this.customWithdrawAmount = null
                this.withdrawAmount = null
                this.showSuccess(this.$t('Request submitted successfully'))
            } catch (error) {
                this.showError(error.error)
            } finally {
                this.loadingRequest = false
            }
        },
        amountFee(fee){
            return this.exchangeCurrency(fee * this.withdrawAmount / 100)
        },
        async handleGetWithdrawalInfo(){
            try {
                const response = await getWithdrawalInfo()
                this.bankDetail = response.bank_detail
                this.paypalEmail = response.paypal_email
                this.confirmPaypalEmail = response.paypal_email
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>