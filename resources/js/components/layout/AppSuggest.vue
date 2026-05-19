<template>
    <div v-if="!appSuggestHide && config && appLink" class="app-suggest fixed bg-white inset-x-0 bottom-0 z-[999] p-4 dark:bg-slate-800 dark:text-white">
        <div class="max-w-sm mx-auto">
			<div class="flex gap-3 mb-3 items-start">
				<img :src="config.appSuggestPhoto" class="app-suggest-image w-24" alt="">
				<div class="app-suggest-content">
					<div class="app-suggest-content-title text-lg font-bold">{{ $t('Check out our mobile apps!') }}</div>
					<div class="app-suggest-content-desc">{{ $t('Our "White label" Android and iOS apps are available on App Stores. Click to install and try out on your phone.') }}</div>
				</div>
			</div>
			<div class="app-suggest-buttons flex justify-center gap-base-2">
				<BaseButton type="outlined" @click="denyAppSuggest">
					{{ $t('No Thanks') }}
				</BaseButton>
				<BaseButton :href="appLink">
					{{ $t('Get The App') }}
				</BaseButton>
			</div>
		</div>
	</div>
</template>

<script>
import { mapState } from 'pinia'
import { useAppStore } from '@/store/app'
import { checkAndroidWeb, checkiOSWeb } from '@/utility/index'
import localData from '@/utility/localData';
import BaseButton from '@/components/inputs/BaseButton.vue'

export default {
    components: { BaseButton },
    data() {
        return {
            appSuggestHide: localData.get('app_suggest_hide', false)
        }
    },
    computed: {
        ...mapState(useAppStore, ['config']),
        appLink() {
			if (checkAndroidWeb()) {
				return this.config.androidLink;
			}
			if (checkiOSWeb()) {
				return this.config.iosLink;
			}
			return null;
		}
    },
    methods: {
        denyAppSuggest() {
			this.appSuggestHide = true;
			localData.set('app_suggest_hide', true)
		}
    },
};
</script>