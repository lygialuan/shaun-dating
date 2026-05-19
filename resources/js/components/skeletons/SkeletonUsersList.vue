<template>
    <div v-for="i in itemNumber" :key="i" class="flex items-center gap-base-2 mb-base-2">
        <Skeleton shape="circle" size="2.5rem" class="flex-shrink-0" />
        <div class="flex-1">
            <Skeleton width="10rem" class="mb-base-1" />
            <Skeleton width="5rem" />
        </div>
        <Skeleton width="3rem" height="1.25rem" class="flex-shrink-0" />
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