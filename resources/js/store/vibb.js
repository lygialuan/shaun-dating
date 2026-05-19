import { defineStore } from 'pinia'

export const useVibbStore = defineStore('vibb', {

    state: () => ({
        currentVibb: null,
        showVibbComment: false
    }),
    actions: {
        setCurrentVibb(vibb){
            this.currentVibb = vibb
        },
        setShowVibbComment(status){
            this.showVibbComment = status
        }
    },
    persist: false
  })