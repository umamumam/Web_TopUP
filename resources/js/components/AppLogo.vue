<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';

const page = usePage();
const settings = computed(() => (page.props as any).site_settings || {});
</script>

<template>
    <div
        class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary text-sidebar-primary-foreground overflow-hidden"
    >
        <img v-if="settings.site_logo" :src="'/storage/' + settings.site_logo" class="size-full object-contain p-0.5" />
        <AppLogoIcon v-else class="size-5 fill-current text-white dark:text-black" />
    </div>
    <div class="ml-1 grid flex-1 text-left text-sm">
        <span class="mb-0.5 truncate leading-tight font-black uppercase tracking-tighter"
            >{{ settings.site_name || 'VEINSTORE' }}</span
        >
    </div>
</template>
