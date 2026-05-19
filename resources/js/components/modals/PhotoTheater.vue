<template>
    <Teleport to="body">
        <div v-if="displayPhotosTheater" class="photos-feed-fullscreen-mask" @click="handleClickBackdrop">
            <div class="absolute top-0 h-20 w-full bg-header-linear z-20"></div>
            <div class="absolute bottom-0 h-20 w-full bg-footer-linear z-20"></div>
            <div class="absolute bottom-4 sm:bottom-3 md:top-2 md:bottom-[inherit] end-2 flex text-white z-20">
                <BaseButton
                    @click="rotateRight"
                    type="transparent-secondary"
                    icon="arrow_clockwise"
                    :size="screen.xs ? 'md' : 'lg'"
                />
                <BaseButton
                    @click="rotateLeft"
                    type="transparent-secondary"
                    icon="arrow_counter_clockwise"
                    :size="screen.xs ? 'md' : 'lg'"
                />
                <BaseButton
                    @click="zoomOut"
                    type="transparent-secondary"
                    icon="magnifying_glass_minus"
                    :size="screen.xs ? 'md' : 'lg'"
                    :disabled="isZoomOutDisabled"
                />
                <BaseButton
                    @click="zoomIn"
                    type="transparent-secondary"
                    icon="magnifying_glass_plus"
                    :size="screen.xs ? 'md' : 'lg'"
                    :disabled="isZoomInDisabled"
                />
                <BaseButton
                    @click="reset"
                    type="transparent-secondary"
                    icon="bounding_box"
                    :size="screen.xs ? 'md' : 'lg'"
                    :disabled="isResetDisabled"
                />
                <BaseButton
                    @click="close"
                    type="transparent-secondary"
                    icon="close"
                    :size="screen.xs ? 'md' : 'lg'"
                />
            </div>
            <VueperSlides
                v-if="photos.length > 0"
                fractions
                ref="photosFeedFullscreen"
                :initSlide="activeSlide"
                :infinite="false"
                :arrows="!screen.md"
                :bullets="false"
                :touchable="isTouchableSlider"
                disable-arrows-on-edges
                :dragging-distance="50"
                prevent-y-scroll
                @slide="handleSlide"
                :rtl="user.rtl ? true : false"
                :gap="5"
                class="photos-feed-fullscreen-slider no-shadow"
            >
                <VueperSlide
                    v-for="(postItem, index) in photos"
                    :key="postItem.id"
                >
                    <template #content>
                        <div
                            class="vueperslide__content-wrapper absolute top-1/2 start-1/2 -translate-x-1/2 -translate-y-1/2 w-full z-10"
                        >
                            <img
                                :src="postItem.subject.url"
                                class="drag-none"
                                :class="{
                                    transition: !isDragging,
                                    'cursor-move': isEnableDragging,
                                }"
                                :style="
                                    index + 1 === activeSlide
                                        ? imagePreviewStyle
                                        : {}
                                "
                                @mousedown="startDrag"
                                @mousemove="drag"
                                @mouseup="stopDrag"
                                @mouseleave="stopDrag"
                                @touchstart="startDrag"
                                @touchmove="drag"
                                @touchend="stopDrag"
                            />
                        </div>
                    </template>
                </VueperSlide>
                <template #arrow-left>
                    <button
                        class="photos-feed-fullscreen-control-btn fixed flex items-center justify-center text-white w-16 h-16 top-1/2 -translate-y-1/2 left-2"
                    >
                        <BaseIcon name="caret_left" size="48" />
                    </button>
                </template>
                <template #arrow-right>
                    <button
                        class="photos-feed-fullscreen-control-btn fixed flex items-center justify-center text-white w-16 h-16 top-1/2 -translate-y-1/2 right-2"
                    >
                        <BaseIcon name="caret_right" size="48" />
                    </button>
                </template>
            </VueperSlides>
        </div>
    </Teleport>
</template>

<script>
import { mapState } from "pinia";
import { useAuthStore } from "@/store/auth";
import { useAppStore } from '@/store/app';
import { VueperSlides, VueperSlide } from "vueperslides";
import BaseIcon from "@/components/icons/BaseIcon.vue";
import BaseButton from "@/components/inputs/BaseButton.vue";

export default {
    components: {
        VueperSlides,
        VueperSlide,
        BaseIcon,
        BaseButton,
    },
    props: ["photos"],
    data() {
        return {
            activeSlide: 1,
            displayPhotosTheater: false,
            rotate: 0,
            scale: 1,
            positionX: 0,
            positionY: 0,
            isDragging: false,
            dragStartX: 0,
            dragStartY: 0,
        };
    },
    computed: {
        ...mapState(useAppStore, ['screen']),
        ...mapState(useAuthStore, ["user"]),
        imagePreviewStyle() {
            return {
                transform: `rotate(${this.rotate}deg) scale(${this.scale}) translate(${this.positionX}px, ${this.positionY}px)`,
                transformOrigin: "center center",
            };
        },
        isZoomInDisabled() {
            return this.scale >= 2;
        },
        isZoomOutDisabled() {
            return this.scale <= 0.4;
        },
		isResetDisabled(){
			return this.scale === 1 && this.rotate === 0
		},
        isTouchableSlider() {
            return this.rotate === 0 && this.scale === 1 && !this.isDragging;
        },
        isEnableDragging() {
            return this.scale > 1 && !this.isTouchableSlider;
        },
    },
    methods: {
        onKeyDown(event) {
            switch (event.code) {
                case "Escape":
                    this.close();
                    break;
                case "ArrowLeft":
                    if (this.user.rtl) {
                        this.$nextTick(this.$refs.photosFeedFullscreen.next());
                    } else {
                        this.$nextTick(
                            this.$refs.photosFeedFullscreen.previous()
                        );
                    }
                    break;
                case "ArrowRight":
                    if (this.user.rtl) {
                        this.$nextTick(
                            this.$refs.photosFeedFullscreen.previous()
                        );
                    } else {
                        this.$nextTick(this.$refs.photosFeedFullscreen.next());
                    }
                    break;
                default:
                    break;
            }
        },
        openPhotosTheater(photoIndex) {
            this.displayPhotosTheater = true;
            this.activeSlide = photoIndex + 1;
            document.body.classList.add("overflow-hidden");
            window.addEventListener("keydown", this.onKeyDown);
        },
        close() {
            this.displayPhotosTheater = false;
            this.activeSlide = 1;
            this.resetTransform();
            this.resetPosition();
            document.body.classList.remove("overflow-hidden");
            window.removeEventListener("keydown", this.onKeyDown);
        },
        handleClickBackdrop(e) {
            if (e.target.tagName == "DIV") {
                this.displayPhotosTheater = false;
                document.body.classList.remove("overflow-hidden");
                window.removeEventListener("keydown", this.onKeyDown);
            }
        },
        rotateRight() {
            this.rotate += 90;
            this.resetPosition();
        },
        rotateLeft() {
            this.rotate -= 90;
            this.resetPosition();
        },
        zoomIn() {
            this.scale = Math.min(this.scale + 0.2, 2);
        },
        zoomOut() {
            this.scale = Math.max(this.scale - 0.2, 0.4);
            this.resetPosition();
        },
        handleSlide(e) {
            this.activeSlide = e.currentSlide.index + 1;
            this.resetTransform();
            this.resetPosition();
        },
        startDrag(event) {
            if (this.isEnableDragging) {
                this.isDragging = true;

                const clientX = event.type === "touchstart" ? event.touches[0].clientX : event.clientX;
                const clientY = event.type === "touchstart" ? event.touches[0].clientY : event.clientY;

                this.dragStartX = clientX;
                this.dragStartY = clientY;
                this.initialPositionX = this.positionX;
                this.initialPositionY = this.positionY;
            }
        },
        stopDrag() {
            this.isDragging = false;
        },
        drag(event) {
            if (this.isDragging) {
				if (event.type === 'touchmove') {
					event.preventDefault();
				}

				const clientX = event.type === 'touchmove' ? event.touches[0].clientX : event.clientX;
				const clientY = event.type === 'touchmove' ? event.touches[0].clientY : event.clientY;

                const deltaX = clientX - this.dragStartX;
				const deltaY = clientY - this.dragStartY;

                const radians = (this.rotate * Math.PI) / 180;

                const rotatedDeltaX = deltaX * Math.cos(radians) + deltaY * Math.sin(radians);
                const rotatedDeltaY = -deltaX * Math.sin(radians) + deltaY * Math.cos(radians);

                this.positionX = this.initialPositionX + rotatedDeltaX;
                this.positionY = this.initialPositionY + rotatedDeltaY;
            }
        },
        resetPosition() {
            this.positionX = 0;
            this.positionY = 0;
        },
        resetTransform() {
            this.rotate = 0;
            this.scale = 1;
        },
        reset() {
            this.resetPosition();
            this.resetTransform();
        }
    },
};
</script>
