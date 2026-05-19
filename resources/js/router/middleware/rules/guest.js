import { useAuthStore } from "../../../store/auth";

export default (to, from, next) => {
    if (useAuthStore().authenticated) {
        next({ name: 'home' });
    } else {
        next();
    }
}