import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import ServerLayout from '@/pages/ServerLayout.vue';

export default [    
  {
    path: '/groups/:tab?',
    name: 'groups',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Groups'))},
  },
  {
    path: '/groups/:id/:slug/:tab?',
    name: 'group_profile',
    component: ServerLayout,
    beforeEnter: middleware.all,
    meta: { title: computed(() => i18n.global.t('Group Detail'))},
  },
  {
    path: '/groups/create',
    name: 'group_create',
    component: () => import('@/pages/groups/GroupCreate.vue'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Create Group'))},
  },
  {
    path: '/groups/:id/manage/:tab?/:sub_tab?',
    name: 'groups_manage',
    component: () => import('@/pages/group_manage/index.vue'),
    beforeEnter: middleware.user,
    props: true,
    meta: { title: computed(() => i18n.global.t('Manage')) }
  },
  {
    path: '/groups/:id/user-manage/:tab?',
    name: 'groups_user_manage',
    component: () => import('@/pages/group_profile/GroupUserManage.vue'),
    beforeEnter: middleware.user,
    props: true,
    meta: { title: computed(() => i18n.global.t('User Manage')) }
  }
]
