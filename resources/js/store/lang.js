import { defineStore } from 'pinia'
import { nextTick } from 'vue'
import localData from '../utility/localData'

export const useLangStore = defineStore('lang', {
    // convert to a function
    state: () => ({
        locale: '',
        locale_loaded: false
    }),
    actions: {
        async init({ i18n, locale }) {
            const res = await fetch(window.siteConfig.siteUrl + '/locales/' + locale + '.json?cache='+window.siteConfig.cacheNumber, {'cache': 'force-cache'});
            const response = await res.json()
            this.locale = locale;
            if (i18n.mode === 'legacy') {
                i18n.global.locale = locale;
            } else {
                i18n.global.locale.value = locale
            }

            i18n.global.setLocaleMessage(locale, response);
            this.locale_loaded = true
            return nextTick()
        },

        change(locale) {
            if (window._.has(window.siteConfig.languages,locale)) {
                localData.set('locale', locale)
                location.reload();
            }
        }
    },
    persist: false
  })