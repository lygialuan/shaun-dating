<template>
    <form @submit.prevent="handleStoreUserList" class="space-y-base-2">
        <div>
            <label class="mb-base-1">{{ $t('List Name') }}</label>
            <BaseInputText v-model="listName" autofocus :error="error.name"/>
        </div>
        <BaseButton :loading="loadingSave" fluid>{{ $t('Save') }}</BaseButton>
    </form>
</template>

<script>
import { storeUserList } from '@/api/user_list'
import BaseInputText from '@/components/inputs/BaseInputText.vue'
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    inject: ['dialogRef'],
    components: {
        BaseInputText,
        BaseButton
    },
    data(){
        return{
            id: this.dialogRef.data.item?.id,
            listName: this.dialogRef.data.item?.name,
            loadingSave: false,
            error: {
                name: null
            }
        }
    },
    methods:{
        async handleStoreUserList(){
            this.loadingSave = true
            try {
                const response = await storeUserList({
                    id: this.id,
                    name: this.listName
                })
                this.dialogRef.close({item: response});
            } catch (error) {
                this.handleApiErrors(this.error, error)
            } finally {
                this.loadingSave = false
            }
        }
    }
}
</script>