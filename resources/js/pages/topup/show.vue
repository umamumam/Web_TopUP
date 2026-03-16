<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { CreditCard, Wallet, Landmark, ChevronRight, CheckCircle2, QrCode, Star, MessageSquare } from 'lucide-vue-next';
import { ref } from 'vue';
import { store, status } from '@/routes/topup';

const props = defineProps<{
    game: any;
    paymentMethods: any[];
}>();

const form = useForm({
    product_id: null,
    payment_method_id: null,
    target_id: '',
    server_id: '',
    nickname: '',
    whatsapp_number: '',
});

const username = ref('');
const regionData = ref('');
const isValidating = ref(false);

const validateId = async () => {
    // Only validate if target_id is present
    if (!form.target_id) {
        return;
    }
    
    // If game has server, only validate if server_id has at least 4 digits (typical for ML)
    if (props.game.has_server && (!form.server_id || form.server_id.length < 3)) {
        return;
    }

    isValidating.value = true;
    username.value = '';
    regionData.value = '';

    try {
        const response = await axios.post('/topup/check-id', {
            game_slug: props.game.slug,
            target_id: form.target_id,
            server_id: form.server_id
        });

        if (response.data.success) {
            username.value = response.data.username;
            regionData.value = response.data.region;
            form.nickname = response.data.username;
        } else {
            // Reset nickname if validation fails
            username.value = '';
            regionData.value = '';
            form.nickname = '';
            console.warn(response.data.message);
        }
    } catch {
        console.error('Cek ID Gagal');
    } finally {
        isValidating.value = false;
    }
};

// Auto validate when typing server ID if target ID is already filled
const onServerIdInput = () => {
    if (form.target_id && form.server_id.length >= 4) {
        validateId();
    }
};

const isSubmitting = ref(false);

const checkout = async () => {
    if (!form.product_id || !form.payment_method_id || !form.target_id || !form.whatsapp_number) {
        alert('Mohon lengkapi semua data!');
        return;
    }

    if (isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;

    try {
        const response = await axios.post(store.url(), form.data());

        if (response.data.success) {
            const snapToken = response.data.snap_token;

            // @ts-expect-error - snap is defined in midtrans script
            window.snap.pay(snapToken, {
                onSuccess: function() {
                    window.location.href = status.url(response.data.reference_id);
                },
                onPending: function() {
                    window.location.href = status.url(response.data.reference_id);
                },
                onError: function() {
                    alert('Pembayaran gagal!');
                },
                onClose: function() {
                    alert('Anda menutup popup pembayaran sebelum selesai.');
                }
            });
        }
    } catch (error: any) {
        alert(error.response?.data?.message || 'Terjadi kesalahan');
    } finally {
        isSubmitting.value = false;
    }
};


</script>

<template>
    <Head :title="'Top Up ' + game.name" />

    <div class="min-h-screen bg-[#0b0e11] text-slate-50 font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Game Banner & Header -->
        <div class="relative w-full overflow-hidden pb-8 sm:pb-12 pt-6 sm:pt-10">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0">
                <img :src="game.banner || game.thumbnail" class="w-full h-full object-cover blur-md opacity-20 scale-110" />
                <div class="absolute inset-0 bg-gradient-to-b from-transparent via-[#0b0e11]/80 to-[#0b0e11]"></div>
            </div>
            
            <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col items-center text-center mt-4">
                <!-- Game Logo -->
                <div class="relative group mb-4 sm:mb-6">
                    <div class="absolute -inset-1 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-[28px] blur opacity-30 group-hover:opacity-100 transition duration-1000 group-hover:duration-200"></div>
                    <img :src="game.thumbnail" class="relative w-24 h-24 sm:w-32 sm:h-32 rounded-[28px] object-cover border-2 border-white/10 shadow-2xl" />
                </div>
                
                <!-- Title -->
                <h1 class="text-2xl sm:text-4xl font-black uppercase tracking-tight text-white mb-2">{{ game.name }}</h1>
                <p class="text-slate-400 text-[10px] sm:text-xs font-bold uppercase tracking-widest mb-6 border border-slate-700 bg-slate-800/50 px-3 py-1 rounded-full">Official Partner</p>
                
                <!-- Badges -->
                <div class="flex flex-wrap justify-center gap-2 sm:gap-4 text-[9px] sm:text-[10px] font-black uppercase tracking-widest text-slate-300">
                    <div class="flex items-center gap-1.5 bg-slate-900/80 backdrop-blur px-3 py-1.5 rounded-full border border-slate-800 ring-1 ring-white/5">
                        <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 shadow-[0_0_8px_#10b981]"></div>
                        <span>Proses Cepat</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-900/80 backdrop-blur px-3 py-1.5 rounded-full border border-slate-800 ring-1 ring-white/5">
                        <div class="w-1.5 h-1.5 rounded-full bg-indigo-500 shadow-[0_0_8px_#6366f1]"></div>
                        <span>Layanan 24/7</span>
                    </div>
                    <div class="flex items-center gap-1.5 bg-slate-900/80 backdrop-blur px-3 py-1.5 rounded-full border border-slate-800 ring-1 ring-white/5">
                        <div class="w-1.5 h-1.5 rounded-full bg-purple-500 shadow-[0_0_8px_#a855f7]"></div>
                        <span>Aman</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
                
                <!-- Left Content: Forms (8 cols) -->
                <div class="lg:col-span-8 space-y-8">
                    
                    <!-- Step 1: User ID -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-[40px] p-8 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-blue-500/10 rounded-full blur-[100px] group-hover:bg-blue-500/20 transition-all duration-700"></div>
                        
                        <div class="flex items-center gap-4 mb-6 sm:mb-8">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-black text-sm sm:text-base text-white shadow-lg shadow-indigo-500/20">1</div>
                            <div>
                                <h2 class="text-sm sm:text-lg font-black uppercase tracking-tight">Data Akun</h2>
                                <p class="text-slate-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest leading-none mt-0.5">Lengkapi Tujuan</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3 w-full relative">
                            <div class="space-y-2 flex-grow">
                                <label class="block text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2">{{ game.input_label }}</label>
                                <input 
                                    v-model="form.target_id"
                                    type="text" 
                                    @blur="validateId"
                                    class="w-full bg-slate-950/50 border-2 border-slate-800/80 rounded-[16px] sm:rounded-[20px] px-4 py-3 sm:px-5 sm:py-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none font-bold"
                                    placeholder="Ketikan ID"
                                />
                            </div>
                            <div v-if="game.has_server" class="space-y-2 w-1/3 sm:w-2/5 shrink-0">
                                <label class="block text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2">Server</label>
                                <input 
                                    v-model="form.server_id"
                                    type="text" 
                                    @blur="validateId"
                                    @input="onServerIdInput"
                                    class="w-full bg-slate-950/50 border-2 border-slate-800/80 rounded-[16px] sm:rounded-[20px] px-4 py-3 sm:px-5 sm:py-4 text-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none font-bold placeholder-slate-600"
                                    placeholder="1234"
                                />
                            </div>
                        </div>

                        <!-- Username display -->
                        <div v-if="isValidating || username" class="mt-8 transition-all duration-500 animate-in fade-in slide-in-from-top-4">
                            <div v-if="isValidating" class="flex items-center gap-3 text-indigo-400 font-black uppercase tracking-widest text-[10px] animate-pulse">
                                <div class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                                <span>Memverifikasi Akun...</span>
                            </div>
                            <div v-else class="bg-emerald-500/10 border border-emerald-500/30 rounded-2xl p-4 flex items-center gap-4 group/user">
                                <div class="w-10 h-10 bg-emerald-500/20 rounded-xl flex items-center justify-center text-emerald-500 group-hover/user:scale-110 transition-transform duration-500">
                                    <CheckCircle2 class="w-6 h-6" />
                                </div>
                                <div>
                                    <div class="text-[10px] text-emerald-500/70 font-black uppercase tracking-[0.2em] mb-0.5">Akun Terverifikasi</div>
                                    <div class="text-sm sm:text-base font-bold text-slate-200">
                                        Your account is <span class="text-emerald-400 font-black">{{ username }}</span> 
                                        <span v-if="regionData"> from 
                                            <span class="text-indigo-400">{{ regionData === 'ID' ? 'Indonesia 🇮🇩' : (regionData === 'Generic' ? '' : regionData) }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Nominal -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-[40px] p-8 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -top-24 -left-24 w-64 h-64 bg-purple-500/10 rounded-full blur-[100px] group-hover:bg-purple-500/20 transition-all duration-700"></div>
                        
                        <div class="flex items-center gap-4 mb-6 sm:mb-8">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-black text-sm sm:text-base text-white shadow-lg shadow-indigo-500/20">2</div>
                            <div>
                                <h2 class="text-sm sm:text-lg font-black uppercase tracking-tight">Nominal Produk</h2>
                                <p class="text-slate-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest leading-none mt-0.5">Pilih Item</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4 relative">
                            <button 
                                v-for="product in game.products" 
                                :key="product.id"
                                @click="form.product_id = product.id"
                                class="relative p-4 sm:p-5 rounded-[20px] sm:rounded-[24px] border-2 transition-all duration-300 text-left group/card overflow-hidden active:scale-[0.98] ring-1 ring-white/5"
                                :class="form.product_id === product.id 
                                    ? 'bg-indigo-600/10 border-indigo-500 shadow-[0_10px_20px_rgba(79,70,229,0.15)]' 
                                    : 'bg-slate-950/80 border-slate-800/80 hover:border-slate-700 hover:bg-slate-800/50'"
                            >
                                <div class="relative z-10">
                                    <div class="flex justify-between items-start mb-2">
                                        <img src="/DIAMOND.png" class="w-5 h-5 sm:w-6 sm:h-6 object-contain filter drop-shadow-[0_2px_4px_rgba(0,0,0,0.5)]" />
                                    </div>
                                    <div 
                                        class="font-black text-[11px] sm:text-[13px] uppercase tracking-wide transition-colors line-clamp-2"
                                        :class="form.product_id === product.id ? 'text-indigo-400' : 'text-slate-200 group-hover/card:text-indigo-300'"
                                    >
                                        {{ product.name }}
                                    </div>
                                    <div class="text-indigo-400 font-black text-xs sm:text-sm mt-1.5 tracking-tighter">
                                        Rp {{ product.price.toLocaleString('id-ID') }}
                                    </div>
                                </div>

                                <div 
                                    class="absolute top-3 right-3 transition-all duration-300"
                                    :class="form.product_id === product.id ? 'translate-y-0 opacity-100' : '-translate-y-2 opacity-0'"
                                >
                                    <div class="bg-indigo-500 rounded-full p-1.5 shadow-lg shadow-indigo-500/50">
                                        <CheckCircle2 class="w-3.5 h-3.5 text-white fill-current" />
                                    </div>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Step 3: Payment -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-[40px] p-8 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden group">
                        <!-- Decorative light -->
                        <div class="absolute -top-24 -right-24 w-64 h-64 bg-indigo-500/10 rounded-full blur-[100px] group-hover:bg-indigo-500/20 transition-all duration-700"></div>
                        
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 mb-6 sm:mb-8 relative">
                            <div class="flex items-center gap-4">
                                <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-black text-sm sm:text-base text-white shadow-lg shadow-indigo-500/20">3</div>
                                <div>
                                    <h2 class="text-sm sm:text-lg font-black uppercase tracking-tight">Metode Bayar</h2>
                                    <p class="text-slate-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest leading-none mt-0.5">Pilih Cara Pembayaran</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-8 relative z-10">
                            <template v-for="cat in [
                                { id: 'qris', name: 'QRIS', icon: QrCode },
                                { id: 'e-wallet', name: 'E-Wallet', icon: Wallet },
                                { id: 'bank_transfer', name: 'Virtual Account (VA)', icon: Landmark },
                                { id: 'retail', name: 'Gerai Ritel', icon: CreditCard }
                            ]" :key="cat.id">
                                
                                <div v-if="props.paymentMethods.filter(p => p.type === cat.id).length > 0">
                                    <!-- Category Divider -->
                                    <div class="flex items-center gap-4 mb-4">
                                        <div class="bg-slate-800 p-2 rounded-xl text-indigo-400">
                                            <component :is="cat.icon" class="w-4 h-4" />
                                        </div>
                                        <h3 class="font-black text-xs sm:text-sm uppercase tracking-widest text-slate-300">{{ cat.name }}</h3>
                                        <div class="flex-grow h-px bg-slate-800/80"></div>
                                    </div>

                                    <!-- Methods List -->
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                        <button 
                                            v-for="pm in props.paymentMethods.filter(p => p.type === cat.id)" 
                                            :key="pm.id"
                                            @click="form.payment_method_id = pm.id"
                                            class="group/pm flex items-center justify-between p-4 sm:p-5 rounded-[20px] sm:rounded-[24px] border-2 transition-all duration-300 relative overflow-hidden active:scale-[0.98]"
                                            :class="form.payment_method_id === pm.id 
                                                ? 'bg-indigo-600/10 border-indigo-500 shadow-[0_10px_20px_rgba(79,70,229,0.1)]' 
                                                : 'bg-slate-800/30 border-slate-800/50 hover:border-slate-700 hover:bg-slate-800/50'"
                                        >
                                            <!-- Selected Indicator Glow -->
                                            <div v-if="form.payment_method_id === pm.id" class="absolute inset-0 bg-indigo-500/5 backdrop-blur-sm"></div>

                                            <div class="flex items-center gap-4 sm:gap-5 relative z-10 w-full pr-8">
                                                <div class="w-12 h-8 sm:w-16 sm:h-10 bg-white rounded-lg sm:rounded-xl p-1.5 flex items-center justify-center shadow-md group-hover/pm:scale-110 transition-transform duration-500 shrink-0">
                                                    <img :src="'/storage/' + pm.image" class="max-h-full max-w-full object-contain" :alt="pm.name" />
                                                </div>
                                                <div class="text-left flex-grow min-w-0">
                                                    <div class="font-black text-[12px] sm:text-[13px] uppercase tracking-wide group-hover/pm:text-indigo-400 transition-colors truncate">{{ pm.name }}</div>
                                                    <div class="text-[9px] sm:text-[10px] text-slate-500 font-bold uppercase mt-0.5 tracking-tighter truncate">
                                                        Biaya: <span :class="form.payment_method_id === pm.id ? 'text-indigo-400' : 'text-slate-400'">{{ pm.fee_percent > 0 ? pm.fee_percent + '%' : 'Rp ' + pm.fee_flat.toLocaleString('id-ID') }}</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Check Icon Animation -->
                                            <div 
                                                class="absolute right-4 transition-all duration-500"
                                                :class="form.payment_method_id === pm.id ? 'opacity-100 scale-100' : 'opacity-0 scale-50'"
                                            >
                                                <div class="bg-indigo-500 rounded-full p-1 shadow-lg shadow-indigo-500/50 flex-shrink-0">
                                                    <CheckCircle2 class="w-3 h-3 text-white fill-current" />
                                                </div>
                                            </div>
                                        </button>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Empty State if no payments -->
                            <div v-if="props.paymentMethods.length === 0" class="py-12 flex flex-col items-center justify-center text-slate-600 bg-slate-800/20 rounded-3xl border-2 border-dashed border-slate-800">
                                <CreditCard class="w-12 h-12 mb-4 opacity-20" />
                                <p class="font-bold uppercase tracking-widest text-xs">Belum ada metode pembayaran</p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Final Confirm -->
                    <div class="bg-slate-900/50 backdrop-blur-xl rounded-[40px] p-8 sm:p-10 border border-slate-800 shadow-2xl relative overflow-hidden group">
                        <div class="absolute -bottom-24 -right-24 w-64 h-64 bg-green-500/10 rounded-full blur-[100px] group-hover:bg-green-500/20 transition-all duration-700"></div>
                        <div class="flex items-center gap-4 mb-6 sm:mb-8">
                            <div class="w-8 h-8 sm:w-10 sm:h-10 bg-indigo-500 rounded-xl flex items-center justify-center font-black text-sm sm:text-base text-white shadow-lg shadow-indigo-500/20">4</div>
                            <div>
                                <h2 class="text-sm sm:text-lg font-black uppercase tracking-tight">Konfirmasi Whatsapp</h2>
                                <p class="text-slate-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest leading-none mt-0.5">Bukti tagihan</p>
                            </div>
                        </div>

                        <div class="space-y-8 relative z-10 w-full">
                            <div class="space-y-3 max-w-lg">
                                <label class="block text-[9px] sm:text-[10px] font-black uppercase text-slate-400 tracking-widest pl-2">Nomor Whatsapp</label>
                                <input 
                                    v-model="form.whatsapp_number"
                                    type="text" 
                                    class="w-full bg-slate-950/50 border-2 border-slate-800/80 rounded-[16px] sm:rounded-[20px] px-4 py-3 sm:px-5 sm:py-4 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all outline-none font-bold text-sm"
                                    placeholder="08123xxxx"
                                />
                            </div>

                            <button 
                                @click="checkout"
                                class="w-full bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-600 bg-[length:200%_auto] hover:bg-right text-white font-black py-6 rounded-[28px] text-xl uppercase shadow-2xl shadow-indigo-500/30 active:scale-95 transition-all duration-500 flex items-center justify-center gap-4 group/btn"
                            >
                                <span class="tracking-widest">Beli Sekarang</span>
                                <ChevronRight class="w-7 h-7 group-hover/btn:translate-x-2 transition-transform" />
                            </button>
                        </div>
                    </div>

                </div>

                <!-- Right Content: Sidebar (4 cols) -->
                <div class="lg:col-span-4 space-y-8">
                    <!-- Rating Card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-[40px] p-8 text-center relative overflow-hidden group">
                        <div class="absolute -top-12 -right-12 w-32 h-32 bg-yellow-500/10 rounded-full blur-3xl"></div>
                        <h3 class="text-slate-500 text-[10px] font-black uppercase tracking-[0.3em] mb-4">Ulasan dan Rating</h3>
                        <div class="text-6xl font-black text-slate-100 mb-2">4.99</div>
                        <div class="flex justify-center gap-1 mb-4 text-yellow-500">
                            <Star v-for="i in 5" :key="i" class="w-6 h-6 fill-current" />
                        </div>
                        <p class="text-slate-500 text-xs font-bold uppercase tracking-widest">Berdasarkan total 2.82jt rating</p>
                    </div>

                    <!-- Help Card -->
                    <div class="bg-slate-900 border border-slate-800 rounded-[40px] p-8 group">
                        <div class="flex items-center gap-4 mb-6">
                            <div class="w-12 h-12 bg-slate-800 rounded-2xl flex items-center justify-center text-slate-400 group-hover:bg-indigo-600 transition-colors">
                                <MessageSquare class="w-6 h-6" />
                            </div>
                            <div>
                                <h3 class="font-black text-sm uppercase tracking-wider">Butuh Bantuan?</h3>
                                <p class="text-slate-500 text-[10px] font-bold uppercase mt-1">CS kami siap membantu 24 jam</p>
                            </div>
                        </div>
                        <a 
                            href="https://wa.me/6285799352991" 
                            target="_blank"
                            class="block w-full bg-slate-800 hover:bg-slate-700 text-slate-300 font-black py-4 rounded-2xl text-center text-[10px] uppercase tracking-widest transition-all active:scale-95"
                        >
                            Hubungi Admin Disini
                        </a>
                    </div>

                    <!-- Selection Summary (Floating) -->
                    <div class="sticky top-8 bg-indigo-600 rounded-[40px] p-8 shadow-2xl shadow-indigo-600/30">
                        <h3 class="text-white/70 text-[10px] font-black uppercase tracking-[0.3em] mb-6">Detail Pesanan</h3>
                        <div v-if="form.product_id" class="space-y-4">
                            <div class="flex justify-between items-center text-white">
                                <span class="text-xs font-bold uppercase opacity-70">Item</span>
                                <span class="font-black uppercase tracking-tight">{{ game.products.find((p: any) => p.id === form.product_id)?.name }}</span>
                            </div>
                            <div class="h-px bg-white/20 w-full"></div>
                            <div class="flex justify-between items-end text-white">
                                <span class="text-xs font-bold uppercase opacity-70">Total</span>
                                <span class="text-2xl font-black tracking-tighter">Rp {{ game.products.find((p: any) => p.id === form.product_id)?.price.toLocaleString('id-ID') }}</span>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <CreditCard class="w-12 h-12 mx-auto mb-4 text-white/20" />
                            <p class="text-white/50 text-[10px] font-black uppercase tracking-widest leading-loose">Pilih produk & metode pembayaran <br/> untuk melihat detail</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
