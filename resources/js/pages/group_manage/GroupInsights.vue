<template>
    <div v-if="overviewData" class="main-content-section">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Overview') }}</h3>
        <div class="mb-base-2">{{ $t('Last 28 days') }}</div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="globe" :cursor="false"/>
            <div class="flex-1">{{ $t('Engagement') }}</div>
            <div>{{ overviewData.engagement }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="users" :cursor="false"/>
            <div class="flex-1">{{ $t('New Members') }}</div>
            <div>{{ overviewData.member_new }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="check_circle" :cursor="false"/>
            <div class="flex-1">{{ $t('Active members') }}</div>
            <div>{{ overviewData.member_active }}</div>
        </div>
    </div>
    <template v-if="chartData">
        <div class="main-content-section">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Members') }}</h3>
            <div class="mb-base-2">{{ $t('Last 7 days') }}</div>
            <div>
                <Chart type="line" :data="memberChartData" :options="chartOptions"  />
            </div>
        </div>
        <div class="main-content-section">
            <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Posts') }}</h3>
            <div class="mb-base-2">{{ $t('Last 7 days') }}</div>
            <div>
                <Chart type="line" :data="postChartData" :options="chartOptions"  />
            </div>
        </div>
    </template>
</template>

<script>
import { mapState } from 'pinia'
import { getGroupOverview, getReportGroupChart } from '@/api/group'
import { useAuthStore } from '@/store/auth'
import { useAppStore } from '@/store/app'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Chart from 'primevue/chart'

export default {
    props: ['adminConfig'],
    components: { BaseIcon, Chart },
    data(){
        return {
            overviewData: null,
            chartData: null,
            memberChartData: null,
            postChartData: null,
            chartOptions: null
        }
    },
    computed: {
		...mapState(useAuthStore, ['user']),
        ...mapState(useAppStore, ['darkmode', 'systemMode']),
        isDarkMode(){
            return this.darkmode === 'on' || (this.darkmode === 'auto' && this.systemMode === 'dark');
        },
        tickColor(){
            return this.isDarkMode ? '#fff' : '#333'
        },
        surfaceBorder(){
            return this.isDarkMode ? '#ffffff26' : '#e0e0e0'
        }
    },
    watch: {
        darkmode: 'setChartOptions',
        systemMode: 'setChartOptions'
    },
    mounted(){
        this.handleGetGroupOverview(this.adminConfig.group.id)
        this.handleGetChartData(this.adminConfig.group.id)
    },
    methods: {
        async handleGetGroupOverview(groupId){
            try {
                const response = await getGroupOverview(groupId)
                this.overviewData = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async handleGetChartData(groupId){
            try {
                const response = await getReportGroupChart(groupId)
                this.chartData = response
                this.setChartMembersData()
                this.setChartPostsData()
                this.setChartOptions()
            } catch (error) {
                this.showError(error.error)
            }
        },
        setChartMembersData(){
            this.memberChartData =  {
                labels: this.chartData.member.label,
                datasets: [
                    {
                        label: this.$t('Members'),
                        data: this.chartData.member.data,
                        fill: false,
                        borderColor: '#396AFF',
                        tension: 0.4
                    }
                ]
            }
        },
        setChartPostsData(){
            this.postChartData =  {
                labels: this.chartData.post.label,
                datasets: [
                    {
                        label: this.$t('Posts'),
                        data: this.chartData.post.data,
                        fill: false,
                        borderColor: '#396AFF',
                        tension: 0.4
                    }
                ]
            }
        },
        setChartOptions ()  {
            const surfaceBorder = this.surfaceBorder;

            this.chartOptions = {
                plugins:{
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        ticks: {
                            color: this.tickColor
                        },
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        ticks: {
                            color: this.tickColor,
                            stepSize: 1
                        },
                        grid: {
                            color: surfaceBorder
                        }
                    }
                }
            }
        }
    }
}
</script>