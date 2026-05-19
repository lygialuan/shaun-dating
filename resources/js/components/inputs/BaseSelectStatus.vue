<template>
    <DropdownMenu v-if="itemData.canEnable || itemData.canStop" :offset-y="10">
        <template v-slot:dropdown-button>
            <button class="flex gap-base-1 items-center">
                <div v-if="modelValue === 'active'" class="w-4 h-4 rounded-full bg-base-green"></div>
                <div v-if="modelValue === 'stop'" class="w-4 h-4 rounded-full bg-base-red"></div>
                <div v-if="modelValue === 'done'" class="w-4 h-4 rounded-full bg-web-wash"></div>
                <BaseIcon v-if="itemData.status != 'done'" name="caret_down" size="16" />
            </button>
        </template>
        <template v-slot:dropdown-content>
            <ul class="max-w-xs">
                <li v-if="itemData.canEnable">
                    <button @click="selectStatus('active', itemData.id)" class="flex gap-base-2 items-center p-2">
                        <div class="w-4 h-4 rounded-full flex-shrink-0 bg-base-green"></div>
                        {{ $t('Active') }}
                    </button>
                </li>
                <li v-if="itemData.canStop">
                    <button @click="selectStatus('stop', itemData.id)" class="flex gap-base-2 items-center p-2">
                        <div class="w-4 h-4 rounded-full flex-shrink-0 bg-base-red"></div>
                        {{ $t('Pause') }}
                    </button>
                </li>
                <li>
                    <button @click="selectStatus('done', itemData.id)" class="flex gap-base-2 items-center p-2">
                        <div class="w-4 h-4 rounded-full flex-shrink-0 bg-web-wash"></div>
                        {{ $t('Complete') }}
                    </button>
                </li>           
            </ul>
        </template>
    </DropdownMenu>
    <div v-else class="w-4 h-4 rounded-full bg-web-wash"></div>
</template>

<script>
import { storeStopAdvertising, storeEnableAdvertising, storeCompleteAdvertising } from '@/api/ads'
import DropdownMenu from '@/components/utilities/DropdownMenu.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue';

export default {
    components: { DropdownMenu, BaseIcon },
    props: {
        modelValue: {
			type: String,
			default: null
		},
        item: {
            type: Object,
            default: null
        }
    },
    data(){
        return {
            itemData: this.item
        }
    },
    methods:{
        async selectStatus(status, adsId){
            try {
                switch (status) {
                    case 'active':
                        this.itemData.canStop = true
                        this.itemData.canEnable = false
                        await storeEnableAdvertising(adsId)
                        break;
                    case 'stop':
                        this.itemData.canStop = false
                        this.itemData.canEnable = true
                        await storeStopAdvertising(adsId)
                        break;
                    case 'done':
                        this.itemData.canStop = false
                        this.itemData.canEnable = false
                        await storeCompleteAdvertising(adsId)  
                        break;
                    default:
                        break;
                }
                this.showSuccess(this.$t('Updated Successfully.'))
                this.$emit('update:modelValue', status);
            } catch (error) {
                this.showError(error.error)
            }
        }
    },
    emits: ['update:modelValue']
}
</script>