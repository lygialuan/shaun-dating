<template>
    <div v-for="i in itemNumber" :key="i" class="flex items-center px-2 py-base-2">
        <Skeleton shape="circle" size="2.5rem" />
        <div class="flex-1 ms-base-2 me-4">
            <Skeleton width="10rem" class="mb-base-1" />
            <Skeleton width="6rem" />
        </div>
        <Skeleton width="1.5rem" height="1.5rem" />
    </div>
</template>

<script>
import Skeleton from "primevue/skeleton";

export default {
    components: { Skeleton },
    props: {
        itemNumber: {
            type: Number,
            default: 10
        }
    }
};
</script>