<template>
    <div class="bg-red-100 text-center border border-red-400 text-base-red p-3 mb-base-2 rounded">
        <slot>{{$t('Error')}}</slot>			
    </div>
</template>

<script>
export default {
}
</script>