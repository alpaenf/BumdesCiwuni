<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { Chart, ArcElement, Tooltip, Legend, DoughnutController } from 'chart.js';

Chart.register(ArcElement, Tooltip, Legend, DoughnutController);

const props = defineProps({
    data: {
        type: Object,
        required: true,
        default: () => ({}),
    },
});

const canvasRef = ref(null);
let chartInstance = null;

const formatRupiah = (val) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(val || 0);
};

const buildChart = () => {
    if (!canvasRef.value) return;

    if (chartInstance) {
        chartInstance.destroy();
    }

    const lp = Number(props.data?.laku_pandai?.pendapatan || 0);
    const samsat = Number(props.data?.samsat?.pendapatan || 0);
    const pbb = Number(props.data?.pbb?.pendapatan || 0);
    const umum = Number(props.data?.umum?.pendapatan || 0);

    const total = lp + samsat + pbb + umum;
    const isZero = total === 0;

    chartInstance = new Chart(canvasRef.value, {
        type: 'doughnut',
        data: {
            labels: ['Laku Pandai', 'Samsat Budiman', 'Pajak PBB', 'Transaksi Umum'],
            datasets: [
                {
                    data: isZero ? [25, 25, 25, 25] : [lp, samsat, pbb, umum],
                    backgroundColor: isZero
                        ? ['#F1F5F9', '#E2E8F0', '#CBD5E1', '#94A3B8']
                        : ['#2563EB', '#F59E0B', '#10B981', '#6366F1'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: isZero ? 0 : 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    enabled: !isZero,
                    callbacks: {
                        label: function (context) {
                            const val = context.raw || 0;
                            const pct = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '0%';
                            return ` ${context.label}: ${formatRupiah(val)} (${pct})`;
                        },
                    },
                },
            },
            cutout: '72%',
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
    <div class="relative w-full h-44 flex items-center justify-center">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>

