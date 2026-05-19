<template>
    <div>
        <p class="mb-base-2">{{ $t('Amount') + ' ( ' + exchangeTokenCurrency(1) }} =  {{ exchangeCurrency(1) + ')' }}</p>
        <BaseInputNumber v-model="fundAmount" @input="handleChangeFundAmount" :placeholder="$t('Value')" autofocus class="max-w-[240px] mb-base-2" />
        <p class="text-base-xs mb-base-2">{{ '~ ' + exchangeCurrency(fundAmount) }}</p>
        <div class="flex flex-wrap gap-x-6 gap-y-base-2 mb-base-2">
            <button v-for="walletPackage in walletPackagesList" :key="walletPackage.id" @click="handleClickPackage(walletPackage)" class="text-primary-color dark:text-dark-primary-color" :class="(walletPackage.id === packageId && walletPackage.amount === fundAmount) ? 'font-bold' : ''">
                {{ walletPackage.amount }}
            </button>        
        </div>
        <h5 class="font-bold mb-base-2">{{ $t('Payment method') }}</h5>
        <div v-if="gateways.length > 0">                   
            <div class="flex flex-col gap-3 mb-base-2">
                <div v-for="gateway in gateways" :key="gateway.id" class="flex items-center gap-3">
                    <BaseRadio v-model="gatewayId" :inputId="gateway.name" name="payment_method" :value="gateway.id" />
                    <label :for="gateway.name">{{ gateway.name }}</label>
                </div>
            </div>
            <BaseButton v-if="fundAmount" @click="handleClickProceed()" :loading="loadingProceed">{{ $t('Proceed to add') + ' ' + exchangeTokenCurrency(fundAmount) }}</BaseButton>
        </div>
        <div v-else>
            <p class="mb-base-2">{{ $t('Payment method is not available. Please contact admin for detail') }}</p>
            <div class="text-end">
                <BaseButton :to="{name: 'contact'}">{{$t('Contact admin')}}</BaseButton>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import { useAuthStore } from '../../store/auth'
import { useAppStore } from '../../store/app'
import { getWalletPackages, storeDeposit } from '@/api/wallet'
import BaseInputNumber from '@/components/inputs/BaseInputNumber.vue'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseInputNumber, BaseRadio, BaseButton },
    data(){
        return{
            fundAmount: null,
            packageId: null,
            gatewayId: null,
            walletPackagesList: [],
            loadingProceed: false
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['config', 'gateways'])
    },
    mounted(){
        this.getWalletPackages()
    },
    methods:{
        async getWalletPackages(){
            try {
                const response = await getWalletPackages()
                this.walletPackagesList = response
            } catch (error) {
                console.log(error)
            }
        },
        handleChangeFundAmount(){
            this.packageId = null
        },
        handleClickPackage(packageData){
            this.packageId = packageData.id
            this.fundAmount = packageData.amount
        },
        async handleClickProceed(){
            this.loadingProceed = true
            try {
                const response = await storeDeposit({
                    gateway_id: this.gatewayId,
                    amount: this.fundAmount,
                    package_id: this.packageId
                })
                window.location.href = response.url
                this.loadingProceed = false
            } catch (error) {
                this.showError(error.error)
                this.loadingProceed = false
            }
        }
    }
}
</script>