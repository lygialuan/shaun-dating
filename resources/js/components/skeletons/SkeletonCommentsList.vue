<template>
    <div class="flex flex-col gap-base-2">
        <div v-for="i in itemNumber" :key="i" class="flex gap-x-base-2">
            <Skeleton shape="circle" size="2.5rem" />
            <div class="flex-1">
                <Skeleton width="5rem" class="mb-base-1" />
                <Skeleton width="10rem" />
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