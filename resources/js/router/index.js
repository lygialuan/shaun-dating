import { createRouter, createWebHistory } from 'vue-router';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { checkOffline, setTitlePage, loadPageView, getBase } from '@/utility/index';
import middleware from './middleware';
import list from './list'
import chats from './chat'
import stories from './story'
import settings from './setting';
import wallet from './wallet';
import pages from './pages';
import ads from './ads'
import membership from './membership'
import groups from './groups'
import vibb from './vibb'
import ServerLayout from '@/pages/ServerLayout.vue';
import { useAppStore } from '../store/app';

const routes = [
  {
    path: '/',
    alias: ['/explore'],
    name: 'home',
    component: ServerLayout,
    meta: { title: computed(() => i18n.global.t('Home')) }
  },
  {
    path: '/'+ window.siteConfig.profilePrefix +':user_name?/:tab?',
    name: 'profile',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Profile')) }
  },
  {
    path: '/first-login',
    name: 'first_login',
    component: loadPageView('first_login'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Hashtags Signup')) }
  },
  {
    path: '/invites/:tab?',
    name: 'invites',
    props: true,
    component: loadPageView('invites'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Invites')) }
  },
  {
    path: '/post/:id/:comment_id?/:reply_id?',
    name: 'post',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Post Detail')) }
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: loadPageView('notifications'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Notifications')) }
  },
  {
    path: '/search/:search_type/:type',
    name: 'search',
    props: true,
    beforeEnter: middleware.all,
    component: loadPageView('search'),
    meta: { title: computed(() => i18n.global.t('Search')) }
  },
  {
    path: '/discovery',
    name: 'discovery',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Discovery')) }
  },
  {
    path: '/watch',
    name: 'watch',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Watch')) }
  },
  {
    path: '/register',
    name: 'register',
    component: loadPageView('register'),
    beforeEnter: middleware.guest,
    meta: { title: computed(() => i18n.global.t('Register')) }
  },
  {
    path: '/login',
    name: 'login',
    component: loadPageView('login'),
    beforeEnter: middleware.guest,
    meta: { title: computed(() => i18n.global.t('Login')) }
  },
  {
    path: '/signup',
    name: 'signup',
    component: loadPageView('signup'),
    beforeEnter: middleware.guest,
    meta: { title: computed(() => i18n.global.t('Signup')) }
  },
  {
    path: '/openid/auth/:name',
    name: 'openid',
    component: loadPageView('openid'),
    meta: { title: computed(() => i18n.global.t('Social Login')) }
  },
  {
    path: '/:catchAll(.*)',
    name: 'page_not_found',
    component: loadPageView('page_not_found'),
    meta: { title: computed(() => i18n.global.t('Page Not Found')) }
  },
  {
    path: '/bookmarks',
    name: 'bookmarks',
    component: ServerLayout,
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Bookmark')) }
  },
  {
    path: '/hashtags/:tab?',
    name: 'hashtags',
    props: true,
    component: loadPageView('hashtags'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Hashtags')) }
  },
  {
    path: '/users/:tab?',
    name: 'users',
    props: true,
    component: loadPageView('users'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('UsersPage')) }
  },
  {
    path: '/unsubscribe/:email/:hash',
    name: 'unsubscribe',
    props: true,
    component: loadPageView('unsubscribe'),
    meta: { title: computed(() => i18n.global.t('Unsubscribe')) }
  },
  {
    path: '/email_confirm',
    name: 'email_confirm',
    props: true,
    component: loadPageView('email_confirm'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Email Confirm')) }
  },
  {
    path: '/sp/:slug',
    name: 'sp_detail',
    component: ServerLayout,
    meta: { title: computed(() => i18n.global.t('Page')) }
  },
  {
    path: '/contact',
    name: 'contact',
    component: loadPageView('contact'),
    meta: { title: computed(() => i18n.global.t('Contact Us')) }
  },
  {
    path: '/recover',
    name: 'recover',
    component: loadPageView('recover'),
    beforeEnter: middleware.guest,
    meta: { title: computed(() => i18n.global.t('Recover')) }
  },
  {
    path: '/permission',
    name: 'permission',
    component: loadPageView('permission'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Permission')) }
  },
  {
    path: '/verify_profile',
    name: 'verify_profile',
    component: loadPageView('verify_profile'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Verify Profile')) }
  },
  {
    path: '/media',
    name: 'media',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Media')) }
  },
  {
    path: '/documents',
    name: 'documents',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Documents')) }
  },
  {
    path: '/phone_confirm',
    name: 'phone_confirm',
    component: loadPageView('phone_confirm'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Phone Confirm')) }
  },
  {
    path: '/two_factor_authentication',
    name: 'two_factor_authentication',
    component: loadPageView('two_factor'),
    beforeEnter: middleware.guest,
    meta: { title: computed(() => i18n.global.t('Two-factor authentication')) }
  },
  {
    path: '/chatbot',
    name: 'chatbot',
    component: loadPageView('chatbot'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Chatbot')) }
  },
  {
    path: '/explore_new',
    name: 'explore',
    component: ServerLayout,
    meta: { title: computed(() => i18n.global.t('Explore')) }
  },
  {
    path: '/compliance/:id?',
    name: 'compliance',
    props: true,
    component: loadPageView('compliance'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Compliance')) }
  },
  {
    path: '/photos_confirm',
    name: 'photos_confirm',
    props: true,
    component: loadPageView('photos_confirm'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Photos Confirm')) }
  },
  {
    path: '/identity_confirm',
    name: 'identity_confirm',
    props: true,
    component: loadPageView('verify_profile'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Identity Confirm')) }
  },
  {
    path: '/matched',
    name: 'matched',
    component: ServerLayout,
    meta: { title: computed(() => i18n.global.t('Matched')) }
  },
];

routes.push(
  ...list,
  ...settings,
  ...chats,
  ...stories,
  ...wallet,
  ...pages,
  ...ads,
  ...membership,
  ...groups,
  ...vibb
);

const router = createRouter({
  history: createWebHistory(getBase()),
  scrollBehavior() {
    // always scroll to top
    return { top: 0 }
  },
  routes,
});

router.afterEach((to, from) => {
  useAppStore().setErrorLayout(false)
  if (from.fullPath == to.fullPath) {
    return
  }
  if (to.meta.title != null) {
    setTitlePage(to.meta.title.value);
  }
}); 

router.beforeEach(async (to) => {
  if (to.name !== 'home' && checkOffline()) {
    // redirect the user to the login page
    return { name: 'home' }
  }

  document.querySelectorAll(".p-dialog-mask").forEach(el => el.remove());
  document.body.classList.remove('p-overflow-hidden', 'overflow-hidden');
  useAppStore().setIsOpenSidebar(false)
})

export default router;
