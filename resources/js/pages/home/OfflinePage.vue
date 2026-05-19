<template>
    <div class="w-full px-base-2">
        <div class="text-center mb-10">
            <Logo />
        </div>
        <WidgetContainer class="max-w-md mx-auto rounded-base-lg">
            <template v-slot:title> {{ $t("Site is under maintenance") }} </template>
            <template v-slot:body>
                <div class="mb-3" v-html="config.offlineMessage"></div>
                <div class="text-center">
                    <BaseButton @click="handleOpenAccessCodeModal">{{$t('Access Site')}}</BaseButton>
                </div>
            </template>
        </WidgetContainer>
    </div>
</template>

<script>
import { mapState } from 'pinia';
import { useAppStore } from '@/store/app';
import { useActionStore } from '@/store/action'
import Logo from '@/components/utilities/Logo.vue';
import BaseButton from '@/components/inputs/BaseButton.vue'
import AccessCodeModal from '@/components/modals/AccessCodeModal.vue';
import WidgetContainer from '@/components/article/WidgetContainer.vue'

export default {
    components: { BaseButton, Logo, WidgetContainer },
    props: ['data', 'params', 'position'],
    computed: {
        ...mapState(useAppStore, ['config']),
        ...mapState(useActionStore, ['samePage'])
    },
    watch: {
        samePage(){
			window.location.href = window.siteConfig.siteUrl
		}
    },
    methods: {
        handleOpenAccessCodeModal(){
            this.$dialog.open(AccessCodeModal, {
                props:{
                    header: this.$t('Enter Access Code'),
                    class: 'p-dialog-xs',
                    modal: true,
                    dismissableMask: true,
                    draggable: false
                }
            })
        }
    }
}
</script>