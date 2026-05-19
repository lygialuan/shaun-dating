import { defineStore } from 'pinia'

export const useActionStore = defineStore('action', {
    // convert to a function
    state: () => ({
        userAction: null,
        samePage: null
    }),
    actions: {
        updateFollowStatus(userAction){
            this.userAction = userAction
        },
        doSamePage(data){
            this.samePage = data
        }
    },
    persist: false
  })