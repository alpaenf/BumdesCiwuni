<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});

const classes = computed(() =>
    props.active
    ? 'bg-[color:var(--color-surface-container-high)] text-[color:var(--color-primary)] font-semibold'
    : 'text-[color:var(--color-secondary)] hover:bg-[color:var(--color-surface-container)]',
);
</script>

<template>
    <Link :href="href" :class="classes">
        <slot />
    </Link>
</template>
