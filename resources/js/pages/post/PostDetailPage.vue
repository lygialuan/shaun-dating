<template>
    <Loading v-if="loading_status" />
    <div v-else>
        <div class="feed-item">
            <Error v-if="error" class="mb-0">{{error}}</Error>
            <div v-else>
                <div class="feed-entry-wrap">
                    <PostContent :post="postInfo" @comment_click="focusForm()" @comment_count_click="focusForm()"/>                      
                    <div v-if="postInfo">
                        <CommentsList ref="commentsList" :comment_id="params.comment_id" :reply_id="params.reply_id" :item="postInfo" :has-menu-footer="authenticated ? true : false"/>
                    </div>  
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { usePostStore } from '@/store/post';
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth'
import localData from '@/utility/localData'
import CommentsList from '@/components/comments/CommentsList.vue';
import Loading from '@/components/utilities/Loading.vue';
import Error from '@/components/utilities/Error.vue';
import PostContent from '@/components/posts/PostContent.vue';

export default {
    components: {
        CommentsList,
        Loading,
        Error,
        PostContent
    },
    props: ['data', 'params', 'position'],
    data() {    
        return {
            loading_status: true,
            error: null,
            refCode: null
		}
	},
    mounted() {
        if(this.params.id){
            this.loadPostInfo(this.params.id)
        }
        this.refCode = this.$route.query.ref_code || ''
        if (this.refCode) {
            localData.set('ref_code', this.refCode)
        } else {
            localData.remove('ref_code')
        }
    },
    unmounted(){
		this.unsetPostInfo()
        if (this.refCode) {
            localData.remove('ref_code')
        }
	},
    computed: {
        ...mapState(usePostStore, ['postInfo']),
        ...mapState(useAuthStore, ['authenticated']),
    },
    methods: {
        ...mapActions(usePostStore, ["getPostById", 'unsetPostInfo']),
        ...mapActions(useAppStore, ['setErrorLayout']),
        async loadPostInfo(postId){
            try {
                await this.getPostById(postId);
                this.loading_status = false
            } catch (error) {
                this.setErrorLayout(true)
                this.error = error.error.detail.id
                this.loading_status = false
            }
        },
        focusForm(){
            if(this.postInfo.canComment){
                this.$refs.commentsList.focusForm()
            }
        }
    }
}
</script>