<template>
    <ContentWarningWrapper :content-warning-list="post.content_warning_categories" :post="post">
        <PaidContentWrapper :item="post" :ratio="1">
            <div class="relative cursor-pointer bg-white dark:bg-slate-800 pb-[100%]" @click="handleClickPost()">
                <div class="absolute inset-0">
                    <div class="w-full h-full bg-center bg-cover bg-no-repeat" :style="{ backgroundImage: `url(${thumbUrl})`}"></div>
                    <div class="bg-black-trans-4 text-white w-8 h-8 flex justify-center items-center rounded-full absolute top-2 start-2">
                        <BaseIcon :name="iconType" size="20" />
                    </div>
                    <div class="flex items-center justify-center gap-6 text-white absolute inset-0 bg-black-trans-4 opacity-0 transition-opacity hover:opacity-100">
                        <div class="flex items-center gap-2 leading-none">
                            <BaseIcon name="heart"/>
                            <span>{{ $filters.numberShortener(post.like_count, $t('[number]'), $t('[number]')) }}</span>
                        </div>
                        <div class="flex items-center gap-2 leading-none">
                            <BaseIcon name="message"/>
                            <span>{{ $filters.numberShortener(post.comment_count, $t('[number]'), $t('[number]')) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </PaidContentWrapper>
    </ContentWarningWrapper>
</template>

<script>
import { checkPopupBodyClass, changeUrl } from '@/utility/index'
import PostDetailModal from '@/components/posts/PostDetailModal.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import PaidContentWrapper from '@/components/paid_content/PaidContentWrapper.vue';

export default {
    props: ['post'],
    components: { BaseIcon, ContentWarningWrapper, PaidContentWrapper },
    computed: {
        thumbUrl(){
            if (this.post.items?.length) {
                if(this.post.type === 'photo'){
                    return this.post.items[0].subject.url
                }
                if(this.post.type === 'video'){
                    return this.post.items[0].subject.thumb.url
                }
            }
            return this.post.thumb
        },
        iconType(){
            if(this.post.type === 'photo' && this.post.items.length > 1){
                return 'images'
            } else if(this.post.type === 'video'){
                return 'video_solid'
            } else {
                return 'photo'
            }
        }
    },
    methods: {
        handleClickPost(){
            let postUrl = this.$router.resolve({
                name: 'post',
                params: { 'id': this.post.id }
            });
            changeUrl(postUrl.fullPath)
            this.$dialog.open(PostDetailModal, {
                data: {
                    post: this.post
                },
                props:{
                    class: 'post-detail-modal p-dialog-lg',
                    modal: true,
                    showHeader: false,
                    draggable: false,
                    dismissableMask: false,
                    closeOnEscape: false
                },
                onClose: () => {
                    changeUrl(this.$router.currentRoute.value.fullPath)
                    checkPopupBodyClass();
                }
            });
        },
    }
}
</script>