<template>
    <div class="bg-black fixed inset-0 z-[999] pt-16">
        <VibbHeader :type="currentTab" @back="handleBackToHome" @change_tab="changeTab" />
        <VibbsList :key="currentTab" :currentVibb="vibbInfo" :loading="loadingVibbsList" :vibbs-list="vibbsList" @load-more="loadMoreVibbs" />
    </div>
</template>
<script>
import { mapActions, mapState } from "pinia";
import { usePostStore } from "@/store/post";
import VibbsList from "@/components/vibb/VibbsList.vue";
import VibbHeader from "@/components/vibb/VibbHeader.vue";

export default {
    props: ['tab'],
    components: { VibbsList, VibbHeader },
    data() {
        return {
            currentTab: this.tab ? this.tab : '',
            currentPage: 1,
            vibbId: this.$route.query.id ? this.$route.query.id : 0
        };
    },
    computed: {
        ...mapState(usePostStore, ["loadingVibbsList", "vibbsList", "vibbInfo"])
    },
    watch: {
        '$route'() {
            this.vibbId = this.$route.query.id ? this.$route.query.id : 0
        },
    },
    mounted() {
        if(this.vibbId){
            this.loadVibbInfo(this.vibbId)
        }
        this.handleGetVibbs()
    },
    unmounted() {
        this.unsetVibbInfo();
        this.unsetVibbsList();
    },
    methods: {
        ...mapActions(usePostStore, ["getVibbById", "unsetVibbInfo", "getVibbsForYouList", "getFollowingVibbsList", "unsetVibbsList"]),
        async loadVibbInfo(vibbId){
            try {
                await this.getVibbById(vibbId);
            } catch (error) {
                this.showError(error.error)
            }
        },
        handleGetVibbs(){
            if(this.currentTab === 'following'){
                this.getFollowingVibbsList(this.currentPage);
            } else {
                this.getVibbsForYouList(this.currentPage);
            }
        },
        loadMoreVibbs($state) {
            if(this.currentTab === 'following'){
                this.getFollowingVibbsList(++this.currentPage).then((response) => {
                    if (response.length === 0) {
                        $state.complete();
                        this.isAtBottom = true
                    } else {
                        $state.loaded();
                    }
                });
            } else {
                this.getVibbsForYouList(++this.currentPage).then((response) => {
                    if (response.length === 0) {
                        $state.complete();
                        this.isAtBottom = true
                    } else {
                        $state.loaded();
                    }
                });
            }
        },
        changeTab(name) {
			this.currentTab = name
            this.currentPage = 1
            this.handleGetVibbs()
		},
        handleBackToHome(){
            this.$router.push({ name: 'home' })
        }
    },
};
</script>