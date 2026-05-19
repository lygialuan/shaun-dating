import { ref, computed } from 'vue'

export function useCaptcha(enableWidget, enableRecapcha, enableTurnstile) {
    const isLoadedRecapcha = ref(false)
    const turnstileToken = ref('')
    const isEnable = computed(() => enableWidget)

    const loadRecaptcha = (recaptchaInstance) => {
        if (isEnable.value && enableRecapcha()) {
            const recaptcha = recaptchaInstance.value
            recaptcha?.showBadge()
            isLoadedRecapcha.value = true
        }
    }

    const unloadRecaptcha = (recaptchaInstance) => {
        if (isEnable.value && enableRecapcha()) {
            const recaptcha = recaptchaInstance.value
            recaptcha?.hideBadge()
        }
    }

    const getCaptchaToken = async (recaptchaFunc, turnstileValue, context) => {
        if (isEnable.value) {
            if (enableRecapcha()) {
                return await recaptchaFunc(context)
            } else if (enableTurnstile()) {
                return turnstileValue
            }
        }
        return null
    }

    const isVerified = () => {
        if (isEnable.value) {
            if (enableRecapcha()) return isLoadedRecapcha.value;
            if (enableTurnstile()) return !!turnstileToken.value;
        }
        return true
    }

    return {
        enableWidget,
        turnstileToken,
        loadRecaptcha,
        unloadRecaptcha,
        getCaptchaToken,
        isVerified
    }
}
