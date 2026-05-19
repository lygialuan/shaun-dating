<template>
    <div class="main-content-section p-10">
        <div class="text-center">
            <div class="main-content-section-header justify-center">
                <div class="main-content-section-header-title">{{$t("Unsubscribe successful.")}}</div>
            </div>
            <div class="mb-base-2">{{$t('You have successfully unsubscribed from email communications.')}}</div>
            <BaseButton :to="{ name: 'home' }">{{$t('Go back to Home')}}</BaseButton>
        </div>
    </div>
</template>

<script>
import BaseButton from '@/components/inputs/BaseButton.vue'
import {unsubscribeEmail} from '@/api/utility'

export default {
    components: { BaseButton },
    props: ['email', 'hash'],
    mounted() {
        unsubscribeEmail({
            'email': this.email,
            'hash': this.hash
        })
    }
}
</script>