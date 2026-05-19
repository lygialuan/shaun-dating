<template>
    <div v-for="i in itemNumber" :key="i" class="flex flex-col">
        <div class="w-full aspect-[4/5] overflow-hidden rounded-[16px]">
            <Skeleton class="w-full !h-full"/>
        </div>
        <Skeleton class="mt-2 w-3/4 h-[1.25rem] md:h-[1.5rem] rounded-md"/>
    </div>
</template>

<script>
import Skeleton from "primevue/skeleton";

export default {
    components: { Skeleton },
    props: {
        itemNumber: {
            type: Number,
            default: 12
        }
    }
};
</script>
