<template>
    <div v-if="enableDarkmode" class="flex flex-col sm:flex-row flex-wrap gap-x-5 gap-y-2 mb-5"> 
        <div class="flex-1">{{$t('Appearance')}}</div>
        <div class="flex-2">
            <div class="space-y-3">                             
                <div class="flex items-center gap-base-2">
                    <BaseRadio v-model="appearance" inputId="off" name="darkmode" value="off" @change="toggleDarkmode(appearance)" />
                    <label for="off">{{ $t('Light') }}</label>
                </div>
                <div class="flex items-center gap-base-2">
                    <BaseRadio v-model="appearance" inputId="on" name="darkmode" value="on" @change="toggleDarkmode(appearance)" />
                    <label for="on">{{ $t('Dark') }}</label>
                </div>
                <div class="flex items-center gap-base-2">
                    <BaseRadio v-model="appearance" inputId="auto" name="darkmode" value="auto" @change="toggleDarkmode(appearance)" />
                    <label for="auto">{{ $t('System') }}</label>
                </div>
            </div>
        </div>  
    </div>
    <!-- <div class="flex flex-col sm:flex-row flex-wrap gap-x-5 gap-y-2"> 
        <div class="flex-1">{{$t('Video autoplay')}}</div>
        <div class="flex-2">
            <BaseSwitch v-model="video_auto_play" @click="toggleVideoAutoPlay(video_auto_play)"/> 
        </div>  
    </div> -->
</template>

<script>
import { mapState, mapActions } from 'pinia';
import { storeDarkmode, storeVideoAutoPlay } from '@/api/user'
import { useAppStore } from '@/store/app';
import { useAuthStore } from '@/store/auth';
import BaseRadio from '@/components/inputs/BaseRadio.vue'
import Constant from '@/utility/constant'

export default {
    components: {BaseRadio},
    data() {
        return {
            loading_status : true,
            video_auto_play : false,
            appearance: null,
            enableDarkmode: Constant.ENABLE_DARKMODE
        }
    },
    mounted() {
        this.video_auto_play = !!(this.user.video_auto_play)
        this.appearance = this.darkmode
    },
    watch:{
        darkmode(){
            this.appearance = this.darkmode
        }
    },
    computed:{
        ...mapState(useAppStore, ['darkmode']),
        ...mapState(useAuthStore, ['user'])
    },
    methods:{
        ...mapActions(useAppStore, ['setDarkmode']),
        ...mapActions(useAuthStore, ['setVideoAutoPlay']),
        async toggleDarkmode(status){
            try {
                await storeDarkmode(status)
                this.setDarkmode(status);
            } catch (error) {
                this.showError(error.error)
            }
        },
        async toggleVideoAutoPlay(status) {
            try {
                if(status){
                    await storeVideoAutoPlay({enable: 0})
                    this.setVideoAutoPlay(false);
                }else{
                    await storeVideoAutoPlay({enable: 1})
                    this.setVideoAutoPlay(true);
                }
            } catch (error) {
                this.showError(error.error)
            }
        }
    }
}
</script>

<style>

</style>