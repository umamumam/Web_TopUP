<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { home } from '@/routes';

defineProps<{
    title?: string;
    description?: string;
}>();

const page = usePage();
const settings = computed(() => (page.props as any).site_settings || {});
</script>

<template>
    <div
        class="relative flex min-h-svh flex-col items-center justify-center gap-6 bg-slate-950 p-6 md:p-10 overflow-hidden"
    >
        <!-- Background Decorations -->
        <div class="absolute inset-0 z-0">
            <img 
                src="/images/auth_bg.png" 
                class="w-full h-full object-cover opacity-30 grayscale brightness-50" 
                alt="Background"
            />
            <div class="absolute inset-0 bg-gradient-to-tr from-slate-950 via-slate-950/80 to-indigo-900/40"></div>
            
            <!-- Animated Blobs -->
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] bg-indigo-600/20 blur-[120px] rounded-full animate-pulse"></div>
            <div class="absolute -bottom-[10%] -right-[10%] w-[40%] h-[40%] bg-purple-600/20 blur-[120px] rounded-full animate-pulse" style="animation-delay: 2s"></div>
        </div>

        <div class="relative z-10 w-full max-w-md">
            <!-- Glass Card -->
            <div class="bg-white/5 backdrop-blur-2xl border border-white/10 rounded-[2.5rem] p-8 md:p-10 shadow-2xl relative overflow-hidden">
                <!-- Subtle Glow Top -->
                <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-indigo-500/50 to-transparent"></div>
                
                <div class="flex flex-col gap-10">
                    <div class="flex flex-col items-center gap-6">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-4 group"
                        >
                            <div
                                class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white/10 border border-white/20 shadow-xl group-hover:scale-105 transition-transform duration-500 overflow-hidden"
                            >
                                <img 
                                    v-if="settings.site_logo" 
                                    :src="'/storage/' + settings.site_logo" 
                                    class="h-full w-full object-contain p-2" 
                                />
                                <div v-else class="text-4xl">🎮</div>
                            </div>
                            <div class="text-center space-y-1">
                                <span class="text-2xl font-black tracking-tighter text-white uppercase block leading-none">
                                    {{ settings.site_name || 'VEINSTORE' }}
                                </span>
                                <span class="text-[10px] font-black uppercase tracking-[0.3em] text-indigo-400">Premium TopUp Store</span>
                            </div>
                        </Link>

                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-black text-white uppercase tracking-tight">{{ title }}</h1>
                            <p class="text-sm font-medium text-slate-400 leading-relaxed px-4">
                                {{ description }}
                            </p>
                        </div>
                    </div>

                    <!-- Slot for Form -->
                    <div class="relative">
                        <slot />
                    </div>
                </div>
            </div>
            
            <!-- Footer Label -->
            <p class="text-center mt-8 text-[10px] font-black uppercase tracking-[0.4em] text-slate-600">
                &copy; {{ new Date().getFullYear() }} {{ settings.site_name || 'VEINSTORE' }} &bull; All Rights Reserved
            </p>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse {
    0%, 100% { opacity: 0.2; transform: scale(1); }
    50% { opacity: 0.4; transform: scale(1.1); }
}
.animate-pulse {
    animation: pulse 8s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
