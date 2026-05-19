<template>
    <VibbHeader :type="subjectType" @back="closeModal" @change_tab="changeTab" />
    <div class="pt-16 h-full">
        <VibbsList :currentVibb="vibb" :loading="loadingVibbsList" :vibbs-list="vibbsList" @load-more="loadMoreVibbs" />
    </div>
</template>
<script>
import { mapActions, mapState } from "pinia";
import { usePostStore } from "@/store/post";
import VibbsList from "@/components/vibb/VibbsList.vue";
import VibbHeader from "@/components/vibb/VibbHeader.vue";

export default {
    components: { VibbsList, VibbHeader },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data() {
        return {
            subjectId: this.dialogRef.data?.subjectId ? this.dialogRef.data?.subjectId : 0,
            subjectType: this.dialogRef.data?.subjectType ? this.dialogRef.data?.subjectType : '',
            vibb: this.dialogRef.data?.vibb ? this.dialogRef.data?.vibb : null,
            currentPage: 1
        };
    },
    computed: {
        ...mapState(usePostStore, ["loadingVibbsList", "vibbsList"])
    },
    mounted() {
        this.handleGetVibbs()
        history.pushState({ modalOpen: true }, '', '');
        window.addEventListener('popstate', this.handleBrowserBack);
    },
    unmounted() {
        this.unsetVibbsList();
        window.removeEventListener('popstate', this.handleBrowserBack);
    },
    watch:{
        '$route'(){
            this.dialogRef.close()
        }
    },
    methods: {
        ...mapActions(usePostStore, ["getVibbsForYouList", "getFollowingVibbsList", "getUserVibbsModalList", "getMyVibbsModalList", "unsetVibbsList"]),
        handleGetVibbs(){
            switch (this.subjectType) {
                case 'following':
                    this.getFollowingVibbsList(this.currentPage);
                    break;
                case 'my':
                    this.getMyVibbsModalList(this.currentPage);
                    break;
                case 'user':
                    this.getUserVibbsModalList(this.subjectId, this.currentPage);
                    break;
                default:
                    this.getVibbsForYouList(this.currentPage);
                    break;
            }
        },
        loadMoreVibbs($state) {
            switch (this.subjectType) {
                case 'following':
                    this.getFollowingVibbsList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                case 'my':
                    this.getMyVibbsModalList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                case 'user':
                    this.getUserVibbsModalList(this.subjectId, ++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
                default:
                    this.getVibbsForYouList(++this.currentPage).then((response) => {
                        if (response.length === 0) {
                            $state.complete();
                            this.isAtBottom = true
                        } else {
                            $state.loaded();
                        }
                    });
                    break;
            }
        },
        closeModal() {
            if (history.state?.modalOpen) {
               history.back();
            }
            this.dialogRef.close()
        },
        changeTab(name) {
            this.subjectType = name
            this.currentPage = 1
            this.handleGetVibbs()
		},
        handleBrowserBack() {
            this.closeModal();
        }
    }
};
</script>