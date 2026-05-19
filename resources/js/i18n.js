import { createI18n } from 'vue-i18n'
import { get } from 'lodash';

export const i18n = createI18n({
    locale: window.siteConfig.languageDefault,
    globalInjection: true,
    legacy: false,
    fallbackLocale: 'en',
    messageResolver: (obj, path) => {
        const message = get(obj, path, '');
        const specialCharRegex = /&quot;|&#039;|&lt;|&gt;|[&<>"']/;
        if (message && specialCharRegex.test(message)) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = message.replaceAll(/@/g, "{'@'}");
            return textarea.value;
        }
        return null;
    }
})