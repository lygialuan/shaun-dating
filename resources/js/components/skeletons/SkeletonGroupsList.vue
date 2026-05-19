<template>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(260px,1fr))] gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="rounded-lg border border-divider dark:border-white/10 bg-white h-full dark:bg-dark-form-base">
            <div class="block pb-[60%] relative overflow-hidden rounded-t-lg">
                <div class="absolute inset-0">
                    <Skeleton height="100%" width="100%" class="rounded-none"/>
                </div>
            </div>
            <div class="p-4">
                <Skeleton width="75%" class="mb-2" />
                <Skeleton width="50%" class="mb-base-2" />
                <div class="flex mb-base-2">
                    <Skeleton v-for="i in 5" :key="i" width="1.5rem" class="-ms-2 first:ms-0 rounded-full" /> 
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
            default: 3
        }
    }
};
</script>