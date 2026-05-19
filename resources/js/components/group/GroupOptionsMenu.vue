<template>
    <div class="options-menu-modal flex flex-col text-main-color dark:text-white outline-none" tabindex="0" autofocus>
        <button v-if="group.canReport" :class="importantActionClass" @click="handleReportGroup(group.id)">{{$t('Report')}}</button>
        <button :class="normalActionClass" @click="handleShareGroup('groups', group)">{{$t('Share')}}</button>
        <button :class="normalActionClass" @click="handleDoCancel()">{{$t('Cancel')}}</button>
    </div>
</template>

<script>
import { mapState } from 'pinia'
import { useAuthStore } from '@/store/auth'
import { checkPopupBodyClass } from '@/utility/index'
import ReportModal from '@/components/modals/ReportModal.vue'
import ShareOptionsMenu from '@/components/share/ShareOptionsMenu.vue'

export default {
    data(){
        return{
            group: this.dialogRef.data.group
        }
    },
    inject: {
        dialogRef: {
            default: null
        }
    },
    computed:{
        ...mapState(useAuthStore, ['authenticated']),
        importantActionClass(){
            return 'options-menu-modal-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10 text-base-red font-bold';
        },
        normalActionClass(){
            return 'options-menu-modal-sub-text options-menu-modal-border text-center p-4 border-t border-light-divider first:border-none dark:border-white/10';
        }
    },
    methods:{
        handleReportGroup(groupId){
            this.dialogRef.close();
            setTimeout(() => this.$dialog.open(ReportModal, {
                data: {
                    type: 'groups',
                    id: groupId
                },
                props:{
                    header: this.$t('Report'),
                    class: 'group-report-modal',
                    modal: true,
                    draggable: false
                }
             }), 300);
        },
        handleShareGroup(type, subject){
            this.dialogRef.close();
            setTimeout(() => {
                if(this.authenticated){
                    this.$dialog.open(ShareOptionsMenu, {
                        data: {
                            subjectType: type,
                            subject: subject
                        },
                        props:{
                            showHeader: false,
                            class: 'dropdown-menu-modal',
                            modal: true,
                            dismissableMask: true,
                            draggable: false
                        },
                        onClose: () => {
                            checkPopupBodyClass();
                        }
                    })
                }else{
                    this.showRequireLogin()
                }
            }, 300);
        },
        handleDoCancel(){
            this.dialogRef.close();
        }
    }
}
</script>