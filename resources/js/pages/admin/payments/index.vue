<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    CreditCard, 
    Plus, 
    Search, 
    Edit2, 
    Trash2, 
    Image as ImageIcon,
    X
} from 'lucide-vue-next';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface PaymentMethod {
    id: number;
    name: string;
    code: string;
    type: string;
    fee_flat: number;
    fee_percent: number;
    image: string | null;
    is_active: boolean;
    created_at: string;
}

const props = defineProps<{
    payments: PaymentMethod[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Manajemen Payment', href: '/admin/payments' },
];

const searchQuery = ref('');
const showModal = ref(false);
const editingPayment = ref<PaymentMethod | null>(null);
const imagePreview = ref<string | null>(null);

const filteredPayments = computed(() => {
    return props.payments.filter(payment => 
        payment.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        payment.code.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        payment.type.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
});

const form = useForm({
    name: '',
    code: '',
    type: 'e-wallet',
    fee_flat: 0,
    fee_percent: 0,
    is_active: true,
    image_file: null as File | null,
});

const openCreateModal = () => {
    editingPayment.value = null;
    form.reset();
    form.clearErrors();
    imagePreview.value = null;
    showModal.value = true;
};

const openEditModal = (payment: PaymentMethod) => {
    editingPayment.value = payment;
    form.name = payment.name;
    form.code = payment.code;
    form.type = payment.type;
    form.fee_flat = payment.fee_flat;
    form.fee_percent = payment.fee_percent;
    form.is_active = payment.is_active;
    form.image_file = null;
    imagePreview.value = payment.image ? `/storage/${payment.image}` : null;
    showModal.value = true;
};

const handleImageChange = (e: Event) => {
    const file = (e.target as HTMLInputElement).files?.[0];
    if (file) {
        form.image_file = file;
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    }
};

const submit = () => {
    if (editingPayment.value) {
        // Manual form because of image upload and Laravel/Symphony multipart PUT limitation
        form.transform((data) => ({
            ...data,
            _method: 'put',
        })).post(`/admin/payments/${editingPayment.value.id}`, {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post('/admin/payments', {
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deletePayment = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus metode pembayaran ini?')) {
        router.delete(`/admin/payments/${id}`);
    }
};

const toggleStatus = (id: number) => {
    router.post(`/admin/payments/${id}/toggle`);
};

const getStatusColor = (active: boolean) => {
    return active ? 'text-green-500 bg-green-500/10' : 'text-slate-400 bg-slate-100';
};

const formatPrice = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <Head title="Manajemen Payment" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-blue-500 rounded-3xl shadow-lg shadow-blue-500/20 text-white">
                        <CreditCard class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tight">Manajemen Payment</h1>
                        <p class="text-slate-500 font-medium">Kelola metode pembayaran dan biaya transaksi.</p>
                    </div>
                </div>
                
                <button 
                    @click="openCreateModal"
                    class="bg-blue-600 hover:bg-blue-500 text-white font-extrabold px-6 py-4 rounded-2xl shadow-xl shadow-blue-500/20 transition-all active:scale-95 flex items-center justify-center gap-2"
                >
                    <Plus class="w-5 h-5" />
                    TAMBAH METODE
                </button>
            </div>

            <!-- Stats/Filters Section -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
                <div class="relative">
                    <Search class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5" />
                    <input 
                        v-model="searchQuery"
                        type="text" 
                        placeholder="Cari metode pembayaran berdasarkan nama atau kode..."
                        class="w-full pl-12 pr-6 py-4 bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl outline-none transition-all font-medium"
                    />
                </div>
            </div>

            <!-- grid View -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div 
                    v-for="payment in filteredPayments" 
                    :key="payment.id"
                    class="group bg-white dark:bg-slate-900 rounded-3xl border-2 transition-all overflow-hidden shadow-sm"
                    :class="payment.is_active ? 'border-slate-100 hover:border-blue-500' : 'border-slate-100 opacity-75 grayscale shadow-none'"
                >
                    <div class="p-6 space-y-6">
                        <div class="flex items-start justify-between">
                            <div class="w-20 h-12 bg-slate-50 dark:bg-slate-800 rounded-xl flex items-center justify-center overflow-hidden border border-slate-100 dark:border-slate-700 p-2">
                                <img v-if="payment.image" :src="`/storage/${payment.image}`" :alt="payment.name" class="max-w-full max-h-full object-contain" />
                                <CreditCard v-else class="w-6 h-6 text-slate-300" />
                            </div>
                            <div class="flex gap-2">
                                <button 
                                    @click="openEditModal(payment)"
                                    class="p-2 text-slate-400 hover:text-blue-500 hover:bg-blue-50 dark:hover:bg-blue-500/10 rounded-xl transition-colors"
                                >
                                    <Edit2 class="w-5 h-5" />
                                </button>
                                <button 
                                    @click="deletePayment(payment.id)"
                                    class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-colors"
                                >
                                    <Trash2 class="w-5 h-5" />
                                </button>
                            </div>
                        </div>

                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-black uppercase text-slate-900 dark:text-white line-clamp-1">{{ payment.name }}</h3>
                                <div :class="['px-2 py-0.5 rounded-lg text-[10px] font-black uppercase tracking-wider', getStatusColor(payment.is_active)]">
                                    {{ payment.is_active ? 'ACTIVE' : 'INACTIVE' }}
                                </div>
                            </div>
                            <p class="text-slate-400 font-bold text-xs uppercase tracking-widest">{{ payment.code }} • {{ payment.type }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 pt-4 border-t border-slate-100 dark:border-slate-800">
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fee Tetap</p>
                                <p class="text-base font-black text-slate-900 dark:text-white">{{ formatPrice(payment.fee_flat) }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Fee Persen</p>
                                <p class="text-base font-black text-slate-900 dark:text-white">{{ payment.fee_percent }}%</p>
                            </div>
                        </div>
                        
                        <button 
                            @click="toggleStatus(payment.id)"
                            class="w-full py-3 rounded-2xl font-black uppercase text-xs tracking-widest transition-all"
                            :class="payment.is_active ? 'bg-slate-100 text-slate-600 hover:bg-slate-200' : 'bg-blue-600 text-white hover:bg-blue-500 shadow-lg shadow-blue-500/20'"
                        >
                            {{ payment.is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredPayments.length === 0" class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                <div class="w-24 h-24 bg-slate-100 dark:bg-slate-800 rounded-full flex items-center justify-center text-slate-300">
                    <CreditCard class="w-12 h-12" />
                </div>
                <div>
                    <h3 class="text-xl font-bold uppercase">Tidak Ada Data</h3>
                    <p class="text-slate-500">Metode pembayaran tidak ditemukan.</p>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showModal = false"></div>
            
            <div class="relative w-full max-w-xl bg-white dark:bg-slate-900 rounded-[2.5rem] shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-200">
                <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight">{{ editingPayment ? 'Edit Payment' : 'Tambah Payment' }}</h2>
                        <p class="text-slate-500 font-medium text-sm">Lengkapi detail metode pembayaran di bawah ini.</p>
                    </div>
                    <button @click="showModal = false" class="p-3 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 rounded-2xl transition-all">
                        <X class="w-6 h-6 text-slate-500" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="p-8 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                    <div class="flex flex-col items-center justify-center">
                        <label for="image_file" class="group relative w-32 h-32 bg-slate-50 dark:bg-slate-800 rounded-3xl border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center gap-2 cursor-pointer hover:border-blue-500 hover:bg-blue-50/50 transition-all overflow-hidden">
                            <img v-if="imagePreview" :src="imagePreview" class="absolute inset-0 w-full h-full object-contain p-2" />
                            <div v-else class="flex flex-col items-center">
                                <ImageIcon class="w-10 h-10 text-slate-300 group-hover:text-blue-500" />
                                <span class="text-[10px] font-black uppercase text-slate-400 group-hover:text-blue-500">Logo</span>
                            </div>
                            <input id="image_file" type="file" class="hidden" @change="handleImageChange" accept="image/*" />
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-black uppercase tracking-widest text-slate-400">Nama Metode</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                placeholder="Gopay / QRIS"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl p-4 outline-none transition-all font-bold"
                            />
                            <p v-if="form.errors.name" class="text-red-500 text-xs font-bold">{{ form.errors.name }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black uppercase tracking-widest text-slate-400">Kode (Provider)</label>
                            <input 
                                v-model="form.code"
                                type="text" 
                                placeholder="gopay / bca_va"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl p-4 outline-none transition-all font-bold"
                            />
                            <p v-if="form.errors.code" class="text-red-500 text-xs font-bold">{{ form.errors.code }}</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-black uppercase tracking-widest text-slate-400">Tipe Pembayaran</label>
                        <select 
                            v-model="form.type"
                            class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl p-4 outline-none transition-all font-bold appearance-none"
                        >
                            <option value="e-wallet">E-Wallet</option>
                            <option value="bank_transfer">Bank Transfer (VA)</option>
                            <option value="qris">QRIS</option>
                            <option value="retail">Convenience Store</option>
                            <option value="manual">Transfer Manual</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label class="text-sm font-black uppercase tracking-widest text-slate-400">Biaya Tetap (Rp)</label>
                            <input 
                                v-model="form.fee_flat"
                                type="number" 
                                class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl p-4 outline-none transition-all font-bold"
                            />
                            <p v-if="form.errors.fee_flat" class="text-red-500 text-xs font-bold">{{ form.errors.fee_flat }}</p>
                        </div>
                        <div class="space-y-2">
                            <label class="text-sm font-black uppercase tracking-widest text-slate-400">Biaya Persen (%)</label>
                            <input 
                                v-model="form.fee_percent"
                                type="number" step="0.01"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-transparent focus:border-blue-500 rounded-2xl p-4 outline-none transition-all font-bold"
                            />
                            <p v-if="form.errors.fee_percent" class="text-red-500 text-xs font-bold">{{ form.errors.fee_percent }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-4 bg-slate-50 dark:bg-slate-800 rounded-2xl border border-slate-100 dark:border-slate-700">
                        <input 
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox" 
                            class="w-6 h-6 rounded-lg text-blue-600 focus:ring-blue-500"
                        />
                        <label for="is_active" class="text-sm font-black uppercase tracking-widest text-slate-900 dark:text-white flex-1 cursor-pointer">Status Aktif</label>
                    </div>

                    <div class="pt-6">
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-blue-600 hover:bg-blue-500 text-white font-black uppercase py-5 rounded-3xl shadow-2xl shadow-blue-500/30 transition-all active:scale-95 disabled:opacity-50"
                        >
                            {{ form.processing ? 'Sedang Memproses...' : (editingPayment ? 'Perbarui Metode' : 'Tambahkan Metode') }}
                        </button>
                    </div>
                </form>
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
