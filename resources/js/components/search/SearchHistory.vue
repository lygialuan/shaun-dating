<template>
    <Loading v-if="loading" class="!p-0 !m-0" />
    <template v-else>
        <div v-if="items.length" class="space-y-4">
            <div v-for="item in items" :key="item.id" class="flex items-center gap-x-3">
                <router-link
                    :to="{ name: 'search', params: { search_type: 'text', type: 'post' }, query: { q: item.query } }"
                    class="flex-1 text-inherit min-w-0"
                >
                    <div class="truncate">{{ item.query }}</div>
                </router-link>
                <button @click.stop.prevent="$emit('delete', item.id)" class="leading-[0] p-1">
                    <BaseIcon name="close" size="16" />
                </button>
            </div>
        </div>
        <div v-else class="text-center">
            {{ $t('Please enter keywords to search') }}
        </div>
    </template>
</template>

<script>
import Loading from '@/components/utilities/Loading.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { Loading, BaseIcon },
    props: {
        items: Array,
        loading: Boolean
    }
}
</script>