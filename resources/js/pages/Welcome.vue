<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { 
    Smartphone, Gamepad2, Monitor, Search, 
    History, LogIn, ChevronRight, MessageCircle, 
    Instagram, Youtube, Facebook
} from 'lucide-vue-next';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { show } from '@/routes/topup';

const props = defineProps<{
    categories: any[];
    paymentMethods: any[];
    banners: any[];
    popularGames: any[];
    settings: any;
}>();

const searchQuery = ref('');
const activeTab = ref('Semua');

const filteredCategories = computed(() => {
    if (!searchQuery.value && activeTab.value === 'Semua') {
        return props.categories;
    }

    return props.categories.map((cat: any) => ({
        ...cat,
        games: cat.games.filter((game: any) => {

            const matchSearch = game.name.toLowerCase().includes(searchQuery.value.toLowerCase());
            const matchTab = activeTab.value === 'Semua' || cat.name === activeTab.value;

            return matchSearch && matchTab;
        })
    })).filter((cat: any) => cat.games.length > 0);
});

const getIcon = (name: string) => {
    switch (name.toLowerCase()) {
        case 'mobile games': return Smartphone;
        case 'pc games': return Monitor;
        default: return Gamepad2;
    }
};

const currentBannerIndex = ref(0);
let bannerInterval: any = null;

onMounted(() => {
    if (props.banners.length > 1) {
        bannerInterval = setInterval(() => {
            currentBannerIndex.value = (currentBannerIndex.value + 1) % props.banners.length;
        }, 5000);
    }
});

onUnmounted(() => {
    if (bannerInterval) {
        clearInterval(bannerInterval);
    }
});
</script>

<template>
    <Head :title="settings.site_name || 'Top Up Game Terpercaya'" />

    <div class="min-h-screen bg-[#0b0e11] text-slate-50 font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Navbar -->
        <nav class="sticky top-0 z-[100] bg-[#0b0e11]/80 backdrop-blur-xl border-b border-slate-800">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20 gap-8">
                    <!-- Logo -->
                    <Link href="/" class="flex items-center gap-3 shrink-0">
                        <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                            <Gamepad2 class="w-6 h-6 text-white" />
                        </div>
                        <span class="text-xl font-black uppercase tracking-tighter text-slate-50">
                            {{ settings.site_name || 'VEINSTORE' }}
                        </span>
                    </Link>

                    <!-- Search Bar -->
                    <div class="flex-grow max-w-2xl relative group hidden md:block">
                        <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                            <Search class="w-4 h-4 text-slate-500 group-focus-within:text-indigo-500 transition-colors" />
                        </div>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Cari Game atau Voucher..."
                            class="w-full bg-slate-900 border-2 border-slate-800 rounded-2xl py-3 pl-12 pr-4 focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all font-medium text-sm text-slate-200"
                        />
                    </div>

                    <!-- Nav Links -->
                    <div class="flex items-center gap-2 sm:gap-6">
                        <Link href="/" class="hidden lg:flex items-center gap-2 text-xs font-black uppercase tracking-widest text-indigo-400 bg-indigo-500/10 px-4 py-2 rounded-full border border-indigo-500/20">
                            <Smartphone class="w-4 h-4" />
                            <span>Topup</span>
                        </Link>
                        <Link href="#" class="hidden lg:flex items-center gap-2 text-xs font-black uppercase tracking-widest text-slate-400 hover:text-white transition-colors">
                            <History class="w-4 h-4" />
                            <span>Cek Transaksi</span>
                        </Link>
                        <Link href="/login" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-2xl text-xs font-black uppercase tracking-widest transition-all active:scale-95 flex items-center gap-2 shadow-lg shadow-indigo-500/20 text-center">
                            <LogIn class="w-4 h-4" />
                            <span>Masuk</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero / Banner Section -->
        <div v-if="banners.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8">
            <div class="relative rounded-[24px] sm:rounded-[40px] overflow-hidden group aspect-[16/9] md:aspect-[21/7] bg-slate-900 shadow-2xl shadow-indigo-500/10">
                <div 
                    v-for="(banner, index) in banners" 
                    :key="banner.id"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="index === currentBannerIndex ? 'opacity-100 z-10' : 'opacity-0 z-0'"
                >
                    <img 
                        :src="banner.image ? '/storage/' + banner.image : banner.image_url" 
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-1000"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-transparent flex items-center px-6 sm:px-12 md:px-20">
                        <div class="max-w-lg space-y-2 sm:space-y-4">
                            <div class="inline-flex items-center gap-2 bg-indigo-500 px-2 py-0.5 sm:px-3 sm:py-1 rounded-full text-[8px] sm:text-[10px] font-black uppercase tracking-widest text-white">
                                🔥 {{ banner.title || 'Promo Terbatas' }}
                            </div>
                            <h2 class="text-xl sm:text-3xl md:text-5xl font-black uppercase tracking-tighter leading-none text-white" v-html="banner.title || 'Promo <br/>Khusus'"></h2>
                            <Link v-if="banner.link" :href="banner.link" class="inline-block mt-2 sm:mt-0 bg-white text-black px-4 py-2 sm:px-8 sm:py-4 rounded-xl sm:rounded-2xl text-[10px] sm:text-xs font-black uppercase tracking-widest hover:bg-indigo-500 hover:text-white transition-all shadow-xl active:scale-95">
                                Cek Sekarang
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination Dots -->
                <div v-if="banners.length > 1" class="absolute bottom-10 left-1/2 -translate-x-1/2 z-20 flex gap-3">
                    <button 
                        v-for="(_, index) in banners" 
                        :key="index"
                        @click="currentBannerIndex = index"
                        class="h-2.5 rounded-full transition-all duration-300 shadow-lg"
                        :class="index === currentBannerIndex ? 'bg-indigo-500 w-12' : 'bg-white/30 w-2.5 hover:bg-white/50'"
                    ></button>
                </div>

                <!-- Navigation Arrows -->
                <button 
                    v-if="banners.length > 1"
                    @click="currentBannerIndex = (currentBannerIndex - 1 + banners.length) % banners.length"
                    class="absolute left-6 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-black/20 backdrop-blur-md border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/40 text-white"
                >
                    <ChevronRight class="w-6 h-6 rotate-180" />
                </button>
                <button 
                    v-if="banners.length > 1"
                    @click="currentBannerIndex = (currentBannerIndex + 1) % banners.length"
                    class="absolute right-6 top-1/2 -translate-y-1/2 z-20 w-12 h-12 rounded-full bg-black/20 backdrop-blur-md border border-white/10 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-black/40 text-white"
                >
                    <ChevronRight class="w-6 h-6" />
                </button>
            </div>
        </div>

        <!-- Quick Stats/Flash Sale Style (Popular Section) -->
        <div v-if="popularGames.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12">
            <div class="bg-slate-900 ring-1 ring-slate-800 rounded-[28px] sm:rounded-[40px] p-6 sm:p-10 border-b-4 border-indigo-600 shadow-2xl">
                <div class="flex items-center gap-3 sm:gap-4 mb-6 sm:mb-10">
                    <div class="w-2 h-6 sm:h-8 bg-indigo-500 rounded-full"></div>
                    <h2 class="text-xl sm:text-2xl font-black uppercase tracking-tight text-slate-100">🔥 Populer Sekarang!</h2>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <Link 
                        v-for="game in popularGames" 
                        :key="game.id" 
                        :href="show.url(game.slug)" 
                        class="group relative flex items-center gap-4 sm:gap-6 p-4 rounded-[20px] sm:rounded-3xl bg-slate-800/50 hover:bg-slate-800 border border-slate-700/50 transition-all duration-300"
                    >
                        <img :src="game.thumbnail" class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl sm:rounded-2xl object-cover shadow-2xl group-hover:scale-110 transition-transform duration-500" />
                        <div>
                            <h3 class="font-black text-sm sm:text-base uppercase tracking-tight text-slate-100 group-hover:text-indigo-400 transition-colors">{{ game.name }}</h3>
                            <p class="text-slate-500 text-[9px] sm:text-[10px] font-bold uppercase tracking-widest mt-1">Official</p>
                        </div>
                        <ChevronRight class="w-5 h-5 text-slate-700 group-hover:text-indigo-500 transition-colors ml-auto" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex gap-4 overflow-x-auto pb-4 scrollbar-none">
                <button 
                    v-for="tab in ['Semua', 'Mobile Games', 'PC Games', 'Voucher']" 
                    :key="tab"
                    @click="activeTab = tab"
                    class="px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all duration-300 border-2 whitespace-nowrap active:scale-95"
                    :class="activeTab === tab 
                        ? 'bg-indigo-600 border-indigo-500 text-white shadow-[0_10px_30px_rgba(79,70,229,0.3)]' 
                        : 'bg-slate-900 border-slate-800 text-slate-400 hover:border-slate-700'"
                >
                    {{ tab }}
                </button>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div v-for="category in filteredCategories" :key="category.id" class="mb-20">
                <div class="flex items-center gap-5 mb-12">
                    <div class="w-12 h-12 bg-slate-900 rounded-2xl border border-slate-800 flex items-center justify-center text-indigo-500 shadow-xl">
                        <component :is="getIcon(category.name)" class="w-6 h-6" />
                    </div>
                    <div>
                        <h2 class="text-2xl font-black uppercase tracking-tight text-slate-100">{{ category.name }}</h2>
                        <p class="text-slate-500 text-[10px] font-bold uppercase tracking-[0.2em] mt-1">{{ category.games.length }} Game Tersedia</p>
                    </div>
                    <div class="flex-grow h-px bg-slate-800/50 ml-8"></div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-8">
                    <Link 
                        v-for="game in category.games" 
                        :key="game.id"
                        :href="show.url(game.slug)"
                        class="group relative bg-[#1c2128] rounded-[32px] overflow-hidden transition-all duration-500 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] ring-1 ring-white/5 hover:ring-indigo-500/50"
                    >
                        <div class="aspect-[3/4] relative overflow-hidden">
                            <img 
                                :src="game.thumbnail" 
                                class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:rotate-2"
                            />
                            <div class="absolute inset-0 bg-gradient-to-t from-[#0b0e11] via-[#0b0e11]/50 to-transparent opacity-0 group-hover:opacity-90 transition-opacity duration-500 pointer-events-none"></div>
                            
                            <!-- Title Overlay at bottom -->
                            <div class="absolute inset-x-0 bottom-0 p-4 pb-6 flex flex-col items-center opacity-0 group-hover:opacity-100 transition-all duration-500 translate-y-4 group-hover:translate-y-0 pointer-events-none">
                                <h3 class="font-black text-center text-[11px] sm:text-[13px] uppercase tracking-wider text-white drop-shadow-[0_4px_8px_rgba(0,0,0,0.9)] line-clamp-2 w-full px-2 filter">
                                    {{ game.name }}
                                </h3>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-[#0b0e11] border-t border-slate-800 pt-20 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-16 mb-20">
                    <div class="lg:col-span-5 space-y-8">
                        <Link href="/" class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg shadow-indigo-500/20">
                                <Gamepad2 class="w-8 h-8 text-white" />
                            </div>
                            <span class="text-2xl font-black uppercase tracking-tighter text-slate-50">
                                {{ settings.site_name || 'VEINSTORE' }}
                            </span>
                        </Link>
                        <p class="text-slate-500 text-sm leading-relaxed max-w-sm">
                            {{ settings.footer_description || 'Platform top up game terpercaya di Indonesia.' }}
                        </p>
                        <div class="flex gap-4">
                            <a 
                                v-for="social in [
                                    { icon: Instagram, key: 'social_instagram' }, 
                                    { icon: Youtube, key: 'social_youtube' }, 
                                    { icon: Facebook, key: 'social_facebook' }, 
                                    { icon: MessageCircle, key: 'social_whatsapp' }
                                ]" 
                                :key="social.key" 
                                :href="settings[social.key] || '#'" 
                                class="w-10 h-10 bg-slate-900 rounded-xl flex items-center justify-center text-slate-500 hover:text-white hover:bg-indigo-600 transition-all border border-slate-800"
                            >
                                <component :is="social.icon" class="w-5 h-5" />
                            </a>
                        </div>
                    </div>

                    <div class="lg:col-span-7 grid grid-cols-2 sm:grid-cols-3 gap-12">
                        <div v-for="menu in [
                            { name: 'Peta Situs', items: ['Beranda', 'Masuk', 'Cek Transaksi', 'Leaderboard'] },
                            { name: 'Dukungan', items: ['WhatsApp', 'Instagram', 'Email', 'Saluran WA'] },
                            { name: 'Legalitas', items: ['Kebijakan Privasi', 'Syarat & Ketentuan'] }
                        ]" :key="menu.name">
                            <h4 class="text-xs font-black uppercase tracking-[0.2em] text-slate-100 mb-8">{{ menu.name }}</h4>
                            <ul class="space-y-4">
                                <li v-for="item in menu.items" :key="item">
                                    <Link href="#" class="text-slate-500 hover:text-indigo-400 text-sm font-medium transition-colors">{{ item }}</Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="h-px bg-slate-800/50 w-full mb-8"></div>
                <div class="flex flex-col sm:flex-row justify-between items-center gap-6 text-slate-600">
                    <p class="text-xs font-medium">&copy; 2026 {{ settings.site_name || 'VEINSTORE' }}. Platform Resmi Untuk Semua Kebutuhan TopUp.</p>
                    <div class="flex gap-8">
                        <img v-for="i in 5" :key="i" :src="'https://veinstore.id/storage/payments/QRIS.webp'" class="h-4 grayscale opacity-30 invert" />
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
