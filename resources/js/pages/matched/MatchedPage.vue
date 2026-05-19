<template>
    <div class="p-2 md:p-0">
        <div class="flex gap-4 sm:gap-6 mb-4 overflow-x-auto no-scrollbar">
            <button v-for="tab in tabs" :key="tab.key" class="whitespace-nowrap text-sm font-medium" :class="[ activeTab === tab.key ? 'border-b-2 border-black text-black dark:border-white dark:text-white' : 'text-gray-400 dark:text-dark-text-base']" @click="onTabClick(tab)">
                {{ tab.label }}
            </button>
        </div>
        <component :is="currentComponent"/>
    </div>
</template>

<script>
import MatchedList from '@/components/matched/MatchedList.vue'
import LikedMeList from '@/components/matched/LikedMeList.vue'
import ILikedList from '@/components/matched/ILikedList.vue'
import ViewedList from '@/components/matched/ViewedList.vue'
import ViewedMeList from '@/components/matched/ViewedMeList.vue'

export default {
    components: {
        MatchedList,
        LikedMeList,
        ILikedList,
        ViewedList,
        ViewedMeList,
	},
    data() {
        return {
            activeTab: 'matched',
            tabs: [
                { key: 'matched', label: this.$t('Matched') },
                { key: 'liked_me', label: this.$t('Liked me'), permission_key: 'dating.allow_see_who_liked_you_list' },
                { key: 'i_liked', label: this.$t('I liked') },
                { key: 'viewed', label: this.$t('Viewed') },
                { key: 'viewed_me', label: this.$t('Viewed me') },
            ]
        }
    },
    computed: {
        currentComponent() {
            return {
                matched: 'MatchedList',
                liked_me: 'LikedMeList',
                i_liked: 'ILikedList',
                viewed: 'ViewedList',
                viewed_me: 'ViewedMeList'
            }[this.activeTab]
        }
    },
    methods: {
        onTabClick(tab) {
            if (!tab.permission_key || this.checkPermission(tab.permission_key)) {
                this.activeTab = tab.key
            }
        }
    }
}
</script>
