<template>
    <div class="main-content-section">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Page overview') }}</h3>
        <div class="mb-base-2">{{ $t('Last 28 days') }}</div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="globe" :cursor="false"/>
            <div class="flex-1">{{ $t('Post Reach') }}</div>
            <div>{{ dataReportOverview?.post_reach }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="users" :cursor="false"/>
            <div class="flex-1">{{ $t('Post Engagement') }}</div>
            <div>{{ dataReportOverview?.post_engagement }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="check_circle" :cursor="false"/>
            <div class="flex-1">{{ $t('New Followers') }}</div>
            <div>{{ dataReportOverview?.follow }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="comment" :cursor="false"/>
            <div class="flex-1">{{ $t('Comment') }}</div>
            <div>{{ dataReportOverview?.post_comment }}</div>
        </div>
        <div class="flex items-center gap-base-2 py-base-2 border-t border-divider dark:border-white/10">
            <BaseIcon name="share" :cursor="false"/>
            <div class="flex-1">{{ $t('Share') }}</div>
            <div>{{ dataReportOverview?.post_share }}</div>
        </div>
    </div>
    <div class="main-content-section">
        <h3 class="text-main-color text-base-lg font-extrabold dark:text-white mb-base-2">{{ $t('Audience') }}</h3>
        <div class="mb-base-2">{{ $t('These values are based on total followers of your Page or profile.') }}</div>
        <div v-if="dataReportAudience">
            <Chart type="bar" :data="chartAgeData" :options="chartAgeOptions" class="h-64 mb-12"  />
            <Chart type="pie" :data="chartGenderData" :options="chartGenderOptions" class="w-72 mx-auto"  />
        </div>
    </div>
</template>

<script>
import { getReportOverviewPage, getReportAudiencePage } from '@/api/page'
import BaseIcon from '@/components/icons/BaseIcon.vue'
import Chart from 'primevue/chart'
import { useAppStore } from '@/store/app'
import { useAuthStore } from '@/store/auth'
import { mapState, mapActions } from 'pinia'

export default {
    components: { BaseIcon, Chart },
    data(){
        return {
            dataReportOverview: null,
            dataReportAudience: null,
            chartAgeData: null,
            chartAgeOptions: null,
            chartGenderData: null,
            chartGenderOptions: null
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
        darkmode: 'setAgeChartOptions',
        systemMode: 'setAgeChartOptions'
    },
    mounted(){
        if (! this.user.is_page) {
            this.setErrorLayout(true)
        } else {
            this.getReportOverview()
            this.getReportAudience()
        }
    },
    methods: {
        ...mapActions(useAppStore, ['setErrorLayout']),
        async getReportOverview(){
            try {
                const response = await getReportOverviewPage()
                this.dataReportOverview = response
            } catch (error) {
                this.showError(error.error)
            }
        },
        async getReportAudience(){
            try {
                const response = await getReportAudiencePage()
                this.dataReportAudience = response
                this.setAgeChartData();
                this.setAgeChartOptions();
                this.setGenderChartData();
                this.setGenderChartOptions();
            } catch (error) {
                this.showError(error.error)
            }
        },
        getPercentGenderGroupByAge(name){
            return window._.flatMap(this.dataReportAudience.ages, ageGroup =>
                window._.map(window._.filter(ageGroup, { 'name': name }), 'percent')
            );
        },
        setAgeChartData() {
            const agesList = window._.keys(this.dataReportAudience.ages)
            const dataCols = this.dataReportAudience.genders.map((data) => {
                return {
                    label: data.name,
                    backgroundColor: data.color,
                    data: this.getPercentGenderGroupByAge(data.name)
                }
            })
            this.chartAgeData = {
                labels: agesList,
                datasets: dataCols
            };
        },
        setAgeChartOptions ()  {
            const surfaceBorder = this.surfaceBorder;

            this.chartAgeOptions = {
                plugins:{
                    legend: {
                        labels: {
                            color: this.tickColor
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';

                                if (label) {
                                    label += ': ';
                                }
                                if (context.parsed.y !== null) {
                                    label += context.parsed.y + '%';
                                }
                                return ' ' + label;
                            }
                        }
                    }
                },
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: {
                            color: this.tickColor,
                            font: {
                                size: 15
                            }
                        },
                        grid: {
                            display: false,
                        }
                    },
                    y: {
                        grid: {
                            color: surfaceBorder,
                            drawBorder: false
                        },
                        suggestedMin: 0,
                        suggestedMax: 100,
                        ticks: {
                            color: this.tickColor,
                            font: {
                                size: 15
                            },
                            stepSize: 20,
                            callback: function(label) {
                                return label + '%'
                            }
                        }
                    }
                }
            }
        },
        setGenderChartData() {
            const gendersList = this.dataReportAudience.follows.map(data => data.name)
            const percentByGender = this.dataReportAudience.follows.map(data => data.percent)
            const colorByGender = this.dataReportAudience.follows.map(data => data.color)

            this.chartGenderData = {
                labels: gendersList,
                datasets: [{
                    data: percentByGender,
                    backgroundColor: colorByGender
                }]
            };
        },
        setGenderChartOptions ()  {
            this.chartGenderOptions = {
                plugins:{
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        }
    }
}
</script>