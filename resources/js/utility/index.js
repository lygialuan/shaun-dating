import localData from "./localData"
import { useAppStore } from "../store/app";

export function setTitlePage(title) {
  document.title = window.siteConfig.siteName + ' | ' + title;
}

export function setAccessCode(code) {
  window.axios.defaults.headers.common['Access-Code'] = code;
}

export function getBase() {
  return window.siteConfig.siteUrl.replace(window.location.origin, '') || '/';
}

export function getAsset(path) {
  return window.siteConfig.cdn + '/' + path;
}

export function checkPopupBodyClass() {
  setTimeout(() => {
    if (document.querySelectorAll(".p-overlay-mask").length > 0) {
      document.body.classList.add('p-overflow-hidden');
    }
  },400);   
}

export function loadPageView(view) {
  return () => import(/* webpackChunkName: 'view-[request]' */ `../pages/${view}/index.vue`)
}

export function changeUrl(url, addToHistory = true) {
  const fullUrl = window.siteConfig.siteUrl + url;
  if (window.location.pathname !== url) {
    if (addToHistory) {
      history.pushState(null, '', fullUrl);
    } else {
      history.replaceState(null, '', fullUrl);
    }
  }
  useAppStore().setCurrentUrl(url)
}

export function isVisible(el, view) {
  const elRect = el.getBoundingClientRect();
  if (!view) return elRect.top >= 0 && elRect.bottom <= window.innerHeight;
  const viewRect = view.getBoundingClientRect();
  return elRect.top >= viewRect.top && elRect.bottom <= viewRect.bottom + 3;
}

export function checkOffline() {
  return (window.siteConfig.offline && localData.get('access_code', null) == null)
}

export function decodeHtml(html) {
  var txt = document.createElement("textarea");
  txt.innerHTML = html;
  return txt.value;
}

export function uuidv4() {
  return ([1e7]+1e3+4e3+8e3+1e11).replace(/[018]/g, c =>
    (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)
  );
}

export function checkAdvancedUpload(){
  var div = document.createElement('div');
  return (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div)) && 'FormData' in window && 'FileReader' in window;
}

export function checkMobileApp(){
  if (window.navigator.userAgent.search('Social') != -1) {
    return true
  }

  return false
}

export function checkAndroidWeb() {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera;
  if (/android/i.test(userAgent)) {
    return true
  }

  return false
}

export function checkiOSWeb() {
  var userAgent = navigator.userAgent || navigator.vendor || window.opera;
  if (/iPad|iPhone|iPod/.test(userAgent) && !window.MSStream) {
    return true
  }

  return false
}