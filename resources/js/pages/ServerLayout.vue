<template>
    <Loading v-if="loading"/>
    <Error v-if="error">{{ error }}</Error>
    <FullColumn>  
        <template v-if="top.length > 0" v-slot:top>
            <component v-for="(dataTop, index) in top" :key="index" :is="loadComponentData(dataTop)" :data="dataTop" :params="$route.params" :position="'top'"></component>
		</template>
		<template v-if="center.length > 0" v-slot:center>
            <component v-for="(dataCenter, index) in center" :key="index" :is="loadComponentData(dataCenter)" :data="dataCenter" :params="$route.params" :position="'center'"></component>
		</template>
		<template v-if="right.length > 0" v-slot:right>
            <component v-for="(dataRight, index) in right" :key="index" :is="loadComponentData(dataRight)" :data="dataRight" :params="$route.params" :position="'right'"></component>
		</template>
        <template v-if="bottom.length > 0" v-slot:bottom>
            <component v-for="(dataBottom, index) in bottom" :key="index" :is="loadComponentData(dataBottom)" :data="dataBottom" :params="$route.params" :position="'bottom'"></component>
		</template>
	</FullColumn>
</template>
  
<script>
import { getDataServerLayout } from "@/api/utility";
import { setTitlePage, checkOffline } from '@/utility/index'
import { defineAsyncComponent } from "vue";
import Loading from "@/components/utilities/Loading.vue"
import Error from "@/components/utilities/Error.vue"
import FullColumn from '@/components/columns/FullColumn.vue'
import { useAppStore} from '../store/app'
import { mapState, mapActions } from 'pinia'

export default {
    components: { Loading, Error, FullColumn },
    data() {
        return {
            loading: true,
            error: null,
            destroy: false,
            top: [],
            center: [],
            right: [],
            bottom: [],
        };
    },
    async mounted() {
        if (checkOffline()) {
            this.center = [{
                component: "OfflinePage",
                data: [],
                params: [],
                type: "container"
            }]
            this.loading = false
        } else {
            let params = window._.merge({ router: this.$route.name, view_type: this.screen.md ? 'mobile' : 'desktop'}, this.$route.params);
            try {
                let data = await getDataServerLayout(params);
                if (! this.destroy) {
                    if (window._.has(data, "title")) {
                        setTitlePage(data.title);
                    }
                    this.top = data.contents.top
                    this.center = data.contents.center
                    this.right = data.contents.right
                    this.bottom = data.contents.bottom
                }
            }
            catch (e) {
                this.error = e.error.message;
                this.setErrorLayout(true)
            }
            finally {
                this.loading = false;
            }
        }
    },
    unmounted() {
        this.destroy = true
    },
    computed: {
        ...mapState(useAppStore, ['screen'])
    },
    methods:{
        ...mapActions(useAppStore, ['setErrorLayout']),
        loadComponentData(data){
            if(data.type == 'component') {
                return defineAsyncComponent(() => import(`../components/widgets/${data.package}/${data.component}.vue`))
            }else{
                return defineAsyncComponent(() => import(`../pages/${this.$route.name}/${data.component}.vue`))
            }
        }
    }
}
</script>