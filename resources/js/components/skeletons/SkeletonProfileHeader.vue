<template>
    <div class="bg-white rounded-none md:rounded-base-lg dark:bg-slate-800 mb-base-2">
        <div class="block relative pb-[38.5%] overflow-hidden rounded-none md:rounded-t-base-lg">
            <div class="absolute inset-0">
                <Skeleton height="100%" width="100%" borderRadius="0" />
            </div>
        </div>
        <div class="relative inline-block p-base-1 ms-4 -mt-20 md:-mt-28 rounded-full bg-white dark:bg-slate-800">
            <Skeleton class="!w-28 !h-28 md:!w-base-36 md:!h-base-36" shape="circle"></Skeleton>
        </div>
        <div class="pt-2 px-4 pb-4">
            <div class="flex flex-wrap justify-between items-start gap-x-6 gap-y-base-2 mb-base-2">
                <div>
                    <Skeleton width="12rem" height="1.25rem" class="mb-base-2" />
                    <Skeleton width="8rem" height="1rem" />
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <Skeleton height="2.375rem" width="6rem"/>
                    <Skeleton height="2.375rem" width="2.375rem"/>
                </div>
            </div>
            <Skeleton width="14rem" height="1rem" />
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
            default: 5
        }
    }
};
</script>