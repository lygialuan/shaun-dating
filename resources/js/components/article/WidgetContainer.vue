<template>
    <div class="widget-box">
        <div v-if="$slots.title" class="widget-box-header">
            <h3 class="widget-box-header-title">
                <slot name="title"></slot>
            </h3>
        </div>
        <div v-if="$slots.body" class="widget-box-body">
            <slot name="body"></slot>
        </div>
    </div>
</template>