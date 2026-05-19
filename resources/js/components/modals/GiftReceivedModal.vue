<template>
    <GiftReceivedList :loading="loading" :gifts-list="giftsList" @load-more="loadMoreGiftReceived">
        <template #empty>
            <div class="p-5 text-center">
                {{ $t('Nothing to see here yet') }}
            </div>
        </template>
    </GiftReceivedList>
</template>

<script>
import { getGiftReceivedList } from '@/api/gift'
import GiftReceivedList from '@/components/gift/GiftReceivedList.vue'

export default {
    components: { GiftReceivedList },
    inject: {
        dialogRef: { default: null }
    },
    data() {
        return {
            userInfo: this.dialogRef?.data?.userInfo || {},
            currentPage: 1,
            giftsList: [],
            loading: true
        }
    },
    mounted() {
        this.initData()
    },
    methods: {
        async initData() {
            this.loading = true
            await this.handleGetGiftReceived(this.userInfo.id, 1)
            this.loading = false
        },
        async handleGetGiftReceived(userId, page) {
            try {
                const response = await getGiftReceivedList(userId, page)
                if (page == 1) {
					this.giftsList = [];
				}	
                this.giftsList.push(...response)
                return response
            } catch (error) {
                console.error(error)
                return []
            }
        },
        loadMoreGiftReceived($state) {
			this.handleGetGiftReceived(this.userInfo.id, ++this.currentPage).then((response) => {
				if(response.length === 0){
					$state.complete()
				}else{
					$state.loaded()
				}
			})
		}
    }
}
</script>