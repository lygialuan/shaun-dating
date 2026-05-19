import { useAuthStore } from "../../../store/auth";

export default (to, from, next) => {
    if (useAuthStore().authenticated) {
        next();
    } else {
        if (window.siteConfig.forceLogin) {
            var redirect= btoa(window.location.href)
            next({ name: 'login' ,query: { redirect: redirect } });
        } else {
            next();
        }
    }
}