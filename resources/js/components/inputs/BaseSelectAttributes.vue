<template>
    <BaseIcon name="pencil" size="20" @click="openAttributeModal()"/>
</template>

<script>
import { checkPopupBodyClass } from '@/utility/index';
import SelectListingAttributesModal from '@/components/modals/SelectListingAttributesModal.vue';
import SelectAttributesModal from '@/components/modals/SelectAttributesModal.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'

export default {
    components: { BaseIcon },
    props: {
        modelValue: {
            type: Array,
            default: null
        },
        attributeOptions: {
            type: Array,
            default: () => []
        },
        options: {
            type: Array,
            default: null
        },
        error: {
            type: String,
            default: null
        },
        placeholder: {
            type: String,
            default: ''
        },
        att: {
            type: String,
            default: ''
        },
        label: {
            type: String,
            default: ''
        },
    },
    data(){
        return {
            maskItems: [],
            isOpenModal : false
        }
    },
    watch: {
        options: function(data){
            this.maskItems = data;
        }
    },
    mounted(){
        this.maskItems = this.options;
    },
    methods: {
        openAttributeModal: function (){
            if (this.isOpenModal == false) {
                this.isOpenModal = true;
                setTimeout(() => this.$dialog.open(SelectListingAttributesModal, {
                    data: {
                        isLoading: false,
                        selectedIds: this.modelValue,
                        categoryObjects: this.attributeOptions,
                        selectedAttributes: [],
                        categoryAttributeValues: [],
                        selectedAttributeId: null,
                        selectedAttributeName: null,
                        allowMultiple: null,
                        selectedAddNewCategoryId: null,
                        paginatorAttributes: {
                            total_page: 0,
                            current_page: 0,
                            limit: 7,
                            items: []
                        },
                        att: this.att
                    },
                    props:{
                        //showHeader: false,
                        header: this.label ?? this.$t('Add attributes'),
                        class: 'comment-report-modal p-dialog-lg',
                        modal: true,
                        draggable: false
                    },
                    templates: {
                        footer: SelectAttributesModal
                    },
                    onClose: (options) => {
                        checkPopupBodyClass();
                        if(typeof options.data !== 'undefined'){
                            this.maskItems = options.data.selectedAttributes;
                            this.$emit('update:modelValue', options.data.selectedIds);
                            this.$emit('changeValue', options.data.selectedIds);
                            this.$emit('update:items', this.maskItems);
                        }
                        this.isOpenModal = false;
                    }
                }), 300);
            }
        },
        removeFilter: function (index){
            this.maskItems.splice(index, 1);
            this.updateModalValue();
        },
        updateModalValue: function (){
            this.$emit('update:modelValue', this.maskItems.map(x => x.id));
            this.$emit('changeValue', this.maskItems.map(x => x.id));
            this.$emit('update:items', this.maskItems);
        }
    },
    emits: ['update:modelValue', 'changeValue', 'update:items']
}
</script>
