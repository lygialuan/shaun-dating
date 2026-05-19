<template>
    <div class="flex flex-col items-center gap-4">
		<img :src="asset('images/default/image_permission_modal.png')" class="max-w-[50px]">
        <div class="font-bold text-xl">{{ $t("Go Premium, Stand Out") }}</div>
        <div v-if="message" class="text-sm text-center dark:text-dark-text-base-gray">{{message}}</div>
        <div class="flex flex-col w-[50%]">
            <BaseButton size="xl" v-if="config.membership.enable" @click="openUpgradeNowModal()">{{$t('Upgrade Now')}}</BaseButton>
            <button :class="normalActionClass" @click="clickClose()">{{$t('Cancel')}}</button>
        </div>
    </div>
</template>
<script>
import { mapState } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue';
import { useAppStore } from '@/store/app'
import MemberShipPage from '@/pages/membership/index.vue';

export default {
    components: { BaseButton },
    inject: ['dialogRef'],
    data() {
		return {
			permission: this.dialogRef.data.permission,
            message: this.dialogRef.data.message,
		}
	},
    mounted() {
        if (! this.message) {
            this.message = window._.has(this.config.permissionMessages, this.permission) ? this.config.permissionMessages[this.permission] : this.$t('You do not have permission to do it.')
        }
    },
    computed: {
		...mapState(useAppStore, ['config']),
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods: {
        clickClose() {
            this.dialogRef.close()
        },
        openUpgradeNowModal: function (){
            this.$dialog.open(MemberShipPage, {
                data:{
                    popupModal: true  
                },
                props: {
                    showHeader: false,
                    modal: true,
                    draggable: false,
                    class: 'p-dialog-lg'
                },
                onClose: (options) => {
                    if (options?.data== true) {
                        this.clickClose()
                    }
                }
            });
        },
    }
}
</script>