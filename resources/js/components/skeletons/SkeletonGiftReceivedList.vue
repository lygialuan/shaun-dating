<template>
    <div class="w-full overflow-x-auto">
        <table class="min-w-full rounded-lg overflow-hidden">
            <thead class="bg-gray-100 dark:bg-gray-600">
                <tr>
                    <th class="px-2 py-3 text-left">
                        <Skeleton width="80px" height="16px" />
                    </th>
                    <th class="px-2 py-3 text-left">
                        <Skeleton width="80px" height="16px" />
                    </th>
                    <th class="px-2 py-3 text-left">
                        <Skeleton width="80px" height="16px" />
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="i in itemNumber" :key="i" class="border-b">
                    <td class="px-2 py-4">
                        <div class="flex items-center gap-3">
                            <Skeleton shape="circle" size="40px" />
                            <Skeleton width="100px" height="16px" />
                        </div>
                    </td>
                    <td class="px-2 py-4">
                        <Skeleton width="100px" height="16px" />
                    </td>
                    <td class="px-2 py-4">
                        <Skeleton width="100px" height="16px" />
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script>
import Skeleton from "primevue/skeleton";

export default {
    components: { Skeleton },
    props: {
        itemNumber: {
            type: Number,
            default: 5
        }
    }
};
</script>