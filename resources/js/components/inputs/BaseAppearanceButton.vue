<template>
    <button v-if="enableDarkmode">
        <BaseIcon :name="darkmode === 'on' ? 'sun' : 'moon'" @click="toggleDarkmode(darkmode === 'on' ? 'off' : 'on')"></BaseIcon>
    </button>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { storeDarkmode } from '@/api/user'
import { useAppStore } from '@/store/app';
import Constant from '@/utility/constant'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    data(){
		return {
			enableDarkmode: Constant.ENABLE_DARKMODE
		}
	},
    computed:{
        ...mapState(useAppStore, ['darkmode'])
    },
    methods:{
        ...mapActions(useAppStore, ['setDarkmode']),
        async toggleDarkmode(status){
            try {
                await storeDarkmode(status)
                this.setDarkmode(status);
            } catch (error) {
                this.showError(error.error)
            }
        },
    }
}
</script>