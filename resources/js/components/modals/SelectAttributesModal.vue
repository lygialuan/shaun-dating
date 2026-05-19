<template>
       <div class="w-full space-y-2"> 
        <BaseButton @click="closeDialog()" class="w-full" :disabled="dialogRef.data.isButtonDisabled">{{$t('Save')}}</BaseButton>
        <BaseButton type="transparent" @click="cancel()" fluid>{{$t('Cancel')}}</BaseButton>
    </div>
</template>

<script>
import { mapActions } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { saveAttributes, saveInterestAttributes } from '@/api/dating';
import BaseButton from '@/components/inputs/BaseButton.vue';

export default {
    components:{ BaseButton },
    inject: ['dialogRef'],
    data: function() {
        return {
            error_message: ''
        }
    },
    methods:{
		...mapActions(useAuthStore, ['me']),
        closeDialog() {
            if(this.dialogRef.data.att === "interest"){
                return  this.handleSaveInterestAttributes(this.dialogRef.data.selectedIds)
            }
            this.handleSaveAttributes(this.dialogRef.data.selectedIds)
        },
        async handleSaveAttributes(data) {
            try{
                await saveAttributes({data});
                this.me();
                this.showSuccess(this.$t('Your changes have been saved.'))
                this.dialogRef.close(this.dialogRef.data);
            }catch (error){
                this.showError(error.error);
            }
        },
        async handleSaveInterestAttributes(data) {
            try{
                await saveInterestAttributes({data});
                this.me();
                this.showSuccess(this.$t('Your changes have been saved.'))
                this.dialogRef.close(this.dialogRef.data);
            }catch (error){
                this.showError(error.error);
            }
        },
        cancel() {
            this.dialogRef.close();
        },
    }
}
</script>
