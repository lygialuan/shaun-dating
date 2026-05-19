<template>
    <div>
        <BaseSuggest v-model="selectedHashtags" multiple optionLabel="name" :suggestions="hashtagsList" @complete="suggestHashtagsList" :emptySearchMessage="$t('No hashtags found')">
            <template #option="{ option }">
                <div>{{ option.name }}</div>
            </template>
        </BaseSuggest>
        <small v-if="error" class="p-error">{{error}}</small>
    </div>
</template>

<script>
import { getMentionHashtagsList } from '@/api/hashtag'
import BaseSuggest from '@/components/inputs/BaseSuggest.vue'

export default {
    components: { BaseSuggest },
    props:{
        modelValue: {
			type: Array,
			default: () => []
		},
        error: {
			type: String,
			default: null
		}
    },
    data(){
        return{
            hashtagsList: [],
            selectedHashtags: this.modelValue,
        }
    },
    watch:{
        selectedHashtags(newValue){
            this.$emit('update:modelValue', newValue);
        }
    },
    methods: {
        async suggestHashtagsList(event) {
            try {
				const response = await getMentionHashtagsList(event.query, 1);
                this.hashtagsList = response;
			} catch (error) {
                this.hashtagsList = [];
			}  
        },
    }
}
</script>