<template>
    <div class="flex items-center gap-base-2 -mb-2" :class="{ 'flex-row-reverse': !owner }">
        <div class="flex-1"></div>
        <ChatMessageAction v-if="room_info?.enable && room_info?.status === 'accepted'" :message="message"
            :owner="owner" />
        <div class="bg-reply-color border border-reply-color text-main-color whitespace-pre-wrap break-word text-xs p-base-1 rounded-xl dark:bg-dark-reply dark:border-white/10 dark:text-white ms-auto">
            <div class="flex gap-2 items-center">
                <button @click="togglePlayVoice" class="flex-shrink-0 bg-main-color w-5 h-5 flex items-center justify-center rounded-full dark:bg-white">
                    <BaseIcon :name="isPlayingVoice ? 'pause' : 'play'" size="10" class="text-white dark:text-main-color" />
                </button>
                <div class="audio-progress">
                    <input
                        type="range"
                        min="0"
                        max="100"
                        step="1"
                        :value="percentagePlayed.toFixed(1)"
                        @input="onSeek"
                        :style="{ direction: 'ltr' }"
                    />
                </div>
                <span class="min-w-10">{{ formattedRemainingTime }}</span>
            </div>
        </div>
    </div>
</template>

<script>
import { mapState, mapActions } from 'pinia'
import { useUtilitiesStore } from '@/store/utilities'
import ChatMessageAction from '@/pages/chat/ChatMessageAction.vue'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import { uuidv4 } from '@/utility'

export default {
    components: { ChatMessageAction, BaseIcon },
    props: ['message', 'owner', 'room_info'],
    data() {
        return {
            isPlayingVoice: false,
            totalDuration: 0,
            remainingTime: 0,
            percentagePlayed: 0,
            audioInstance: null,
            key: uuidv4()
        }
    },
    computed: {
        ...mapState(useUtilitiesStore, ['currentAudioPlaying']),
        audioFile() {
            return this.message.items[0].subject
        },
        formattedRemainingTime() {
            const minutes = Math.floor(this.remainingTime / 60);
            const seconds = this.remainingTime % 60 < 1 ? Math.ceil(this.remainingTime % 60) : Math.round(this.remainingTime % 60);
            return `${this.formatTime(minutes)}:${this.formatTime(seconds)}`;
        }
    },
    watch: {
        isPlayingVoice(value) {
            if (value) {
                this.setCurrentAudioPlaying(`audio_${this.audioFile.file.id}_${this.key}`);
            }
        },
        currentAudioPlaying(){
            if(this.currentAudioPlaying !== `audio_${this.audioFile.file.id}_${this.key}`){
                this.isPlayingVoice = false;
                this.audioInstance.pause();
            }
        }
    },
    mounted() {
        this.audioInstance = new Audio(this.audioFile.file.url);
        this.audioInstance.addEventListener('ended', this.handleAudioEnd);
        this.audioInstance.addEventListener('timeupdate', this.updateRemainingTime);
        this.audioInstance.addEventListener('loadedmetadata', this.updateRemainingTime);
        document.addEventListener('visibilitychange', this.handleVisibilityChange);
    },
    beforeDestroy() {
        if (this.audioInstance) {
            this.audioInstance = null;
            this.audioInstance.removeEventListener('ended', this.handleAudioEnd);
            this.audioInstance.removeEventListener('timeupdate', this.updateRemainingTime);
            this.audioInstance.removeEventListener('loadedmetadata', this.updateRemainingTime);
        }
        document.removeEventListener('visibilitychange', this.handleVisibilityChange);
    },
    methods: {
        ...mapActions(useUtilitiesStore, ['setCurrentAudioPlaying']),
        formatTime(time) {
            return time < 10 ? '0' + time : time;
        },
        togglePlayVoice() {
            this.isPlayingVoice = !this.isPlayingVoice;
            if (this.isPlayingVoice) {
                this.audioInstance.play();
            } else {
                this.audioInstance.pause();
            }
        },
        updateRemainingTime() {
            this.totalDuration = this.audioInstance.duration;
            this.remainingTime = this.audioInstance.duration - this.audioInstance.currentTime;
            this.percentagePlayed = (this.audioInstance.currentTime / this.audioInstance.duration) * 100;
        },
        handleAudioEnd() {
            this.isPlayingVoice = false;
        },
        handleVisibilityChange() {
            if (document.visibilityState === 'hidden' && this.isPlayingVoice) {
                this.audioInstance.pause();
                this.isPlayingVoice = false;
            }
        },
        onSeek(e) {
            const percentage = e.target.value;
            this.audioInstance.currentTime = (percentage / 100) * this.totalDuration;
        }
    }
}
</script>