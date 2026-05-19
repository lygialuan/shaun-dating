<template>
    <AutoComplete 
        :inputClass="inputClass" 
        :class="{'disabled': disableAutoComplete}"
        @item-select="handleItemSelect" 
        @item-unselect="handleItemUnselect" 
        multiple 
        fluid
        panelClass="max-w-full"
    >
        <template #option="slotProps">
            <slot name="option" :option="slotProps.option"></slot>
        </template>
    </AutoComplete>
</template>

<script>
import AutoComplete from 'primevue/autocomplete'

export default {
    props: {
        multiple: {
            type: Boolean,
            default: false
        }
    },
    components: { AutoComplete },
    data(){
        return{
            disableAutoComplete: false,
            inputClass: ''
        }
    },
    methods:{
        handleItemSelect(){
            if(!this.multiple){
                this.disableAutoComplete = true
                this.inputClass = 'hidden'
            }
            this.$emit('item-select');
        },
        handleItemUnselect(){
            if(!this.multiple){
                this.disableAutoComplete = false
                this.inputClass = ''
            }
            this.$emit('item-unselect');
        }
    },
    emits: ['item-select', 'item-unselect']
}
</script>