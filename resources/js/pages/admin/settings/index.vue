<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Settings as SettingsIcon, Save, Info, CreditCard, Zap } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    settings: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Settings', href: '/admin/settings' },
];

const form = useForm({
    profit_margin_type: props.settings.profit_margin_type || 'flat',
    profit_margin_value: props.settings.profit_margin_value || 2000,
    midtrans_server_key: props.settings.midtrans_server_key || '',
    midtrans_client_key: props.settings.midtrans_client_key || '',
    midtrans_status: props.settings.midtrans_status || 'sandbox',
    digiflazz_username: props.settings.digiflazz_username || '',
    digiflazz_api_key: props.settings.digiflazz_api_key || '',
    digiflazz_webhook_secret: props.settings.digiflazz_webhook_secret || '',
    digiflazz_mode: props.settings.digiflazz_mode || 'development',
});

const submit = () => {
    form.post('/admin/settings', {
        preserveScroll: true,
        onSuccess: () => alert('Pengaturan disimpan!'),
    });
};
</script>

<template>
    <Head title="Pengaturan Sistem" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8 pb-20">
            <div class="flex items-center gap-4 mb-8">
                <div class="p-4 bg-indigo-500 rounded-3xl shadow-lg shadow-indigo-500/20">
                    <SettingsIcon class="w-8 h-8 text-white" />
                </div>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight">Pengaturan Sistem</h1>
                    <p class="text-slate-500 font-medium">Atur keuntungan dan konfigurasi API website anda.</p>
                </div>
            </div>

            <form @submit.prevent="submit" class="space-y-8">
                <!-- Profit Margin Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="p-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <Save class="w-5 h-5 text-indigo-500" />
                            Management Profit (Keuntungan)
                        </h2>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Margin Type -->
                            <div class="space-y-4">
                                <label class="block text-sm font-bold uppercase tracking-wider text-slate-500">Tipe Keuntungan</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <button 
                                        type="button"
                                        @click="form.profit_margin_type = 'flat'"
                                        class="p-4 rounded-2xl border-2 transition-all text-center font-bold"
                                        :class="form.profit_margin_type === 'flat' ? 'border-indigo-500 bg-indigo-500/5 text-indigo-500' : 'border-slate-200 dark:border-slate-800'"
                                    >
                                        Flat (Rp)
                                    </button>
                                    <button 
                                        type="button"
                                        @click="form.profit_margin_type = 'percent'"
                                        class="p-4 rounded-2xl border-2 transition-all text-center font-bold"
                                        :class="form.profit_margin_type === 'percent' ? 'border-indigo-500 bg-indigo-500/5 text-indigo-500' : 'border-slate-200 dark:border-slate-800'"
                                    >
                                        Persentase (%)
                                    </button>
                                </div>
                            </div>

                            <!-- Margin Value -->
                            <div class="space-y-4">
                                <label class="block text-sm font-bold uppercase tracking-wider text-slate-500">Nilai Keuntungan</label>
                                <div class="relative">
                                    <span v-if="form.profit_margin_type === 'flat'" class="absolute left-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">Rp</span>
                                    <input 
                                        v-model="form.profit_margin_value"
                                        type="number" 
                                        class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-12 py-4 focus:border-indigo-500 outline-none transition-all font-black text-xl"
                                    />
                                    <span v-if="form.profit_margin_type === 'percent'" class="absolute right-4 top-1/2 -translate-y-1/2 font-bold text-slate-400">%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="flex gap-4 p-6 bg-blue-500/5 border border-blue-500/20 rounded-2xl text-sm leading-relaxed">
                            <Info class="w-6 h-6 text-blue-500 flex-shrink-0" />
                            <p class="text-blue-600 dark:text-blue-400">
                                <strong>Penting:</strong> Perubahan keuntungan hanya akan berlaku pada produk baru yang disinkronkan atau saat Anda menekan tombol "Sinkronisasi" ulang di menu Produk. 
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Midtrans API Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="p-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <CreditCard class="w-5 h-5 text-blue-500" />
                            Konfigurasi Midtrans
                        </h2>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Server Key</label>
                                <input 
                                    v-model="form.midtrans_server_key"
                                    type="password" 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-blue-500 outline-none transition-all font-medium"
                                    placeholder="Midtrans Server Key"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Client Key</label>
                                <input 
                                    v-model="form.midtrans_client_key"
                                    type="text" 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-blue-500 outline-none transition-all font-medium"
                                    placeholder="Midtrans Client Key"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Environment</label>
                                <select 
                                    v-model="form.midtrans_status"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-blue-500 outline-none transition-all font-bold"
                                >
                                    <option value="sandbox">Sandbox (Testing)</option>
                                    <option value="production">Production (Live)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Digiflazz API Card -->
                <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <div class="p-8 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <Zap class="w-5 h-5 text-amber-500" />
                            Konfigurasi Digiflazz
                        </h2>
                    </div>
                    
                    <div class="p-8 space-y-6">
                        <div class="grid grid-cols-1 gap-6">
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Username</label>
                                <input 
                                    v-model="form.digiflazz_username"
                                    type="text" 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-amber-500 outline-none transition-all font-medium"
                                    placeholder="Digiflazz Username"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">API Key</label>
                                <input 
                                    v-model="form.digiflazz_api_key"
                                    type="password" 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-amber-500 outline-none transition-all font-medium"
                                    placeholder="Digiflazz API Key"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Webhook Secret</label>
                                <input 
                                    v-model="form.digiflazz_webhook_secret"
                                    type="password" 
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-amber-500 outline-none transition-all font-medium"
                                    placeholder="Digiflazz Webhook Secret"
                                />
                            </div>
                            <div class="space-y-2">
                                <label class="text-sm font-bold text-slate-500 uppercase">Mode</label>
                                <select 
                                    v-model="form.digiflazz_mode"
                                    class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-6 py-4 focus:border-amber-500 outline-none transition-all font-bold"
                                >
                                    <option value="development">Development (Testing)</option>
                                    <option value="production">Production (Live)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="fixed bottom-8 right-8 z-50">
                    <button 
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-500 text-white font-extrabold px-10 py-4 rounded-2xl shadow-2xl shadow-indigo-500/40 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-3"
                    >
                        <Save v-if="!form.processing" class="w-5 h-5" />
                        <span v-else class="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin"></span>
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Semua Pengaturan' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
