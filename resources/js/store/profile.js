import { defineStore } from 'pinia'
import { setTitlePage } from '@/utility'
import { getUserProfileInfo } from '@/api/user'
import { useAppStore } from '@/store/app'

export const useProfileStore = defineStore('profile', {
    state: () => ({
        userInfo: null
    }),
    actions: {
        async getUserInfo(userName){
            try {
				const response = await getUserProfileInfo(userName)
				this.userInfo = response
				setTitlePage(this.userInfo.name);
			} catch (error) {
				useAppStore().setErrorLayout(true)
			}
        },
        setUserInfo(value) {
            this.userInfo = value
        },
        setTotalGiftReceived(){
            this.userInfo.total_gift_received++
        }
    },
    persist: false
})