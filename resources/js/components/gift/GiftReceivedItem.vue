<template>
    <tr class="border-b hover:bg-gray-100 dark:hover:bg-gray-600 dark:border-white/10 transition-colors text-sm break-words">
        <td class="px-2 py-2" v-if="gift.gift">
            <div class="flex flex-col md:flex-row md:items-center gap-1 md:gap-2">
                <img :src="gift.gift.icon" :alt="gift.gift.name" class="w-10 h-10 object-contain shrink-0"/>
                {{ gift.gift.name }}
            </div>
        </td>
        <td class="px-2 py-2" v-else>
            {{  $t('Deleted Gift') }}
        </td>
        <td class="px-2 py-2">
            {{ gift.name }}
        </td>
        <td class="px-2 py-2">
            {{ gift.date }}
        </td>
    </tr>
</template>

<script>
export default {
    props: {
        gift: {
            type: Object,
            required: true
        }
    }
}
</script>