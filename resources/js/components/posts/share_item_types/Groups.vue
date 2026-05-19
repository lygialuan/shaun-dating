<template>
    <div class="px-base-2 pb-base-2 md:px-4 md:pb-4">
        <router-link :to="{name: 'group_profile', params: {id: item.id, slug: item.slug}}" class="block text-main-color rounded-lg border border-divider dark:border-white/10">
            <div class="block pb-[65%] bg-cover bg-center bg-no-repeat rounded-t-lg" :style="{ backgroundImage: `url(${item.cover})`}"></div>
            <div class="p-base-2">
                <div class="font-bold mb-1">{{ item.name }}</div>
                <div v-if="item.categories.length" class="text-xs truncate text-sub-color dark:text-slate-400">
                    <span v-for="(category, index) in item.categories" :key="category.id">
                        <router-link :to="{name: 'groups', params: { tab: 'all' }, query: {category_id: category.id}}" class="text-inherit">{{ category.name }}</router-link>
                        {{ (index === item.categories.length - 1) ? '' : ' · '}}
                    </span>
                </div>
            </div>
        </router-link>
    </div>
</template>

<script>
export default {
    props: {
        item: Object
    }
}
</script>