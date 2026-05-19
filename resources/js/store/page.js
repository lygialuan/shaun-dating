import { defineStore } from 'pinia'
import { getMyUserPages } from '@/api/page'

export const usePagesStore = defineStore('pages', {
    state: () => ({
        pagesList: [],
        loadingPages: true,
    }),
    actions: {
        unsetPagesList(){
            this.pagesList = [];
            this.loadingPages = true
        },
        doPushPagesList(pushedPagesList, page){
            if (page === 1) {
                this.pagesList = [];
            }
            this.pagesList = window._.concat(this.pagesList, pushedPagesList)
        },
        async getMyPagesList(page){
            try {
                const response = await getMyUserPages(page)
                this.doPushPagesList(response.items, page)
                this.loadingPages = false
                return response
            } catch (error) {
                console.log(error)
                this.loadingPages = false
            }
        }
    },
    persist: false
  })