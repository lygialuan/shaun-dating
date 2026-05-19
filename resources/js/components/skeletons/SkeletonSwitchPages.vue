<template>
    <div class="space-y-base-2">
        <div v-for="i in itemNumber" :key="i" class="flex items-center gap-base-2">
            <Skeleton shape="circle" size="3rem" class="flex-shrink-0" />
            <div class="flex-1">
                <Skeleton width="10rem" height="1.25rem" />
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