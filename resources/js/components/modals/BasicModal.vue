<template>
    <div class="text-center">
        {{ content }}
    </div>
</template>

<script>
export default {
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            content: this.dialogRef.data.content
        }
    },
}
</script>