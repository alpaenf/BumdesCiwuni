<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const label = computed(() => {
    const labels = {
        lancar: 'Lancar',
        'perhatian-khusus': 'Perhatian Khusus',
        menunggak: 'Menunggak',
        'kredit-macet': 'Kredit Macet',
        lunas: 'Lunas',
    };

    return labels[props.status] || props.status;
});

const classes = computed(() => {
    const styles = {
        lancar: 'bg-blue-100 text-blue-700',
        'perhatian-khusus': 'bg-yellow-100 text-yellow-700',
        menunggak: 'bg-orange-100 text-orange-700',
        'kredit-macet': 'bg-red-100 text-red-700',
        lunas: 'bg-slate-100 text-slate-700',
    };

    return styles[props.status] || 'bg-slate-100 text-slate-700';
});
</script>

<template>
    <span
        class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
        :class="classes"
    >
        {{ label }}
    </span>
</template>
