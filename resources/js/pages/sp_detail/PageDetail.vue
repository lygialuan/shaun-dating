<template>
    <div class="main-content-section">
        <div class="main-content-section-header">
            <h3 class="main-content-section-header-title">{{data.data.title}}</h3>
        </div>
        <div v-html="data.data.content" class="page-content-section"></div>
    </div>
</template>

<script>
export default {
    props: ['data', 'params', 'position']
}
</script>

<style>

</style>