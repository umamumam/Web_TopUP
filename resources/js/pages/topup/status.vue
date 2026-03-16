<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import axios from 'axios';
import { CheckCircle2, XCircle, Clock, Copy, Smartphone, MessageSquare, RefreshCw } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';
import { home } from '@/routes';

const props = defineProps<{
    order: any;
}>();

const isRefreshing = ref(false);
const orderData = ref({ ...props.order });

const refresh = async (isManual = false) => {
    if (isRefreshing.value && !isManual) {
        return;
    }
    
    if (isManual === true) {
        isRefreshing.value = true;
    }

    try {
        const response = await axios.get(`/topup/status/${orderData.value.reference_id}/api`);
        
        // Update reactive data
        orderData.value.status = response.data.status;
        orderData.value.payment_status = response.data.payment_status;
        orderData.value.sn = response.data.sn;
        orderData.value.nickname = response.data.nickname;

        // If finished, clear interval
        if (['success', 'failed'].includes(orderData.value.status) && orderData.value.payment_status === 'paid') {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }
    } catch (error) {
        console.error('Polling error:', error);
    } finally {
        if (isManual === true) {
            isRefreshing.value = false;
        }
    }
};

// Auto refresh every 3 seconds
let interval: any = null;
onMounted(() => {
    if (orderData.value.payment_status !== 'paid' || !['success', 'failed'].includes(orderData.value.status)) {
        interval = setInterval(() => {
            refresh(false);
        }, 3000);
    }
});

onUnmounted(() => {
    if (interval) {
        clearInterval(interval);
    }
});

const getStatusColor = (status: string) => {
    switch (status) {
        case 'success': return 'text-green-500';
        case 'failed': case 'failure': return 'text-red-500';
        case 'pending': return 'text-yellow-500';
        case 'processing': return 'text-blue-500';
        default: return 'text-slate-400';
    }
};

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'success': return CheckCircle2;
        case 'failed': case 'failure': return XCircle;
        case 'pending': return Clock;
        case 'processing': return Clock;
        default: return Clock;
    }
};

const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    alert('Berhasil disalin: ' + text);
};
</script>

<template>
    <Head title="Status Pesanan" />

    <div class="min-h-screen bg-slate-950 text-slate-50 py-12">
        <div class="max-w-2xl mx-auto px-4">
            
            <div class="bg-slate-900 rounded-3xl border border-slate-800 overflow-hidden">
                <!-- Status Header -->
                <div class="p-8 text-center border-b border-slate-800 bg-slate-800/50 relative">
                    <button 
                        @click="refresh(true)" 
                        :disabled="isRefreshing"
                        class="absolute top-6 right-6 p-3 bg-slate-800 hover:bg-slate-700 rounded-2xl transition-all active:scale-95 disabled:opacity-50 group"
                        title="Perbarui Status"
                    >
                        <RefreshCw class="w-5 h-5" :class="isRefreshing ? 'animate-spin text-indigo-500' : 'text-slate-400 group-hover:rotate-180 transition-transform duration-500'" />
                    </button>

                    <component :is="getStatusIcon(orderData.status)" class="w-20 h-20 mx-auto mb-6" :class="getStatusColor(orderData.status)" />
                    <h1 class="text-3xl font-extrabold uppercase tracking-tight mb-2">
                        Status: {{ orderData.status.toUpperCase() }}
                    </h1>
                    <p class="text-slate-400 font-medium">No. Pesanan: {{ orderData.reference_id }}</p>
                    
                    <div v-if="orderData.payment_status !== 'paid'" class="mt-4 px-4 py-2 bg-indigo-500/10 border border-indigo-500/20 rounded-xl inline-flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-indigo-400">Menunggu Pembayaran</span>
                    </div>
                </div>

                <!-- Transaction Details (Receipt Style) -->
                <div class="px-6 py-8 sm:px-10 space-y-8 bg-slate-900">
                    
                    <!-- Game Info Card -->
                    <div class="flex items-center gap-5 p-5 bg-gradient-to-r from-slate-800 to-slate-800/40 rounded-3xl border border-slate-700/50 shadow-inner">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-slate-900 rounded-2xl overflow-hidden flex-shrink-0 shadow-lg border border-slate-700">
                            <img :src="orderData.game.thumbnail" class="w-full h-full object-cover scale-110" />
                        </div>
                        <div>
                            <h3 class="font-black text-lg sm:text-xl uppercase tracking-tight text-slate-100">{{ orderData.game.name }}</h3>
                            <p class="text-indigo-400 text-[11px] sm:text-sm font-bold mt-1 tracking-widest uppercase">{{ orderData.product.name }}</p>
                        </div>
                    </div>

                    <!-- Receipt Data -->
                    <div class="space-y-4 rounded-3xl p-6 border border-slate-800 bg-slate-950/50 relative overflow-hidden">
                        <!-- Decorative dotted line on top/bottom -->
                        <div class="flex justify-between items-center py-3 border-b border-slate-800/80 border-dashed">
                            <span class="text-slate-500 text-[11px] sm:text-xs font-black uppercase tracking-widest">ID Tujuan</span>
                            <div class="flex items-center gap-3">
                                <span class="font-bold text-slate-200 text-sm">{{ orderData.target_id }}{{ orderData.server_id ? ' (' + orderData.server_id + ')' : '' }}</span>
                                <button @click="copyToClipboard(orderData.target_id + (orderData.server_id ? orderData.server_id : ''))" class="text-slate-500 hover:text-indigo-400 transition-colors p-1 bg-slate-800 rounded-md">
                                    <Copy class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                        
                        <div v-if="orderData.nickname" class="flex justify-between items-center py-3 border-b border-slate-800/80 border-dashed">
                            <span class="text-slate-500 text-[11px] sm:text-xs font-black uppercase tracking-widest">Nama Akun</span>
                            <span class="font-bold text-emerald-400 text-sm">{{ orderData.nickname }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-slate-800/80 border-dashed">
                            <span class="text-slate-500 text-[11px] sm:text-xs font-black uppercase tracking-widest">Metode Bayar</span>
                            <span class="font-black uppercase text-slate-200 text-sm flex items-center gap-2">
                                <span v-if="orderData.payment_method" class="w-2 h-2 rounded-full bg-indigo-500"></span>
                                {{ orderData.payment_method?.name || 'Menunggu API' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-3 border-b border-slate-800/80 border-dashed">
                            <span class="text-slate-500 text-[11px] sm:text-xs font-black uppercase tracking-widest">Status Bayar</span>
                            <span class="font-black uppercase px-3 py-1 rounded-lg text-[10px] tracking-widest" :class="orderData.payment_status === 'paid' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-400 border border-rose-500/20'">
                                {{ orderData.payment_status === 'paid' ? 'LUNAS' : orderData.payment_status }}
                            </span>
                        </div>
                        
                        <div v-if="orderData.sn" class="flex justify-between items-center py-3 border-b border-slate-800/80 border-dashed">
                            <span class="text-slate-500 text-[11px] sm:text-xs font-black uppercase tracking-widest">SN / Voucher</span>
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-indigo-400 font-bold text-sm">{{ orderData.sn }}</span>
                                <button @click="copyToClipboard(orderData.sn)" class="text-slate-500 hover:text-indigo-400 transition-colors p-1 bg-slate-800 rounded-md">
                                    <Copy class="w-3.5 h-3.5" />
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center pt-6 mt-2 relative">
                            <!-- Zigzag/ticket cutoff effect (pure css dots) -->
                            <div class="absolute -top-[1.2rem] -left-8 w-[calc(100%+4rem)] flex justify-between px-2 overflow-hidden opacity-20 pointer-events-none">
                                <div v-for="i in 30" :key="i" class="w-2 h-2 rounded-full bg-white"></div>
                            </div>

                            <span class="text-slate-400 font-black uppercase tracking-widest text-xs">Total Pembayaran</span>
                            <span class="text-2xl sm:text-3xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-purple-400 tracking-tighter">
                                Rp {{ parseFloat(orderData.total_amount).toLocaleString('id-ID') }}
                            </span>
                        </div>
                    </div>
                </div>

                    <!-- Footer Info -->
                    <div class="mt-8 pt-8 border-t border-slate-800 text-center">
                        <p class="text-slate-500 text-sm mb-6">
                            Jika pesanan belum masuk dalam 10 menit setelah status SUKSES, silahkan hubungi CS kami.
                        </p>
                        <div class="grid grid-cols-2 gap-4">
                            <Link 
                                :href="home.url()"
                                class="flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-700 text-white font-bold py-4 rounded-2xl transition-all"
                            >
                                <Smartphone class="w-5 h-5" />
                                <span>Beli Lagi</span>
                            </Link>
                            <a 
                                href="https://wa.me/6285799352991" 
                                target="_blank"
                                class="flex items-center justify-center gap-2 bg-green-600 hover:bg-green-500 text-white font-bold py-4 rounded-2xl transition-all shadow-lg shadow-green-900/20"
                            >
                                <MessageSquare class="w-5 h-5" />
                                <span>Hubungi CS</span>
                            </a>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</template>
