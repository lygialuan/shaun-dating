<template>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="rounded-lg border border-divider dark:border-white/10 bg-white h-full dark:bg-dark-form-base">
            <div class="block pb-[35%] relative overflow-hidden rounded-t-lg">
                <div class="absolute inset-0">
                    <Skeleton height="100%" width="100%" class="rounded-none"/>
                </div>
            </div>
            <div class="p-base-2">
                <div class="flex gap-4 mb-base-2">
                    <Skeleton shape="circle" size="3.125rem" class="flex-shrink-0" />
                    <div class="flex-1 space-y-2">
                        <Skeleton height="1.25rem" width="80%" />
                        <Skeleton width="50%" />
                        <Skeleton width="50%" />
                    </div>
                </div>
                <Skeleton width="100%" height="2.25rem" />
            </div>
        </div>
    </div>
</template>

<script>
import Skeleton from "primevue/skeleton";

export default {
    components: { Skeleton },
    props: {
        itemNumber: {
            type: Number,
            default: 2
        }
    }
};
</script>