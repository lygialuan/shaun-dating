import { useAuthStore } from "../../../store/auth";

export default (to, from, next) => {
    if (useAuthStore().authenticated) {
        next();
    } else {
        var redirect= btoa(window.location.href)
        next({ name: 'home' ,query: { redirect: redirect } });
    }
}