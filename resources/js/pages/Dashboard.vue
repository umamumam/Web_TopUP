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
import { ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    stats: {
        total_sales: number;
        total_orders: number;
        pending_orders: number;
        success_orders: number;
        total_games: number;
        total_products: number;
        digiflazz_balance: number;
    };
    recentOrders: any[];
    chartData: {
        monthly: any[];
        weekly: any[];
        daily: any[];
    }
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

const currentTab = ref('Bulanan');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return {
        dayName: days[date.getDay()],
        dayNum: date.getDate(),
        monthName: months[date.getMonth()]
    };
};

// Chart Options & Series
const monthlyOptions = {
    chart: {
        type: 'area' as const,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#0ea5e9'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100],
        },
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth' as const, width: 3 },
    xaxis: {
        categories: props.chartData.monthly.map(d => {
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
            return months[d.month - 1];
        }),
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => `Rp ${val.toLocaleString('id-ID')}`
        }
    },
    grid: {
        borderColor: 'rgba(156, 163, 175, 0.1)',
        strokeDashArray: 4,
    }
};

const weeklyOptions = {
    chart: {
        type: 'area' as const,
        toolbar: { show: false },
        fontFamily: 'inherit',
    },
    colors: ['#0ea5e9'],
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [20, 100, 100, 100],
        },
    },
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth' as const, width: 3 },
    xaxis: {
        categories: props.chartData.weekly.map(d => `Mgg ${d.week}`),
        axisBorder: { show: false },
        axisTicks: { show: false },
    },
    yaxis: {
        labels: {
            formatter: (val: number) => `Rp ${val.toLocaleString('id-ID')}`
        }
    },
    grid: {
        borderColor: 'rgba(156, 163, 175, 0.1)',
        strokeDashArray: 4,
    }
};

const monthlySeries = [{
    name: 'Pemasukan',
    data: props.chartData.monthly.map(d => d.total)
}];

const weeklySeries = [{
    name: 'Pemasukan',
    data: props.chartData.weekly.map(d => d.total)
}];
</script>

<template>
    <Head title="Dashboard Admin" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8 bg-slate-50/50 dark:bg-slate-950/50 min-h-screen">
            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 text-slate-800 dark:text-slate-100">
                <!-- Revenue + Orders -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 relative overflow-hidden group">
                    <div class="space-y-4 relative z-10">
                        <div class="flex items-center gap-3">
                            <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Total Pendapatan</span>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black">{{ formatCurrency(stats.total_sales) }}</span>
                            <div class="flex items-center gap-1.5 mt-1 text-[10px] font-bold text-indigo-500 uppercase tracking-wider">
                                <ShoppingCart class="w-3 h-3" />
                                {{ stats.total_orders }} Pesanan Berhasil
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Digiflazz Balance -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 group">
                    <div class="space-y-4">
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Saldo Digiflazz</span>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black">{{ formatCurrency(stats.digiflazz_balance) }}</span>
                            <span class="mt-1 text-[10px] font-bold text-orange-500 uppercase tracking-wider">Status: Aktif</span>
                        </div>
                    </div>
                </div>

                <!-- Games -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 group">
                    <div class="space-y-4">
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Total Game</span>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black leading-none">{{ stats.total_games }}</span>
                            <span class="mt-2 text-[10px] font-bold text-purple-500 uppercase tracking-wider">Kategori Aktif</span>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="bg-white dark:bg-slate-900 p-6 rounded-[2rem] shadow-sm border border-slate-200 dark:border-slate-800 group">
                    <div class="space-y-4">
                        <span class="text-xs font-black uppercase tracking-[0.2em] text-slate-400">Total Produk</span>
                        <div class="flex flex-col">
                            <span class="text-2xl font-black leading-none">{{ stats.total_products }}</span>
                            <span class="mt-2 text-[10px] font-bold text-blue-500 uppercase tracking-wider">Voucher Tersedia</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Financial Statistics Section -->
            <div class="bg-white dark:bg-slate-900 p-8 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
                    <h2 class="text-xl font-black uppercase tracking-tight">Statistik Keuangan</h2>
                    
                    <div class="flex items-center p-1.5 bg-slate-100 dark:bg-slate-800 rounded-2xl">
                        <button 
                            @click="currentTab = 'Bulanan'"
                            class="px-6 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all"
                            :class="currentTab === 'Bulanan' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Bulanan
                        </button>
                        <button 
                            @click="currentTab = 'Mingguan'"
                            class="px-6 py-2.5 text-xs font-black uppercase tracking-widest rounded-xl transition-all"
                            :class="currentTab === 'Mingguan' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-500 hover:text-slate-700'"
                        >
                            Mingguan
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                    <!-- Main Chart Column -->
                    <div class="lg:col-span-8">
                        <VueApexCharts 
                            v-if="currentTab === 'Bulanan'"
                            type="area" 
                            height="350" 
                            :options="monthlyOptions" 
                            :series="monthlySeries" 
                        />
                        <VueApexCharts 
                            v-else
                            type="area" 
                            height="350" 
                            :options="weeklyOptions" 
                            :series="weeklySeries" 
                        />
                        
                        <div class="flex items-center justify-center gap-8 mt-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-indigo-500"></div>
                                <span class="text-[10px] font-black uppercase text-slate-500 tracking-wider">Pemasukan</span>
                            </div>
                        </div>
                    </div>

                    <!-- Daily Side List -->
                    <div class="lg:col-span-4 space-y-8">
                        <div>
                            <h3 class="font-black uppercase tracking-wider text-slate-400 text-xs mb-2">Pemasukan Mingguan</h3>
                            <p class="text-sm font-medium text-slate-500 mb-6">Detail Pemasukan 7 Hari Terakhir</p>
                        </div>

                        <div class="space-y-6">
                            <div v-for="day in chartData.daily" :key="day.date" class="flex items-center justify-between border-b dark:border-slate-800 pb-4 last:border-0">
                                <div class="flex items-center gap-4">
                                    <div class="text-left">
                                        <div class="text-xs font-black text-slate-800 dark:text-slate-200 uppercase">{{ formatDate(day.date).dayName }}</div>
                                        <div class="text-[10px] text-slate-400 font-bold uppercase">{{ formatDate(day.date).dayNum }} {{ formatDate(day.date).monthName }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-black text-rose-500">{{ formatCurrency(day.total) }}</div>
                                </div>
                            </div>
                            
                            <div v-if="chartData.daily.length === 0" class="text-center py-10">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Belum ada transaksi</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Orders Table -->
            <div class="bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-sm border border-slate-200 dark:border-slate-800 overflow-hidden">
                <div class="p-8 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center bg-slate-50/50 dark:bg-slate-800/30">
                    <h2 class="text-xl font-bold uppercase tracking-tight">Pesanan Terbaru</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                            <tr>
                                <th class="px-8 py-5">ID Transaksi</th>
                                <th class="px-8 py-5">Item/Game</th>
                                <th class="px-8 py-5 text-center">Status Bayar</th>
                                <th class="px-8 py-5">Status Pengiriman</th>
                                <th class="px-8 py-5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="order in recentOrders" :key="order.id" class="hover:bg-slate-50 dark:hover:bg-slate-800/20 transition-colors">
                                <td class="px-8 py-6 font-medium text-sm text-slate-600 dark:text-slate-400">{{ order.reference_id }}</td>
                                <td class="px-8 py-6">
                                    <div class="font-black text-sm uppercase">{{ order.game.name }}</div>
                                    <div class="text-[10px] text-slate-400 font-bold uppercase mt-0.5">{{ order.product.name }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <span class="px-4 py-1.5 rounded-xl text-[9px] font-black uppercase tracking-widest shadow-sm inline-block" :class="order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10' : 'bg-rose-100 text-rose-600 dark:bg-rose-500/10'">
                                        {{ order.payment_status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-2 text-xs font-black uppercase tracking-tighter">
                                        <Clock v-if="order.status === 'pending'" class="w-4 h-4 text-yellow-500" />
                                        <CheckCircle2 v-else-if="order.status === 'success'" class="w-4 h-4 text-emerald-500" />
                                        {{ order.status }}
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right font-black text-slate-900 dark:text-slate-100">{{ formatCurrency(order.total_amount) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
