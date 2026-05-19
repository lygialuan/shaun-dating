<template>
    {{ originalText }}
    ==
    {{ translatedText }}
    <div @click="translateText(originalText, 'fr')">translate</div>
</template>
<script>

export default {
  data() {
    return {
      originalText: 'Ada juga dalam Google PlayStore',
      translatedText: '',
      translated: false,
    }
  },
  methods: {
    async translateText(text, targetLang) {
        const response = await fetch(`https://translation-api-url?text=${text}&targetLang=${targetLang}`);
        const data = await response.json();
        this.translatedText =  data.translatedText;
    }
  }
}
</script>
