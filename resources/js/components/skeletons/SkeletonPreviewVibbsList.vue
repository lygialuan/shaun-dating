<template>
    <div class="grid grid-cols-[repeat(auto-fill,minmax(180px,1fr))] gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="block relative pb-[162.5%] overflow-hidden rounded-lg">
            <div class="absolute inset-0">
                <Skeleton height="100%" width="100%" />
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
            default: 5
        }
    }
};
</script>