<template>
    <div v-if="userReviewInfo.can_review" class="main-content-section">
        <h3 class="main-content-section-header-title mb-base-2">{{ $t('Do you recommend') + ' ' + userReviewInfo.name + '?'}}</h3>
        <div class="flex gap-base-2">
            <div class="flex-1">
                <BaseButton type="secondary" @click="handleClickRecommend(true)" fluid>{{ $t('Yes') }}</BaseButton>
            </div>
            <div class="flex-1">
                <BaseButton type="secondary" @click="handleClickRecommend(false)" fluid>{{ $t('No') }}</BaseButton>
            </div>
        </div>
    </div>
    <div class="flex gap-base-2 justify-between items-center mb-base-2 px-base-2 md:px-0">
        <h3 class="main-content-section-header-title">{{ $t('Rating') }} · {{ userReviewInfo.review_score }} {{ $filters.numberShortener(userReviewInfo.review_count, $t('([number] review)'), $t('([number] reviews)')) }}</h3>
    </div>
    <PostsList :loading="loadingPostsList" :posts-list="postsList" @load-more="loadMoreReviews">
        <template #empty>
            <div class="main-content-section">
                <div class="p-5 text-center">
                    {{ $t('Nothing to see here yet') }}
                </div>
            </div>
		</template>
    </PostsList>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import BaseButton from '@/components/inputs/BaseButton.vue'
import RecommendModal from '@/components/modals/RecommendModal.vue'
import { useAuthStore } from '@/store/auth'
import { usePostStore } from '@/store/post'
import PostsList from '@/components/posts/PostsList.vue'

export default {
    components: { BaseButton, PostsList },
    props: ['userInfo'],
    data(){
        return {
            userReviewInfo: this.userInfo,
            currentPage: 1
        }
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
		...mapState(usePostStore, ['postsList', 'loadingPostsList']),
    },
    mounted(){
        this.getReviewsPageList(this.userReviewInfo.id, this.currentPage)
        this.setCurrentPostPage('review');
    },
    unmounted(){
		this.unsetPostsList()
        this.setCurrentPostPage()
	},
    methods: {
        ...mapActions(usePostStore, ['getReviewsPageList', 'unsetPostsList', 'setCurrentPostPage']),
        handleClickRecommend(isRecommend){
            this.$dialog.open(RecommendModal, {
                data: {
                    page: this.userReviewInfo,
                    isRecommend: isRecommend
                },
                props: {
                    header: isRecommend ? this.$t('Recommend') + ' ' + this.userReviewInfo.name  : this.$t('How can') + ' ' + this.userReviewInfo.name + ' ' + this.$t('improve?'),
                    class: 'p-dialog-lg',
                    modal: true,
                    draggable: false
                },
                onClose: async(options) => {
                    if(options.data){
                        this.userReviewInfo.can_review = false
                    }
                }
            })
        },
        loadMoreReviews($state) {
			this.getReviewsPageList(this.userReviewInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
    },
    emits: ['change_tab', 'update_user_info']
}
</script>