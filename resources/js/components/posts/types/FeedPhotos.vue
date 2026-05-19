<template>
    <ContentWarningWrapper :content-warning-list="post.content_warning_categories" :post="post">
        <PaidContentWrapper :item="post" :parent-item="parentPost">
            <div :id="postFeedSliderId">
                <VueperSlides
                    v-if="post.items"
                    ref="photosFeed"
                    :slide-ratio="aspectRatioImage(post.items[0].subject.params)"
                    :infinite="false"
                    :arrows="true"
                    disable-arrows-on-edges
                    :rtl="user.rtl"
                    :touchable="false"
                    :gap="5"
                    @slide="handleSlide($event.currentSlide.index)"
                    class="activity_content_photos_list no-shadow"
                >
                    <VueperSlide
                        v-for="(postItem, index) in post.items"
                        :key="postItem.id"
                        class="bg-no-repeat"
                        :style="{ backgroundColor: postItem.subject.params.dominant_color || '#000' }"
                        :image="postItem.subject.url"
                        @click="openPhotoTheater(index)"
                        role="button"
                    ></VueperSlide>
                    <template #arrow-left>
                        <button class="text-light-gray">
                            <BaseIcon name="arrow_circle_left" size="32" />
                        </button>
                    </template>
                    <template #arrow-right>
                        <button class="text-light-gray">
                            <BaseIcon name="arrow_circle_right" size="32" />
                        </button>
                    </template>
                </VueperSlides>
            </div>
        </PaidContentWrapper>
    </ContentWarningWrapper>
    <PhotoTheater ref="photoTheater" :photos="post.items" />
</template>

<script>
import { mapState } from 'pinia';
import { VueperSlides, VueperSlide } from 'vueperslides';
import { useAuthStore } from '@/store/auth';
import { uuidv4 } from '@/utility/index';
import PhotoTheater from '@/components/modals/PhotoTheater.vue';
import ContentWarningWrapper from '@/components/utilities/ContentWarningWrapper.vue';
import BaseIcon from '@/components/icons/BaseIcon.vue'
import PaidContentWrapper from '@/components/paid_content/PaidContentWrapper.vue';

export default {
    components: {
        VueperSlides,
        VueperSlide,
        PhotoTheater,
        ContentWarningWrapper,
        BaseIcon,
        PaidContentWrapper
    },
    props: {
        post: {
            type: Object,
            default: null
        },
        parentPost: {
            type: Object,
            default: null
        }
    },
    data() {
        return {
            feedKey: uuidv4(),
			BULLET_WIDTH: 14,
            MAX_VISIBLE_BULLETS: 6,
            BULLET_CONTAINER_WIDTH: 84,
            BULLET_SCROLL_THRESHOLD: 70,
            BULLET_SCROLL_OFFSET: 14
        };
    },
    computed: {
        ...mapState(useAuthStore, ['user']),
        postFeedSliderId() {
            return `postFeedSlider_${this.post.id}_${this.feedKey}`;
        },
    },
    mounted() {
        this.adjustBulletsPosition();
    },
    methods: {
        openPhotoTheater(photoIndex) {
            this.$refs.photoTheater.openPhotosTheater(photoIndex);
        },
        adjustBulletsPosition() {
            const postParentWrap = document.getElementById(this.postFeedSliderId);
            const bullets = postParentWrap?.getElementsByClassName('vueperslides__bullet');
            if (bullets?.length > this.MAX_VISIBLE_BULLETS) {
                const translateX = (bullets.length * this.BULLET_WIDTH - this.BULLET_CONTAINER_WIDTH) / 2;
                for (const bullet of bullets) {
                    bullet.style.transform = `translateX(${this.user.rtl ? '-' : ''}${translateX}px)`;
                }
            }
        },
        handleSlide(index) {
            const postParentWrap = document.getElementById(this.postFeedSliderId);
            const bullets = postParentWrap?.getElementsByClassName('vueperslides__bullet');
            if (bullets?.length > this.MAX_VISIBLE_BULLETS) {
                const bullet = bullets[index];
                const bulletRect = this.getRelativeBoundingClientRect(bullet);
                const translateXPrev =
                    new DOMMatrixReadOnly(window.getComputedStyle(bullets[0]).transform).e + this.BULLET_SCROLL_OFFSET;
                const translateXNext =
                    new DOMMatrixReadOnly(window.getComputedStyle(bullets[0]).transform).e - this.BULLET_SCROLL_OFFSET;

                if (bulletRect.left > this.BULLET_SCROLL_THRESHOLD) {
                    for (const bullet of bullets) {
                        bullet.style.transform = `translateX(${translateXNext}px)`;
                    }
                } else if (bulletRect.left < this.BULLET_WIDTH) {
                    for (const bullet of bullets) {
                        bullet.style.transform = `translateX(${translateXPrev}px)`;
                    }
                }
            }
        },
        getRelativeBoundingClientRect(element) {
            const domRect = element.getBoundingClientRect();
            const parentDomRect = element.parentElement.getBoundingClientRect();
            return new DOMRect(
                domRect.left - parentDomRect.left,
                domRect.top - parentDomRect.top,
                domRect.width,
                domRect.height
            );
        }
    }
};
</script>