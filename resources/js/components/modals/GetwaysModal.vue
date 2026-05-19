<template>
    <p>{{ $t('Items') + ': ' + plan.description }}</p>
    <div class="flex flex-col p-4 space-y-4">
        <h3 class="text-lg font-semibold">
            {{ $t('Select a payment method') }}
        </h3>
        <div v-for="gateway in plan.gateways" :key="gateway.id" @click="selected = gateway">
            <div class="flex flex-row items-center cursor-pointer">
                <BaseRadio v-model="selected" :inputId="'gateway-' + gateway.id" name="gateway" :value="gateway"/>
                <label :for="'gateway-' + gateway.id" class="ml-2 cursor-pointer">
                    {{ gateway.type === 'wallet' ? gateway.name.replace(':wallet', exchangeTokenCurrency(user.wallet_balance)) : gateway.name}}
                </label>
            </div>
            <div v-if="gateway.type === 'wallet'" class="text-xs ml-7"><router-link :to="{name: 'wallet'}" class="sidebar-user-menu-link text-blue-500 underline underline-offset-2">{{ $t('Reload your balance') }}</router-link></div>
        </div>
        <BaseButton :disabled="!selected" @click="subscribeNow">
            {{ $t('Subscribe now') }}
        </BaseButton>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseRadio, BaseButton },
    inject: ['dialogRef'],
    data() {
        return {
            selected: null,
            plan: this.dialogRef.data.plan
        }
    },
    computed:{
        ...mapState(useAuthStore, ['user']),
    },
    methods: {
        subscribeNow() {
            if (!this.selected) return
            this.dialogRef.close({selected: this.selected})
        }
    }
}
</script>