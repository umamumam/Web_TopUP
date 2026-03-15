<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ShoppingCart, Search, Clock, CheckCircle2, XCircle, Trash2 } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    orders: any;
    filters: any;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Pesanan', href: '/admin/orders' },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};

const deleteOrder = (orderId: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        router.delete(`/admin/orders/${orderId}`);
    }
};
</script>

<template>
    <Head title="Semua Pesanan" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <div class="flex items-center gap-4">
                <div class="p-4 bg-emerald-500 rounded-3xl shadow-lg shadow-emerald-500/20 text-white">
                    <ShoppingCart class="w-8 h-8" />
                </div>
                <div>
                    <h1 class="text-3xl font-black uppercase tracking-tight">Data Pesanan</h1>
                    <p class="text-slate-500 font-medium">Rekapitulasi semua transaksi yang masuk.</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" />
                    <input 
                        type="text" 
                        placeholder="Cari ID Transaksi, User ID, atau No. WhatsApp..."
                        class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl pl-12 pr-5 py-3 outline-none focus:border-emerald-500 transition-all font-medium"
                    />
                </div>
            </div>

            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">ID / Tanggal</th>
                                <th class="px-6 py-4">Target / Whatsapp</th>
                                <th class="px-6 py-4">Item</th>
                                <th class="px-6 py-4">Pembayaran</th>
                                <th class="px-6 py-4">Proses Provider</th>
                                <th class="px-6 py-4">Nominal</th>
                                <th class="px-6 py-4">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="order in orders.data" :key="order.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                <td class="px-6 py-4">
                                    <div class="font-black text-sm">{{ order.reference_id }}</div>
                                    <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mt-1">
                                        {{ new Date(order.created_at).toLocaleString('id-ID') }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-sm">{{ order.target_id }} {{ order.server_id ? '(' + order.server_id + ')' : '' }}</div>
                                    <div class="text-xs text-slate-500">{{ order.whatsapp_number }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-xs uppercase">{{ order.game.name }}</div>
                                    <div class="text-xs text-indigo-500 font-medium">{{ order.product.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-black uppercase" :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-600 dark:bg-green-500/10' : 'bg-red-100 text-red-600 dark:bg-red-500/10'">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-xs font-bold uppercase">
                                        <Clock v-if="order.status === 'pending'" class="w-4 h-4 text-yellow-500" />
                                        <Clock v-else-if="order.status === 'processing'" class="w-4 h-4 text-blue-500 animate-pulse" />
                                        <CheckCircle2 v-else-if="order.status === 'success'" class="w-4 h-4 text-green-500" />
                                        <XCircle v-else-if="order.status === 'failed'" class="w-4 h-4 text-red-500" />
                                        {{ order.status }}
                                    </div>
                                    <div v-if="order.sn" class="text-[10px] font-mono text-slate-400 mt-1">SN: {{ order.sn }}</div>
                                </td>
                                <td class="px-6 py-4 font-black whitespace-nowrap">{{ formatCurrency(order.total_amount) }}</td>
                                <td class="px-6 py-4">
                                    <button 
                                        @click="deleteOrder(order.id)"
                                        class="p-2 bg-red-100 text-red-600 hover:bg-red-200 dark:bg-red-500/10 dark:text-red-500 dark:hover:bg-red-500/20 rounded-xl transition-colors"
                                        title="Hapus Transaksi"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-between items-center bg-white dark:bg-slate-900 p-6 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-sm font-medium text-slate-500">
                    Total {{ orders.total }} pesanan ditemukan.
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in orders.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        v-html="link.label"
                        class="px-4 py-2 rounded-xl text-sm font-bold border transition-all"
                        :class="[
                            link.active ? 'bg-emerald-600 border-emerald-600 text-white shadow-lg' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-50',
                            !link.url ? 'opacity-50 cursor-not-allowed' : ''
                        ]"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
