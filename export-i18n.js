const VueI18NExtract = require('vue-i18n-extract');

VueI18NExtract.extractI18NItemsFromVueFiles

const report = VueI18NExtract.createI18NReport({
  vueFiles: './resources/js/**/*.?(js|vue)',
  languageFiles: './public/locales/install.json',
  add : true,
  separator: '***********',
  noEmptyTranslation: '*'
});