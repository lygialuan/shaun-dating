<template>
    <Error v-if="error">{{error}}</Error>
    <Loading v-if="loading_edit_histories"/>
    <div v-else>
        <div v-for="editHistory in editHistoriesList" :key="editHistory.id" class="flex mb-base-2 pt-base-2 border-t border-divider dark:border-white/10 last:mb-0 first:border-none">
            <Avatar :user="editHistory.user" :activePopover="false" />
			<div class="flex-1 mx-base-2 min-w-0">
                <UserName :user="editHistory.user" :activePopover="false" />
                <ContentHtml :content="editHistory.content" :mentions="editHistory.mentions"></ContentHtml>
                <div class="text-xs text-sub-color dark:text-slate-400">{{editHistory.created_at}}</div>
			</div>
        </div>
    </div>
    <div v-if="loadmore_status" class="text-center mt-5">
        <BaseButton @click="getEditHistoriesList(type, itemId, page)">{{$t('View more')}}</BaseButton>
    </div>
</template>
<script>
import { getEditHistoriesList } from '@/api/history'
import Error from '@/components/utilities/Error.vue';
import Loading from '@/components/utilities/Loading.vue';
import BaseButton from '@/components/inputs/BaseButton.vue';
import ContentHtml from '@/components/utilities/ContentHtml.vue'
import Avatar from '@/components/user/Avatar.vue'
import UserName from '@/components/user/UserName.vue'

export default {
    components: { Error, Loading, BaseButton, ContentHtml, Avatar, UserName },
    inject: {
        dialogRef: {
            default: null
        }
    },
    data(){
        return{
            type: this.dialogRef.data.type,
            itemId: this.dialogRef.data.id,
            error: null,
            loadmore_status: false,
            loading_edit_histories: true,
            editHistoriesList: [],
            page: 1
        }
    },
    mounted() {
        this.getEditHistoriesList(this.type, this.itemId, this.page)
    },
    methods: {
        async getEditHistoriesList(type, itemId, page){
            try {             
				const response = await getEditHistoriesList(type, itemId, page)
                if(page === 1){
                    this.editHistoriesList = response.items
                }else{
                    this.editHistoriesList = window._.concat(this.editHistoriesList, response.items);
                }
                if(response.has_next_page){
                    this.loadmore_status = true
                    this.page++;
                }else{
                    this.loadmore_status = false
                }
                this.loading_edit_histories = false
			} catch (error) {
                this.error = error
                this.loading_edit_histories = false
			}
        }
    } 
}
</script>