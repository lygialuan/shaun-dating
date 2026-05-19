<template>
    <div class="video-player-short flex flex-col justify-center relative group" ref="videoContainer" :id="`videoContainer-${playerId}`">
        <div class="absolute inset-0 overflow-hidden rounded-none md:rounded-md">
            <div class="w-full h-full bg-cover bg-center bg-no-repeat blur-3xl scale-150" :style="{ backgroundImage: `url(${video.thumb.url})`}"></div>
        </div>
        <div class="flex flex-col justify-center overflow-hidden w-full h-full z-10 md:rounded-md">
            <video
                ref="player"
                :src="video.file?.url"
                :muted="volume === 0"
                :preload="preload"
                :id="`videoMain-${playerId}`"
                :autoplay="allowAutoplay"
                :controls="false"
                :loop="loop"
                playsinline
                class="max-h-full"
                :poster="video.thumb.url" 
            />
        </div>
        <div class="h-28 bg-story-detail-linear absolute top-0 left-0 right-0 rounded-none md:rounded-t-md z-10"></div>
        <template v-if="controls">
            <div class="absolute flex justify-between items-center transition-opacity opacity-100 md:opacity-0 md:group-hover:opacity-100 z-30" :style="{ left: actionOffsetX + 'px', top: isFullScreen ? '12px' : `${actionOffsetY}px` }">
                <div class="flex items-start gap-base-2">
                    <button v-if="allowTogglePlay" :class="vibbActionClass" @click.stop="togglePlay">
                        <BaseIcon :name="playIcon" />
                    </button>
                    <button v-if="allowFullScreen" :class="vibbActionClass" @click.stop="toggleFullScreen">
                        <BaseIcon :name="fullScreenIcon" />
                    </button>
                    <div :class="[vibbActionClass, `volume-controls volume-controls-${volumeOrientation}`]" @click.stop>
                        <button @click.stop="toggleMute" class="leading-none">
                            <BaseIcon :name="volumeIcon" />
                        </button>
                        <div v-if="!isMobile" class="volume-controls-slider">
                            <Slider v-model="volume" :orientation="volumeOrientation" :step="0.01" :min="0" :max="1" @change="updateVolume" ref="volume" />
                        </div>
                    </div>
                </div>
            </div>
            <div v-if="allowTogglePlay" class="absolute inset-0 z-20 cursor-pointer" @click="togglePlay"></div>
        </template>
        <div v-if="showProgressBar" class="video-progress absolute inset-x-0 -bottom-2 z-30 py-2">
            <Slider v-model="percentagePlayed" :step="1" :min="0" :max="100" @change="onSeek" class="cursor-pointer" />
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { useVideoStore } from '@/store/video'
import { uuidv4, checkiOSWeb } from '@/utility/index'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Slider from 'primevue/slider'

export default {
    props: {
        video: {
            type: Object,
            default: null
        },
        autoPlay:{
            type: Boolean,
            default: false
        },
        preload: {
            type: String,
            default: 'auto'
        },
        loop: {
            type: Boolean,
            default: true
        },
        allowFullScreen: {
            type: Boolean,
            default: true
        },
        allowPip: {
            type: Boolean,
            default: true
        },
        allowTogglePlay: {
            type: Boolean,
            default: true
        },
        showProgressBar: {
            type: Boolean,
            default: true
        },
        isContentWarning: {
            type: Boolean,
            default: false
        },
        actionOffsetX: {
			type: Number,
			default: 12
		},
		actionOffsetY: {
			type: Number,
			default: 12
		},
        volumeOrientation: {
            type: String,
            default: 'horizontal'
        },
        controls: {
            type: Boolean,
            default: true
        },
        alwaysPlay: {
            type: Boolean,
            default: false
        },
        reload: {
            type: Boolean,
            default: false
        }
    },
    components: { BaseIcon, Slider },
    data() {
        return {
            isPlaying: false,
            duration: 0,
            percentagePlayed: 0,
            time: 0,
            volume: 0,
            isFullScreen: false,
            videoKey: uuidv4(),
            pipActive: false,
            observer: null
        };
    },
    computed: {
        ...mapState(useAppStore, ['openedModalCount', 'isMobile']),
        ...mapState(useAuthStore, ['user']),
        ...mapState(useVideoStore, ['muteVideo', 'volumeValue', 'volumeCurrentValue', 'currentlyPlaying', 'currentPipVideo']),
        volumeIcon(){
            if (this.volume > 0.66) {
                return 'speaker_high'
            } else if (this.volume > 0) {
                return 'speaker_low'
            } else {
               return 'speaker_none'
            }
        },
        playIcon(){
            return this.isPlaying ? 'pause' : 'play';
        },
        fullScreenIcon(){
            return this.isFullScreen ? 'fullscreen_exit' : 'fullscreen';
        },
        vibbActionClass(){
            return 'flex justify-center items-center p-3 bg-black-trans-6 text-white rounded-full'
        },
        playerId(){
            return this.video.file.id + '_' + this.videoKey
        },
        allowAutoplay(){
            return !this.isContentWarning && ((this.user.video_auto_play && this.autoPlay) || this.alwaysPlay)
        }
	},
    mounted() {
        this.bindEvents();
        this.volume = this.volumeCurrentValue || this.volumeValue
        this.$refs.player.volume = this.volume;

        document.addEventListener('visibilitychange', this.handleVisibilityChange);

        this.handlePlayVisibleItem(this.reload)
    },
    unmounted() {
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
        if (this.observer) {
            this.observer.disconnect();
            this.observer = null;
        }
    },
    watch: {
        volumeValue(){
            this.volume = this.volumeValue
        },
        volumeCurrentValue(){
            this.volume = this.volumeCurrentValue
        },
        volume(){
            this.$refs.player.volume = this.volume;
        },
        openedModalCount(newValue){
            if(newValue > 0){
                this.$refs.player?.pause();
            } else {
                this.handlePlayVisibleItem()
            }
        },
        time(){
            this.setCurrentPlayedTime(this.time)
        },
        isContentWarning(newVal){
            if(newVal){
                this.pause()
            } else {
                if ((this.user.video_auto_play && this.autoPlay) || this.alwaysPlay) {
                    this.play();
                }
            }
        },
        currentlyPlaying(newVal){
            if (newVal !== this.playerId) {
                this.pause();
            }
        }
    },
    methods: {
        ...mapActions(useVideoStore, ['setMuteVideo', 'setVolumeValue', 'setVolumeCurrentValue', 'setCurrentlyPlaying', 'setCurrentPlayedTime', 'setCurrentPipVideo']),
        bindEvents() {
            const player = this.$refs.player;
            const playerContainer = this.$refs.videoContainer;
            player.addEventListener("loadeddata", this.onLoadedData);
            player.addEventListener("timeupdate", this.onTimeUpdate);
            player.addEventListener("play", this.onPlay);
            player.addEventListener("pause", this.onPause);
            player.addEventListener("ended", this.onEnded);
            if(this.allowFullScreen){
                playerContainer.addEventListener('fullscreenchange', this.updateFullscreenButton);
                playerContainer.addEventListener('webkitfullscreenchange', this.updateFullscreenButton);
            }
            if(this.allowPip){
                player.addEventListener("enterpictureinpicture", this.onEnterPiP);
                player.addEventListener("leavepictureinpicture", this.onLeavePiP);
            }
        },
        onLoadedData() {
            this.duration = this.$refs.player?.duration;
        },
        onTimeUpdate() {
            this.percentagePlayed = (this.$refs.player?.currentTime / this.duration) * 100;
            this.time = this.$refs.player?.currentTime;
        },
        onPlay() {
            this.isPlaying = true;
        },
        onPause() {
            this.isPlaying = false;
        },
        onEnded() {
            this.isPlaying = false;
        },
        play(){
            if(!this.currentPipVideo){
                this.setCurrentlyPlaying(this.playerId);
                this.$refs.player?.play();
                if ((this.currentlyPlaying && this.currentlyPlaying !== this.playerId) || this.isContentWarning) {
                    this.pause();
                }
            }
        },
        pause(){
            this.$refs.player?.pause();
        },
        togglePlay() {
            if(!this.allowTogglePlay){
                return
            }
            if (this.isPlaying) {
                this.pause()
            } else {
                this.play()
            }
        },
        doMute(){
            this.setMuteVideo(true)
            this.setVolumeValue(0)
        },
        doUnmute(){
            this.setMuteVideo(false)
            if (this.muteVideo) {
                this.setVolumeValue(0)
            }else if(this.volumeCurrentValue){
                this.setVolumeValue(this.volumeCurrentValue)
            }else{
				this.setVolumeValue(1)
			}
        },
        toggleMute() {
            this.muteVideo ? this.doUnmute() : this.doMute()  
        },
        onSeek(e) {
            const percentage = e;
            this.$refs.player.currentTime = (percentage / 100) * this.duration;
        },
        convertTimeToDuration(seconds) {
            return [
                Math.floor(seconds / 60)
                    .toString()
                    .padStart(2, "0"),
                Math.floor(seconds % 60)
                    .toString()
                    .padStart(2, "0"),
            ].join(":");
        },
        updateVolume() {
            if (this.$refs.player.muted) {
                this.$refs.player.muted = false;
            }
			this.setVolumeCurrentValue(this.volume);
            if(this.$refs.player.volume === 0){
                this.setMuteVideo(true)
            } else  {
                this.setMuteVideo(false)
            }
        },
        toggleFullScreen() {
            if(this.allowFullScreen){
                if (document.fullscreenElement || document.webkitIsFullScreen) {
                    if (document.exitFullscreen) {
                        document.exitFullscreen()
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen()
                    }
                } else {
                    const videoContainer = this.$refs.videoContainer
                    const player = this.$refs.player

                    if(checkiOSWeb()){
                        player.webkitEnterFullscreen()
                    } else {
                        if (videoContainer.requestFullscreen) {
                            videoContainer.requestFullscreen();
                        } else if (videoContainer.webkitRequestFullscreen) {
                            videoContainer.webkitRequestFullscreen();
                        } else if (videoContainer.msRequestFullscreen) {
                            videoContainer.msRequestFullscreen();
                        }
                    }
                }
                if(this.user.video_auto_play){
                    setTimeout(() => {
                        this.play()
                    }, 100);
                }
            }
        },
        updateFullscreenButton() {
            if(this.allowFullScreen){
                if (document.fullscreenElement || document.webkitIsFullScreen) {
                    this.isFullScreen = true
                } else {
                    this.isFullScreen = false
                }
            }
        },
        async togglePiP(){
            if(this.allowPip){
                if (!document.pictureInPictureElement) {
                    try {
                        await this.$refs.player.requestPictureInPicture();
                    } catch (error) {
                        console.error("Error enabling PiP mode:", error);
                    }
                } else {
                    await document.exitPictureInPicture();
                }
            }
        },
        onEnterPiP() {
            if(this.allowPip) {
                this.pipActive = true;
                this.setCurrentPipVideo(this.playerId);
            }
        },
        onLeavePiP() {
            if(this.allowPip) {
                this.pipActive = false;
                this.setCurrentPipVideo(null);
            }
        },
        handlePlayVisibleItem(reload = false){
            const _self = this;
            if (this.observer) {
                this.observer.disconnect();
            }
            this.observer = new IntersectionObserver(function(entries) {
                if (entries[0].isIntersecting) {
                    _self.$emit('in-viewport');
                    if (_self.allowAutoplay) {
                        _self.play();
                        if (reload) {
                            _self.$refs.player.currentTime = 0;
                        }
                    } else {
                        _self.pause();
                    }
                } else {
                    if (_self.currentPipVideo !== _self.playerId) {
                        _self.pause();
                    }
                }
            }, { threshold: [0.8] });

            const videoContainer = document.getElementById(`videoContainer-${this.playerId}`);
            if (videoContainer) {
                this.observer.observe(videoContainer);
            }
        },
        handleVisibilityChange() {
            if (document.hidden) {
                this.pause();
            } else {
                this.handlePlayVisibleItem();
            }
        }
    },
    emits: ['in-viewport']
};
</script>
