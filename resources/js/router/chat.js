import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [    
  {
    path: '/chat/inbox/:room_id?',
    name: 'chat',
    props: true,
    component: loadPageView('chat'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Chat')), permission: 'chat.allow' },
  },
  {
    path: '/chat/requests/:room_id?',
    name: 'chat_requests',
    props: true,
    component: loadPageView('chat'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Requests')), permission: 'chat.allow' },
  }
]