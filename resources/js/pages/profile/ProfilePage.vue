<template>
	<div v-if="userInfo">
	</div>
</template>

<script>
import { mapActions, mapState } from 'pinia'
import { useProfileStore } from '@/store/profile'

export default {
	components: {
	},
	props: ['data', 'params', 'position'],
	data() {
		return {
			currentTab: this.params.tab ? this.params.tab : ''
		}
	},
	watch: {
        currentRouter(newVal){
            this.currentTab = newVal.params.tab || ''
        }
    },
	async mounted(){
		await this.getUserInfo(this.params.user_name)
		await this.handleOpenProfile(this.userInfo)
	},
	unmounted() {
		this.setUserInfo()
	},
	computed: {
		...mapState(useProfileStore, ['userInfo']),
	},
	methods: {
		...mapActions(useProfileStore, ['getUserInfo', 'setUserInfo']),
		handleOpenProfile(user) {
            if (document.querySelector('.p-dialog-profile')) return
            this.openProfile({ user, pageProfile: true })
        }
	}
}
</script>