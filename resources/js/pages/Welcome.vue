<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    Smartphone,
    Gamepad2,
    Monitor,
    Search,
    History,
    LogIn,
    ChevronRight,
    MessageCircle,
    Instagram,
    Youtube,
    Facebook,
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

    return props.categories
        .map((cat: any) => ({
            ...cat,
            games: cat.games.filter((game: any) => {
                const matchSearch = game.name
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase());
                const matchTab =
                    activeTab.value === 'Semua' || cat.name === activeTab.value;

                return matchSearch && matchTab;
            }),
        }))
        .filter((cat: any) => cat.games.length > 0);
});

const getIcon = (name: string) => {
    switch (name.toLowerCase()) {
        case 'mobile games':
            return Smartphone;
        case 'pc games':
            return Monitor;
        default:
            return Gamepad2;
    }
};

const currentBannerIndex = ref(0);
let bannerInterval: any = null;

onMounted(() => {
    if (props.banners.length > 1) {
        bannerInterval = setInterval(() => {
            currentBannerIndex.value =
                (currentBannerIndex.value + 1) % props.banners.length;
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

    <div
        class="min-h-screen bg-[#0b0e11] font-sans text-slate-50 selection:bg-indigo-500 selection:text-white"
    >
        <!-- Navbar -->
        <nav
            class="sticky top-0 z-[100] border-b border-slate-800 bg-[#0b0e11]/80 backdrop-blur-xl"
        >
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-20 items-center justify-between gap-8">
                    <!-- Logo -->
                    <Link href="/" class="flex shrink-0 items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl shadow-lg shadow-indigo-500/20 overflow-hidden"
                            :class="settings.site_logo ? 'bg-white/10' : 'bg-gradient-to-br from-indigo-500 to-purple-600'"
                        >
                            <img v-if="settings.site_logo" :src="'/storage/' + settings.site_logo" class="h-full w-full object-contain p-1" />
                            <Gamepad2 v-else class="h-6 w-6 text-white" />
                        </div>
                        <span
                            class="text-xl font-black tracking-tighter text-slate-50 uppercase"
                        >
                            {{ settings.site_name || 'VEINSTORE' }}
                        </span>
                    </Link>

                    <!-- Search Bar -->
                    <div
                        class="group relative hidden max-w-2xl flex-grow md:block"
                    >
                        <div
                            class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-5"
                        >
                            <Search
                                class="h-4 w-4 text-slate-500 transition-colors group-focus-within:text-indigo-500"
                            />
                        </div>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Cari Game atau Voucher..."
                            class="w-full rounded-2xl border-2 border-slate-800 bg-slate-900 py-3 pr-4 pl-12 text-sm font-medium text-slate-200 transition-all outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10"
                        />
                    </div>

                    <!-- Nav Links -->
                    <div class="flex items-center gap-2 sm:gap-6">
                        <Link
                            href="/"
                            class="hidden items-center gap-2 rounded-full border border-indigo-500/20 bg-indigo-500/10 px-4 py-2 text-xs font-black tracking-widest text-indigo-400 uppercase lg:flex"
                        >
                            <Smartphone class="h-4 w-4" />
                            <span>Topup</span>
                        </Link>
                        <Link
                            href="#"
                            class="hidden items-center gap-2 text-xs font-black tracking-widest text-slate-400 uppercase transition-colors hover:text-white lg:flex"
                        >
                            <History class="h-4 w-4" />
                            <span>Cek Transaksi</span>
                        </Link>
                        <Link
                            href="/login"
                            class="flex items-center gap-2 rounded-2xl bg-indigo-600 p-3 text-center text-xs font-black tracking-widest text-white uppercase shadow-lg shadow-indigo-500/20 transition-all hover:bg-indigo-500 active:scale-95 sm:px-6 sm:py-3"
                        >
                            <LogIn class="h-4 w-4" />
                            <span class="hidden sm:inline">Masuk</span>
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero / Banner Section -->
        <div
            v-if="banners.length > 0"
            class="mx-auto max-w-7xl px-4 py-4 sm:px-6 sm:py-8 lg:px-8"
        >
            <div
                class="group relative aspect-[16/9] overflow-hidden rounded-[24px] bg-slate-900 shadow-2xl shadow-indigo-500/10 sm:rounded-[40px] md:aspect-[21/7]"
            >
                <div
                    v-for="(banner, index) in banners"
                    :key="banner.id"
                    class="absolute inset-0 transition-opacity duration-1000 ease-in-out"
                    :class="
                        index === currentBannerIndex
                            ? 'z-10 opacity-100'
                            : 'z-0 opacity-0'
                    "
                >
                    <img
                        :src="
                            banner.image
                                ? '/storage/' + banner.image
                                : banner.image_url
                        "
                        class="h-full w-full object-cover transition-transform duration-1000 group-hover:scale-105"
                    />
                    <div
                        class="absolute inset-0 flex items-center bg-gradient-to-r from-black/80 via-black/40 to-transparent px-6 sm:px-12 md:px-20"
                    >
                        <div class="max-w-lg space-y-2 sm:space-y-4">
                            <div
                                class="inline-flex items-center gap-2 rounded-full bg-indigo-500 px-2 py-0.5 text-[8px] font-black tracking-widest text-white uppercase sm:px-3 sm:py-1 sm:text-[10px]"
                            >
                                🔥 {{ banner.title || 'Promo Terbatas' }}
                            </div>
                            <h2
                                class="text-xl leading-none font-black tracking-tighter text-white uppercase sm:text-3xl md:text-5xl"
                                v-html="banner.title || 'Promo <br/>Khusus'"
                            ></h2>
                            <Link
                                v-if="banner.link"
                                :href="banner.link"
                                class="mt-2 inline-block rounded-xl bg-white px-4 py-2 text-[10px] font-black tracking-widest text-black uppercase shadow-xl transition-all hover:bg-indigo-500 hover:text-white active:scale-95 sm:mt-0 sm:rounded-2xl sm:px-8 sm:py-4 sm:text-xs"
                            >
                                Cek Sekarang
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Pagination Dots -->
                <div
                    v-if="banners.length > 1"
                    class="absolute bottom-10 left-1/2 z-20 flex -translate-x-1/2 gap-3"
                >
                    <button
                        v-for="(_, index) in banners"
                        :key="index"
                        @click="currentBannerIndex = index"
                        class="h-2.5 rounded-full shadow-lg transition-all duration-300"
                        :class="
                            index === currentBannerIndex
                                ? 'w-12 bg-indigo-500'
                                : 'w-2.5 bg-white/30 hover:bg-white/50'
                        "
                    ></button>
                </div>

                <!-- Navigation Arrows -->
                <button
                    v-if="banners.length > 1"
                    @click="
                        currentBannerIndex =
                            (currentBannerIndex - 1 + banners.length) %
                            banners.length
                    "
                    class="absolute top-1/2 left-6 z-20 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-black/20 text-white opacity-0 backdrop-blur-md transition-opacity group-hover:opacity-100 hover:bg-black/40"
                >
                    <ChevronRight class="h-6 w-6 rotate-180" />
                </button>
                <button
                    v-if="banners.length > 1"
                    @click="
                        currentBannerIndex =
                            (currentBannerIndex + 1) % banners.length
                    "
                    class="absolute top-1/2 right-6 z-20 flex h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full border border-white/10 bg-black/20 text-white opacity-0 backdrop-blur-md transition-opacity group-hover:opacity-100 hover:bg-black/40"
                >
                    <ChevronRight class="h-6 w-6" />
                </button>
            </div>
        </div>

        <!-- Quick Stats/Flash Sale Style (Popular Section) -->
        <div
            v-if="popularGames.length > 0"
            class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-12 lg:px-8"
        >
            <div
                class="rounded-[28px] border-b-4 border-indigo-600 bg-slate-900 p-6 shadow-2xl ring-1 ring-slate-800 sm:rounded-[40px] sm:p-10"
            >
                <div class="mb-6 flex items-center gap-3 sm:mb-10 sm:gap-4">
                    <div
                        class="h-6 w-2 rounded-full bg-indigo-500 sm:h-8"
                    ></div>
                    <h2
                        class="text-xl font-black tracking-tight text-slate-100 uppercase sm:text-2xl"
                    >
                        🔥 Populer Sekarang!
                    </h2>
                </div>

                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-6 lg:grid-cols-3"
                >
                    <Link
                        v-for="game in popularGames"
                        :key="game.id"
                        :href="show.url(game.slug)"
                        class="group relative flex items-center gap-4 rounded-[20px] border border-slate-700/50 bg-slate-800/50 p-4 transition-all duration-300 hover:bg-slate-800 sm:gap-6 sm:rounded-3xl"
                    >
                        <img
                            :src="game.thumbnail"
                            class="h-16 w-16 rounded-xl object-cover shadow-2xl transition-transform duration-500 group-hover:scale-110 sm:h-20 sm:w-20 sm:rounded-2xl"
                        />
                        <div>
                            <h3
                                class="text-sm font-black tracking-tight text-slate-100 uppercase transition-colors group-hover:text-indigo-400 sm:text-base"
                            >
                                {{ game.name }}
                            </h3>
                            <p
                                class="mt-1 text-[9px] font-bold tracking-widest text-slate-500 uppercase sm:text-[10px]"
                            >
                                Official
                            </p>
                        </div>
                        <ChevronRight
                            class="ml-auto h-5 w-5 text-slate-700 transition-colors group-hover:text-indigo-500"
                        />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Category Tabs -->
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <div class="scrollbar-none flex gap-4 overflow-x-auto pb-4">
                <button
                    v-for="tab in [
                        'Semua',
                        'Mobile Games',
                        'PC Games',
                        'Voucher',
                    ]"
                    :key="tab"
                    @click="activeTab = tab"
                    class="rounded-2xl border-2 px-8 py-4 text-xs font-black tracking-widest whitespace-nowrap uppercase transition-all duration-300 active:scale-95"
                    :class="
                        activeTab === tab
                            ? 'border-indigo-500 bg-indigo-600 text-white shadow-[0_10px_30px_rgba(79,70,229,0.3)]'
                            : 'border-slate-800 bg-slate-900 text-slate-400 hover:border-slate-700'
                    "
                >
                    {{ tab }}
                </button>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div
                v-for="category in filteredCategories"
                :key="category.id"
                class="mb-20"
            >
                <div class="mb-12 flex items-center gap-5">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-800 bg-slate-900 text-indigo-500 shadow-xl"
                    >
                        <component
                            :is="getIcon(category.name)"
                            class="h-6 w-6"
                        />
                    </div>
                    <div>
                        <h2
                            class="text-2xl font-black tracking-tight text-slate-100 uppercase"
                        >
                            {{ category.name }}
                        </h2>
                        <p
                            class="mt-1 text-[10px] font-bold tracking-[0.2em] text-slate-500 uppercase"
                        >
                            {{ category.games.length }} Game Tersedia
                        </p>
                    </div>
                    <div class="ml-8 h-px flex-grow bg-slate-800/50"></div>
                </div>

                <div
                    class="grid grid-cols-2 gap-8 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5"
                >
                    <Link
                        v-for="game in category.games"
                        :key="game.id"
                        :href="show.url(game.slug)"
                        class="group relative overflow-hidden rounded-[32px] bg-[#1c2128] ring-1 ring-white/5 transition-all duration-500 hover:-translate-y-3 hover:shadow-[0_20px_40px_rgba(0,0,0,0.4)] hover:ring-indigo-500/50"
                    >
                        <div class="relative aspect-[3/4] overflow-hidden">
                            <img
                                :src="game.thumbnail"
                                class="h-full w-full object-cover transition-transform duration-700 group-hover:scale-110 group-hover:rotate-2"
                            />
                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-[#0b0e11] via-[#0b0e11]/50 to-transparent opacity-0 transition-opacity duration-500 group-hover:opacity-90"
                            ></div>

                            <!-- Title Overlay at bottom -->
                            <div
                                class="pointer-events-none absolute inset-x-0 bottom-0 flex translate-y-4 flex-col items-center p-4 pb-6 opacity-0 transition-all duration-500 group-hover:translate-y-0 group-hover:opacity-100"
                            >
                                <h3
                                    class="line-clamp-2 w-full px-2 text-center text-[11px] font-black tracking-wider text-white uppercase drop-shadow-[0_4px_8px_rgba(0,0,0,0.9)] filter sm:text-[13px]"
                                >
                                    {{ game.name }}
                                </h3>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-800 bg-[#0b0e11] pt-20 pb-12">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mb-20 grid grid-cols-1 gap-16 lg:grid-cols-12">
                    <div class="space-y-8 lg:col-span-5">
                        <Link href="/" class="flex items-center gap-3">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-2xl shadow-lg shadow-indigo-500/20 overflow-hidden"
                                :class="settings.site_logo ? 'bg-white/10' : 'bg-gradient-to-br from-indigo-500 to-purple-600'"
                            >
                                <img v-if="settings.site_logo" :src="'/storage/' + settings.site_logo" class="h-full w-full object-contain p-1.5" />
                                <Gamepad2 v-else class="h-8 w-8 text-white" />
                            </div>
                            <span
                                class="text-2xl font-black tracking-tighter text-slate-50 uppercase"
                            >
                                {{ settings.site_name || 'VEINSTORE' }}
                            </span>
                        </Link>
                        <p
                            class="max-w-sm text-sm leading-relaxed text-slate-500"
                        >
                            {{
                                settings.footer_description ||
                                'Platform top up game terpercaya di Indonesia.'
                            }}
                        </p>
                        <div class="flex gap-4">
                            <a
                                v-for="social in [
                                    {
                                        icon: Instagram,
                                        key: 'social_instagram',
                                    },
                                    { icon: Youtube, key: 'social_youtube' },
                                    { icon: Facebook, key: 'social_facebook' },
                                    {
                                        icon: MessageCircle,
                                        key: 'social_whatsapp',
                                    },
                                ]"
                                :key="social.key"
                                :href="settings[social.key] || '#'"
                                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-800 bg-slate-900 text-slate-500 transition-all hover:bg-indigo-600 hover:text-white"
                            >
                                <component :is="social.icon" class="h-5 w-5" />
                            </a>
                        </div>
                    </div>

                    <div
                        class="grid grid-cols-2 gap-12 sm:grid-cols-3 lg:col-span-7"
                    >
                        <div
                            v-for="menu in [
                                {
                                    name: 'Peta Situs',
                                    items: [
                                        'Beranda',
                                        'Masuk',
                                        'Cek Transaksi',
                                        'Leaderboard',
                                    ],
                                },
                                {
                                    name: 'Dukungan',
                                    items: [
                                        'WhatsApp',
                                        'Instagram',
                                        'Email',
                                        'Saluran WA',
                                    ],
                                },
                                {
                                    name: 'Legalitas',
                                    items: [
                                        'Kebijakan Privasi',
                                        'Syarat & Ketentuan',
                                    ],
                                },
                            ]"
                            :key="menu.name"
                        >
                            <h4
                                class="mb-8 text-xs font-black tracking-[0.2em] text-slate-100 uppercase"
                            >
                                {{ menu.name }}
                            </h4>
                            <ul class="space-y-4">
                                <li v-for="item in menu.items" :key="item">
                                    <Link
                                        href="#"
                                        class="text-sm font-medium text-slate-500 transition-colors hover:text-indigo-400"
                                        >{{ item }}</Link
                                    >
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="mb-8 h-px w-full bg-slate-800/50"></div>
                <div
                    class="flex flex-col items-center justify-between gap-6 text-slate-600 sm:flex-row"
                >
                    <p class="text-xs font-medium">
                        &copy; 2026 {{ settings.site_name || 'VEINSTORE' }}.
                        Platform Resmi Untuk Semua Kebutuhan TopUp.
                    </p>
                    <div class="flex gap-8">
                        <img
                            v-for="i in 5"
                            :key="i"
                            :src="'https://veinstore.id/storage/payments/QRIS.webp'"
                            class="h-4 opacity-30 grayscale invert"
                        />
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
