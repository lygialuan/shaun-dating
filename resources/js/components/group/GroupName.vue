<template>
    <GroupInfoPopover :item="group" :active-popover="activePopover" class="inline min-w-0 max-w-full">
        <router-link :to="{name: 'group_profile', params: {id: group.id, slug: group.slug}}" class="base-username inline font-semibold text-inherit">{{ group.name }}</router-link>
    </GroupInfoPopover>
</template>

<script>
import { defineAsyncComponent } from 'vue';
const GroupInfoPopover = defineAsyncComponent(() => import('@/components/group/GroupInfoPopover.vue'));

export default {
    props: {
        group: {
            type: Object,
            default: null
        },
        activePopover: {
            type: Boolean,
            default: true
        },
    },
    components: { GroupInfoPopover }
}
</script>