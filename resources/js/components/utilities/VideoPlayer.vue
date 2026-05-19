<template>
    <div class="video-container flex flex-col justify-center group" ref="videoContainer" :id="`videoContainer-${playerId}`">
        <video
            ref="player"
            :src="video.file?.url"
            :poster="video.thumb?.url"
            :muted="volume === 0"
            :preload="preload"
            :id="`videoMain-${playerId}`"
            :autoplay="allowAutoplay"
            :controls="false"
            :loop="loop"
            playsinline
        />
        <template v-if="!pipActive">
            <div v-if="isPlayed" class="absolute left-0 bottom-0 right-0 bg-black-gradient z-20" :class="{'transition-opacity opacity-0 group-hover:opacity-100': isPlaying}">
                <div class="flex items-center justify-between gap-3 text-white p-base-2" @click.stop.prevent>
                    <div class="flex items-center gap-3">
                        <button @click="togglePlay">
                            <BaseIcon :name="playIcon" />
                        </button>
                        <div class="text-xs whitespace-nowrap min-w-[80px]">
                            <div>{{ convertTimeToDuration(time) }} / {{ convertTimeToDuration(duration) }}</div>
                        </div>
                    </div>
                    <div class="flex-1 video-progress">
                        <Slider v-model="percentagePlayed" :step="1" :min="0" :max="100" @change="onSeek" />
                    </div>
                    <div class="flex items-center gap-3">
                        <button v-if="allowFullScreen" @click="toggleFullScreen">
                            <BaseIcon :name="fullScreenIcon" />
                        </button>
                        <button v-if="allowPip" @click="togglePiP">
                            <BaseIcon name="pip" />
                        </button>
                        <button :class="`volume-controls volume-controls-${volumeOrientation}`">
                            <BaseIcon :name="volumeIcon" @click.stop="toggleMute" class="relative z-10"/>
                            <div v-if="!isMobile" class="volume-controls-slider">
                                <Slider v-model="volume" :orientation="volumeOrientation" :step="0.01" :min="0" :max="1" @change="updateVolume" ref="volume" />
                            </div>
                        </button>
                    </div>
                </div>    
            </div>
            <div class="absolute inset-0 flex justify-center items-center w-full cursor-pointer z-10" @click="togglePlay" role="button">
                <div v-if="!isPlaying && !isPlayed" class="flex justify-center items-center w-12 h-12 bg-black-trans-6 text-white rounded-full">
                    <BaseIcon name="play_fill" class="leading-none"/>
                </div>
            </div>
        </template>
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
            default: false
        },
        allowFullScreen: {
            type: Boolean,
            default: true
        },
        allowPip: {
            type: Boolean,
            default: true
        },
        isContentWarning: {
            type: Boolean,
            default: false
        },
        volumeOrientation: {
            type: String,
            default: 'vertical'
        },
        muted: {
            type: Boolean,
            default: true
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
            isFullscreen: false,
            videoKey: uuidv4(),
            pipActive: false,
            observer: null,
            isPlayed: false
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
            return this.isPlaying ? 'pause' : 'play'
        },
        fullScreenIcon(){
            return this.isFullscreen ? 'fullscreen_exit' : 'fullscreen';
        },
        playerId(){
            return this.video.file.id + '_' + this.videoKey
        },
        allowAutoplay(){
            return this.user.video_auto_play && this.autoPlay && !this.isContentWarning
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
        time(){
            this.setCurrentPlayedTime(this.time)
        },
        isContentWarning(newVal){
            if(newVal){
                this.pause()
            } else {
                if (this.user.video_auto_play && this.autoPlay) {
                    this.play();
                }
            }
        },
        currentlyPlaying(newVal){
            if (newVal !== this.playerId) {
                this.pause();
            }
        },
        openedModalCount(newValue){
            if(newValue > 0){
                this.$refs.player?.pause();
            } else {
                this.handlePlayVisibleItem()
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
            this.isPlayed = true
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
                    this.isFullscreen = true
                } else {
                    this.isFullscreen = false
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
                this.handlePlayVisibleItem()
            }
        }
    },
    emits: ['in-viewport']
};
</script>
