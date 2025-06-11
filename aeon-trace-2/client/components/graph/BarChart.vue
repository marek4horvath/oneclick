<script setup lang="ts">
import { computed } from 'vue'
import { Bar } from 'vue-chartjs'
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Title,
    Tooltip,
} from 'chart.js'

const props = defineProps<{
    data: number[]
}>()

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const labels = ['0', '0', '0', '1', '1', '1', '1']
const secondRow = ['0', '4', '8', '2', '4', '6', '8']

const createGradient = (ctx: CanvasRenderingContext2D, chartArea: any) => {
    const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom)

    gradient.addColorStop(0, '#26A69A')
    gradient.addColorStop(1, 'rgba(38, 166, 154, 0.26)')

    return gradient
}

const chartData = computed(() => ({
    labels,
    datasets: [
        {
            label: 'Income',
            backgroundColor: (context: any) => {
                const { chart } = context
                const { ctx, chartArea } = chart
                if (!chartArea) {
                    return '#65C09E'
                }

                return createGradient(ctx, chartArea)
            },
            barThickness: 7,
            borderRadius: 10,
            data: props.data,
        },
    ],
}))

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: { enabled: false },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: {
                padding: 8,
                color: '#B0BBD5',
                font: { lineHeight: 1.8 },
                callback: (value: any, index: number) => [labels[index], secondRow[index]],
            },
            border: { display: false },
        },
        y: {
            display: false,
            grid: { display: false },
        },
    },
}
</script>

<template>
    <Bar
        :data="chartData"
        :options="chartOptions"
    />
</template>
