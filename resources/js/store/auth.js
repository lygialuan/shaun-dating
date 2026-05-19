import { defineStore } from 'pinia'
import localData from '@/utility/localData'

import { login, logout, signup } from '@/api/auth';
import { me, loginFirst, storeAccount } from '@/api/user'
import { storeProfileSettings } from '@/api/setting'
import { storeProfilePage } from '@/api/page'
import { checkOffline } from '@/utility/index'

export const useAuthStore = defineStore('auth', {
    // convert to a function
    state: () => ({
        authenticated: localData.get('authenticated', null) != null && ! checkOffline(),
        user: null
    }),
    actions: {
        setVideoAutoPlay(status) {
            this.user.video_auto_play = status
        },
        setDatingSearchHistory(data) {
            this.user.dating_search_history = data
        },
        updateUserData(userData){
            Object.keys(this.user).forEach((key) => this.user[key] = userData[key] ? userData[key] : this.user[key] )
        },
        updateWalletBalance(amount){
            this.user.wallet_balance = amount
        },
        async login(user) {
            await login(user)
                .then(() => {      
                    localData.set('authenticated', true);
                });
        },
        async logout() {
            await logout(localData.get('fcm_token'))
            this.remove()
        },
        async me() {
            await me()
                .then((response) => {       
                    if (this.user && this.user.id != response.data.data.id) {
                        window.location.href = window.siteConfig.siteUrl
                    }             
                    this.user = response.data.data;
                });
        },
        remove() {
            localData.remove('authenticated')
            localData.removePwa()
        },
        loginFirst() {
            loginFirst().then(() => {                    
                this.user.already_setup_login = true;
            });
        },
        async storeProfileSettings(profileData){
            const response = await storeProfileSettings(profileData)
            this.updateUserData(profileData)	
            return response				
		},
        async storeProfilePageSettings(profileData){
            await storeProfilePage(profileData)
            this.updateUserData(profileData)					
		},
        async storeAccountSettings(accountData){
            await storeAccount(accountData)
            this.updateUserData(accountData)					
		},
        async signupUser(user) {
            await signup(user)
            localData.set('authenticated', true);
        },
        updateCurrentPlan(plan){
            this.user.membership_package_name = plan
        },
        updateUserMeInfo(data) {
            this.user = data
        }
    },
    persist: false
  })