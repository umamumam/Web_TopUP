<script setup lang="ts">
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import { Package, RefreshCw, Plus, Pencil, Trash2, X, Search } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    products: any;
    games: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Produk', href: '/admin/products' },
];

const showManualForm = ref(false);

const form = useForm({
    id: null as number | null,
    game_id: '' as string | number,
    name: '',
    sku: '',
    price: 0,
    original_price: 0,
});

const isEditing = computed(() => form.id !== null);

const syncForm = useForm({});
const digiSearchQuery = ref('');
const isShowingDigiModal = ref(false);
const digiProducts = ref<any[]>([]);
const isFetchingDigi = ref(false);

const sync = () => {
    if (confirm('Sinkronisasi massal akan memperbarui harga modal produk yang sudah ada sesuai margin profit di pengaturan. Lanjutkan?')) {
        syncForm.post('/admin/products/sync', {
            onSuccess: () => alert('Sinkronisasi massal selesai!'),
        });
    }
};

const openDigiModal = async () => {
    isShowingDigiModal.value = true;

    if (digiProducts.value.length === 0) {
        fetchDigiProducts();
    }
};

const fetchDigiProducts = async () => {
    isFetchingDigi.value = true;

    try {
        const response = await axios.get('/admin/products/get-price-list');

        if (response.data.success) {
            digiProducts.value = response.data.data;
        } else {
            alert(response.data.message);
        }
    } catch (error: any) {
        console.error('Fetch error:', error);
        alert('Gagal mengambil data Digiflazz: ' + (error.response?.data?.message || error.message));
    } finally {
        isFetchingDigi.value = false;
    }
};

const filteredDigiProducts = computed(() => {
    if (!digiSearchQuery.value) {
        return digiProducts.value;
    }

    const query = digiSearchQuery.value.toLowerCase();

    return digiProducts.value.filter(p => 
        (p.product_name && p.product_name.toLowerCase().includes(query)) || 
        (p.brand && p.brand.toLowerCase().includes(query)) || 
        (p.buyer_sku_code && p.buyer_sku_code.toLowerCase().includes(query))
    );
});

const importProduct = async (product: any) => {
    try {
        const response = await axios.post('/admin/products/import', product);
        
        if (response.data.success) {
            alert(response.data.message);
            router.reload({ only: ['products'] });
        } else {
            alert(response.data.message);
        }
    } catch (error: any) {
        console.error('Import error:', error);
        alert('Gagal mengimpor produk: ' + (error.response?.data?.message || error.message));
    }
};

const editProduct = (product: any) => {
    form.id = product.id;
    form.game_id = product.game_id;
    form.name = product.name;
    form.sku = product.sku;
    form.price = product.price;
    form.original_price = product.original_price;
    showManualForm.value = true;
};

const cancelEdit = () => {
    form.reset();
    form.id = null;
    showManualForm.value = false;
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/products/${form.id}`, {
            onSuccess: () => {
                cancelEdit();
                alert('Produk berhasil diupdate!');
            }
        });
    } else {
        form.post('/admin/products', {
            onSuccess: () => {
                form.reset();
                showManualForm.value = false;
                alert('Produk manual ditambahkan!');
            }
        });
    }
};

const deleteProduct = (id: number) => {
    if (confirm('Hapus produk ini?')) {
        form.delete(`/admin/products/${id}`);
    }
};

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
};
</script>

<template>
    <Head title="Kelola Produk" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-blue-500 rounded-3xl shadow-lg shadow-blue-500/20 text-white">
                        <Package class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tight">Katalog Produk</h1>
                        <p class="text-slate-500 font-medium tracking-tight">Manajemen SKU, harga otomatis (Digiflazz) atau manual.</p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button 
                        @click="showManualForm = !showManualForm"
                        class="flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-white font-bold px-6 py-3.5 rounded-2xl transition-all active:scale-95"
                    >
                        <Plus class="w-5 h-5 text-blue-400" />
                        Tambah Manual
                    </button>
                    <button 
                        @click="openDigiModal"
                        class="flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white font-bold px-6 py-3.5 rounded-2xl shadow-lg shadow-blue-500/20 transition-all active:scale-95"
                    >
                        <Search class="w-5 h-5" />
                        Cari di Digiflazz
                    </button>
                    <button 
                        @click="sync"
                        :disabled="syncForm.processing"
                        class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold px-6 py-3.5 rounded-2xl shadow-lg shadow-indigo-500/20 transition-all active:scale-95 disabled:opacity-50"
                    >
                        <RefreshCw class="w-5 h-5" :class="syncForm.processing ? 'animate-spin' : ''" />
                        Sync Digiflazz
                    </button>
                </div>
            </div>

            <!-- Manual Form -->
            <div v-if="showManualForm" class="bg-white dark:bg-slate-900 p-8 rounded-[32px] border-2 border-blue-500/30 shadow-2xl relative overflow-hidden transition-all duration-500 animate-in slide-in-from-top-4">
                <button @click="cancelEdit" class="absolute top-6 right-6 p-2 text-slate-400 hover:text-red-500 transition-colors">
                    <X class="w-6 h-6" />
                </button>
                <h2 class="text-2xl font-black mb-8 uppercase tracking-widest flex items-center gap-3">
                    <span class="w-2 h-8 bg-blue-500 rounded-full"></span>
                    {{ isEditing ? 'Edit Produk' : 'Tambah Produk Manual' }}
                </h2>
                
                <form @submit.prevent="submit" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Games</label>
                        <select v-model="form.game_id" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-blue-500 outline-none transition-all font-bold appearance-none">
                            <option value="">Pilih Game</option>
                            <option v-for="game in games" :key="game.id" :value="game.id">{{ game.name }}</option>
                        </select>
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Nama Produk</label>
                        <input v-model="form.name" type="text" placeholder="Contoh: 50 Diamonds" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-blue-500 outline-none transition-all font-bold" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">SKU Code</label>
                        <input v-model="form.sku" type="text" placeholder="ML_50_DM" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-blue-500 outline-none transition-all font-mono text-sm" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Harga Modal (IDR)</label>
                        <input v-model="form.original_price" type="number" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-blue-500 outline-none transition-all font-bold" />
                    </div>
                    <div class="space-y-2">
                        <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Harga Jual (IDR)</label>
                        <input v-model="form.price" type="number" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-blue-500 outline-none transition-all font-bold" />
                    </div>
                    <div class="flex items-end">
                        <button type="submit" :disabled="form.processing" class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black py-3.5 rounded-2xl shadow-xl shadow-blue-500/20 transition-all uppercase tracking-widest text-sm">
                            {{ isEditing ? 'Simpan' : 'Tambah Ke Katalog' }}
                        </button>
                    </div>
                </form>
            </div>

            <!-- List Section -->
            <div class="bg-white dark:bg-slate-900 rounded-[40px] border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                            <tr>
                                <th class="px-8 py-5">Produk</th>
                                <th class="px-8 py-5">Game</th>
                                <th class="px-8 py-5">SKU</th>
                                <th class="px-8 py-5">Profit</th>
                                <th class="px-8 py-5">Harga Jual</th>
                                <th class="px-8 py-5 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="product in products.data" :key="product.id" class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ product.name }}</div>
                                    <div v-if="!product.is_active" class="inline-flex mt-1 px-1.5 py-0.5 rounded bg-red-500 text-[8px] text-white font-black uppercase">Inactive</div>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium">{{ product.game?.name }}</td>
                                <td class="px-8 py-5 font-mono text-xs text-slate-500 uppercase tracking-tighter">{{ product.sku }}</td>
                                <td class="px-8 py-5">
                                    <div class="text-[10px] text-slate-400 font-bold uppercase tracking-tighter mb-0.5">Untung</div>
                                    <div class="text-green-500 font-black text-xs">+{{ formatCurrency(product.price - product.original_price) }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-[10px] text-blue-500 font-bold uppercase tracking-tighter mb-0.5">Final</div>
                                    <div class="font-black text-base">{{ formatCurrency(product.price) }}</div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="flex gap-2 justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="editProduct(product)" class="p-2.5 text-blue-500 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-500 hover:text-white rounded-xl transition-all">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteProduct(product.id)" class="p-2.5 text-red-500 bg-red-50 dark:bg-red-500/10 hover:bg-red-500 hover:text-white rounded-xl transition-all">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center bg-white dark:bg-slate-900 p-8 rounded-[32px] border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-xs font-black text-slate-400 uppercase tracking-widest">
                    {{ products.total }} TOTAL DATA
                </div>
                <div class="flex gap-2">
                    <Link 
                        v-for="link in products.links" 
                        :key="link.label"
                        :href="link.url || '#'"
                        class="px-5 py-2.5 rounded-2xl text-xs font-black uppercase tracking-widest border-2 transition-all active:scale-95"
                        :class="[
                            link.active ? 'bg-blue-600 border-blue-600 text-white shadow-lg' : 'bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:bg-slate-100 text-slate-500',
                            !link.url ? 'opacity-30 cursor-not-allowed' : ''
                        ]"
                    >
                        <span v-html="link.label"></span>
                    </Link>
                </div>
            </div>
        </div>
        <!-- Digiflazz Search Modal -->
        <div v-if="isShowingDigiModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white dark:bg-slate-900 w-full max-w-4xl rounded-[40px] shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-800/50">
                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight">Cari Produk Digiflazz</h2>
                        <p class="text-slate-500 text-sm font-medium">Cari dan impor produk langsung ke katalog Anda.</p>
                    </div>
                    <button @click="isShowingDigiModal = false" class="p-3 hover:bg-slate-200 dark:hover:bg-slate-700 rounded-2xl transition-all font-bold">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-8 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800">
                    <div class="relative">
                        <Search class="absolute left-5 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5 transition-colors group-focus-within:text-blue-500" />
                        <input 
                            v-model="digiSearchQuery"
                            type="text" 
                            placeholder="Cari Nama Produk, Brand, atau SKU (Contoh: Mobile Legends, 50 Diamonds...)"
                            class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none transition-all font-bold shadow-inner"
                        />
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-8 custom-scrollbar">
                    <div v-if="isFetchingDigi" class="flex flex-col items-center justify-center py-20 space-y-4">
                        <RefreshCw class="w-12 h-12 text-blue-500 animate-spin" />
                        <p class="font-black uppercase tracking-widest text-slate-400 animate-pulse text-xs">Mengambil Data Price List...</p>
                    </div>
                    
                    <div v-else-if="filteredDigiProducts.length === 0" class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                        <div class="w-20 h-20 bg-slate-50 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300">
                            <Package class="w-10 h-10" />
                        </div>
                        <div>
                            <p class="text-lg font-bold">Produk tidak ditemukan</p>
                            <p class="text-slate-400 text-sm">Coba kata kunci lain atau periksa koneksi API Anda.</p>
                        </div>
                    </div>

                    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div 
                            v-for="p in filteredDigiProducts" 
                            :key="p.buyer_sku_code"
                            class="p-5 bg-slate-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 rounded-3xl border-2 border-transparent hover:border-blue-500/50 transition-all group flex items-center justify-between"
                        >
                            <div class="space-y-1">
                                <div class="text-[10px] font-black text-blue-500 uppercase tracking-widest">{{ p.brand }} - {{ p.category }}</div>
                                <div class="font-black text-slate-900 dark:text-white group-hover:text-blue-600 transition-colors uppercase leading-tight">{{ p.product_name }}</div>
                                <div class="flex items-center gap-3">
                                    <div class="text-xs font-mono text-slate-400 uppercase tracking-tighter">{{ p.buyer_sku_code }}</div>
                                    <div class="text-sm font-black text-slate-900 dark:text-white">{{ formatCurrency(p.price) }}</div>
                                </div>
                            </div>
                            <button 
                                @click="importProduct(p)"
                                class="bg-white dark:bg-slate-700 hover:bg-blue-600 dark:hover:bg-blue-500 hover:text-white p-3 rounded-2xl shadow-sm transition-all active:scale-90 border border-slate-100 dark:border-slate-600"
                            >
                                <Plus class="w-5 h-5" />
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="p-6 bg-slate-50/50 dark:bg-slate-800/50 border-t border-slate-100 dark:border-slate-800 flex justify-between items-center text-[10px] font-black text-slate-400 uppercase tracking-widest">
                    <div>Menampilkan {{ filteredDigiProducts.length }} produk</div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span> Live Connection
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.2);
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(148, 163, 184, 0.4);
}
</style>
