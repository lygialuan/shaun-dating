import { defineStore } from 'pinia'

export const useDraftStore = defineStore('draft', {
    state: () => ({
        drafts: {}
    }),
    actions: {
        setDraft(key, content) {
            this.drafts[key] = content
        },
        removeDraft(key) {
            delete this.drafts[key]
        },
        getDraft(key) {
            return this.drafts[key] || ''
        }
    }
})
