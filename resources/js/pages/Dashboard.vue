<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { 
    TrendingUp, 
    ShoppingCart, 
    Gamepad2, 
    Package, 
    Clock, 
    CheckCircle2 
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    stats: {
        total_sales: number;
        total_orders: number;
        pending_orders: number;
        success_orders: number;
        total_games: number;
        total_products: number;
    };
    recentOrders: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Revenue -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-500/10 rounded-2xl">
                            <TrendingUp class="w-6 h-6 text-green-500" />
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Pendapatan</div>
                    <div class="text-2xl font-black mt-1">{{ formatCurrency(stats.total_sales) }}</div>
                </div>

                <!-- Orders -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-indigo-500/10 rounded-2xl">
                            <ShoppingCart class="w-6 h-6 text-indigo-500" />
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Pesanan</div>
                    <div class="text-2xl font-black mt-1">{{ stats.total_orders }}</div>
                </div>

                <!-- Games -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-500/10 rounded-2xl">
                            <Gamepad2 class="w-6 h-6 text-purple-500" />
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Game</div>
                    <div class="text-2xl font-black mt-1">{{ stats.total_games }}</div>
                </div>

                <!-- Products -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-500/10 rounded-2xl">
                            <Package class="w-6 h-6 text-blue-500" />
                        </div>
                    </div>
                    <div class="text-slate-500 text-sm font-medium uppercase tracking-wider">Total Produk</div>
                    <div class="text-2xl font-black mt-1">{{ stats.total_products }}</div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-6 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                    <h2 class="text-xl font-bold uppercase tracking-tight">Pesanan Terbaru</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-xs uppercase font-bold">
                            <tr>
                                <th class="px-6 py-4">ID Transaksi</th>
                                <th class="px-6 py-4">Item/Game</th>
                                <th class="px-6 py-4">Status Bayar</th>
                                <th class="px-6 py-4">Status Pengiriman</th>
                                <th class="px-6 py-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-6 py-4 font-medium text-sm">{{ order.reference_id }}</td>
                                <td class="px-6 py-4 text-sm">
                                    <div class="font-bold">{{ order.game.name }}</div>
                                    <div class="text-xs text-slate-500">{{ order.product.name }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase" :class="order.payment_status === 'paid' ? 'bg-green-100 text-green-600 dark:bg-green-500/10' : 'bg-red-100 text-red-600 dark:bg-red-500/10'">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 text-sm font-medium">
                                        <Clock v-if="order.status === 'pending'" class="w-4 h-4 text-yellow-500" />
                                        <CheckCircle2 v-else-if="order.status === 'success'" class="w-4 h-4 text-green-500" />
                                        {{ order.status }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-black">{{ formatCurrency(order.total_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
