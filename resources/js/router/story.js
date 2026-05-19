import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [    
  {
    path: '/stories',
    name: 'stories',
    component: loadPageView('stories'),
    beforeEnter: middleware.user,
    meta: { title: computed(() => i18n.global.t('Stories')), permission: 'story.allow_create'  }
  },
  {
    path: '/story/:storyId',
    name: 'story_view',
    props: true,
    component: () => import('@/pages/stories/StoryDetail.vue'),
    meta: { title: computed(() => i18n.global.t('Story Detail')) }
  },
  {
    path: '/story/item/:storyItemId',
    name: 'story_view_item',
    props: true,
    beforeEnter: middleware.user,
    component: () => import('@/pages/stories/StoryItemDetail.vue'),
    meta: { title: computed(() => i18n.global.t('Story Item Detail')) }
  },
]