<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart, ArcElement, Tooltip, Legend, DoughnutController } from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend, DoughnutController);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const buildChart = () => {
    if (!canvasRef.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    chartInstance = new Chart(canvasRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Aktif', 'Lunas', 'Menunggak', 'Kredit Macet'],
            datasets: [
                {
                    data: [
                        props.data.aktif || 0,
                        props.data.lunas || 0,
                        props.data.menunggak || 0,
                        props.data.kredit_macet || 0,
                    ],
                    backgroundColor: ['#10B981', '#64748B', '#F97316', '#EF4444'],
                    borderWidth: 0,
                },
            ],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 10,
                        boxHeight: 10,
                        color: '#1E293B',
                        font: {
                            family: 'Poppins',
                            size: 12,
                        },
                    },
                },
            },
            cutout: '65%',
        },
    });
};

onMounted(buildChart);

watch(
    () => props.data,
    () => {
        buildChart();
    },
    { deep: true },
);

onBeforeUnmount(() => {
    if (chartInstance) {
        chartInstance.destroy();
    }
});
</script>

<template>
    <div class="relative">
        <canvas ref="canvasRef" class="max-h-72"></canvas>
    </div>
</template>
