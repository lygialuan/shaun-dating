<template>
    <h2 class="text-xl font-bold mb-4">{{ $t("Profiles") }}</h2>
	<CardUserDating :users="pagesList" :loading="loadingPages" hidden-swipe="true" :isPageExplore="false"/>
    <template v-if="!loadingPages">
        <InfiniteLoading @infinite="loadMorePages">				
            <template #spinner>
                <Loading />
            </template>
            <template #complete><span></span></template>
        </InfiniteLoading>
    </template>
</template>

<script>
import { mapState, mapActions } from "pinia";
import { usePagesStore } from "@/store/page";
import { useAppStore } from "@/store/app";
import { useAuthStore } from "@/store/auth";
import CardUserDating from '@/components/matched/CardUserDating.vue'
import InfiniteLoading from 'v3-infinite-loading'
import Loading from '@/components/utilities/Loading.vue'

export default {
    components: { CardUserDating, InfiniteLoading, Loading },
    data() {
        return {
            currentPage: 1,
        };
    },
    computed: {
        ...mapState(useAuthStore, ["user"]),
        ...mapState(usePagesStore, ["loadingPages", "pagesList"]),
    },
    mounted() {
        if (this.user.is_page) {
            this.setErrorLayout(true);
        } else {
            this.getMyPagesList(this.currentPage);
        }
    },
    unmounted() {
        this.unsetPagesList();
    },
    methods: {
        ...mapActions(useAppStore, ["setErrorLayout"]),
        ...mapActions(usePagesStore, ["getMyPagesList", "unsetPagesList"]),
        loadMorePages($state) {
            this.getMyPagesList(++this.currentPage).then((response) => {
                if (response.has_next_page) {
                    $state.loaded();
                } else {
                    $state.complete();
                }
            });
        },
    },
};
</script>
