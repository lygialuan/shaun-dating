import { defineStore } from 'pinia'
import { getAttributes, getInterestAttributes } from '@/api/dating';

export const useDatingStore = defineStore('dating', {
    state: () => ({
        originAttributes: [],
        originInterestAttributes: []
    }),
    actions: {
        async handleGetAttributes() {
            const response = await getAttributes();
            this.originAttributes = response;
        },
        async handleGetInterestAttributes() {
            const response = await getInterestAttributes();
            this.originInterestAttributes = response;
        },
    }
})
