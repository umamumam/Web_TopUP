<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { 
    Trash2, Plus, Image as ImageIcon, 
    Star, Settings, Share2, Info, Save 
} from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Landing Page', href: '/admin/landing-page' },
];

const props = defineProps<{
    banners: any[];
    popularGames: any[];
    allGames: any[];
    settings: any;
}>();


const bannerForm = useForm({
    title: '',
    image: null as File | null,
    image_url: '',
    link: '',
    order: 0
});

const settingsForm = useForm({
    social_facebook: props.settings.social_facebook || '',
    social_instagram: props.settings.social_instagram || '',
    social_youtube: props.settings.social_youtube || '',
    social_whatsapp: props.settings.social_whatsapp || '',
    footer_description: props.settings.footer_description || '',
    site_name: props.settings.site_name || 'VEINSTORE CLONE',
    site_logo: null as File | null
});

const popularForm = useForm({
    game_ids: props.popularGames.map(g => g.id)
});

const submitBanner = () => {
    bannerForm.post('/admin/landing-page/banners', {
        onSuccess: () => bannerForm.reset(),
        forceFormData: true
    });
};

const deleteBanner = (id: number) => {
    if (confirm('Yakin ingin menghapus banner ini?')) {
        router.delete(`/admin/landing-page/banners/${id}`);
    }
};



const submitSettings = () => {
    settingsForm.post('/admin/landing-page/settings', {
        forceFormData: true
    });
};

const submitPopular = () => {
    popularForm.post('/admin/landing-page/popular');
};

const toggleGame = (id: number) => {
    const index = popularForm.game_ids.indexOf(id);
    if (index > -1) {
        popularForm.game_ids.splice(index, 1);
    } else {
        popularForm.game_ids.push(id);
    }
};
</script>

<template>
    <Head title="Manajemen Landing Page" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-black uppercase tracking-tight text-slate-900">Landing Page Content</h1>
            </div>

            <!-- Flash Messages -->
            <div v-if="($page.props.flash as any)?.success" class="bg-emerald-50 border border-emerald-200 text-emerald-600 px-6 py-4 rounded-2xl flex items-center gap-3 animate-in fade-in slide-in-from-top-4">
                <div class="w-8 h-8 bg-emerald-500 rounded-full flex items-center justify-center text-white">
                    <Save class="w-4 h-4" />
                </div>
                <span class="text-sm font-black uppercase tracking-wider">{{ ($page.props.flash as any).success }}</span>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                
                <!-- Banner Management -->
                <div class="bg-white rounded-[32px] border border-slate-200 p-8 space-y-6 shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-indigo-500 rounded-2xl text-white">
                            <ImageIcon class="w-6 h-6" />
                        </div>
                        <h2 class="font-black uppercase tracking-wider text-slate-800">Banner Carousel</h2>
                    </div>

                    <form @submit.prevent="submitBanner" class="space-y-4 p-6 bg-slate-50 rounded-[24px] border border-slate-100">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-500 pl-1">Judul (Opsional)</label>
                                <input v-model="bannerForm.title" type="text" class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold p-3" />
                            </div>
                            <div class="space-y-1">
                                <label class="text-[10px] font-black uppercase text-slate-500 pl-1">Order</label>
                                <input v-model="bannerForm.order" type="number" class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold p-3" />
                            </div>
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-500 pl-1">Link Tujuan</label>
                            <input v-model="bannerForm.link" type="text" placeholder="https://..." class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold p-3" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-500 pl-1">Atau Link Gambar (External)</label>
                            <input v-model="bannerForm.image_url" type="text" placeholder="https://..." class="w-full bg-white border-slate-200 rounded-xl text-sm font-bold p-3" />
                        </div>
                        <div class="space-y-1">
                            <label class="text-[10px] font-black uppercase text-slate-500 pl-1">Upload File (Priority)</label>
                            <input @input="bannerForm.image = ($event.target as HTMLInputElement).files?.[0] || null" type="file" class="w-full text-xs text-slate-500 file:bg-indigo-50 file:border-none file:px-4 file:py-2 file:rounded-lg file:text-indigo-600 file:font-black file:uppercase file:mr-4" />
                            <div v-if="bannerForm.errors.image" class="text-rose-500 text-[10px] font-bold uppercase mt-1">{{ bannerForm.errors.image }}</div>
                        </div>
                        <button type="submit" :disabled="bannerForm.processing" class="w-full bg-indigo-600 hover:bg-indigo-500 text-white font-black py-4 rounded-xl text-xs uppercase transition-all flex items-center justify-center gap-2 shadow-lg shadow-indigo-500/20 active:scale-95">
                            <Plus class="w-4 h-4" /> Tambah Banner
                        </button>
                    </form>

                    <div class="space-y-3">
                        <div v-for="banner in banners" :key="banner.id" class="flex items-center gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-sm transition-all hover:bg-slate-50">
                            <img :src="banner.image ? '/storage/'+banner.image : banner.image_url" class="w-24 h-12 object-cover rounded-xl shadow-md" />
                            <div class="flex-grow">
                                <div class="text-xs font-black uppercase tracking-tight text-slate-800">{{ banner.title || 'Tanpa Judul' }}</div>
                                <div class="text-[10px] text-slate-500 font-bold uppercase tracking-widest">Urutan ke-{{ banner.order }}</div>
                            </div>
                            <button @click="deleteBanner(banner.id)" class="text-rose-500 hover:bg-rose-50 p-2.5 rounded-xl transition-all active:scale-90">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Popular Games -->
                <div class="bg-white rounded-[32px] border border-slate-200 p-8 space-y-6 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="p-3 bg-yellow-500 rounded-2xl text-white">
                                <Star class="w-6 h-6" />
                            </div>
                            <h2 class="font-black uppercase tracking-wider text-slate-800">Game Populer</h2>
                        </div>
                        <button @click="submitPopular" :disabled="popularForm.processing" class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-3 rounded-xl text-[10px] font-black uppercase tracking-widest transition-all shadow-md active:scale-95">
                            Simpan Perubahan
                        </button>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto pr-2 scrollbar-thin">
                        <button 
                            v-for="game in allGames" 
                            :key="game.id"
                            @click="toggleGame(game.id)"
                            class="p-4 rounded-[24px] border-2 transition-all text-left space-y-3 relative group overflow-hidden"
                            :class="popularForm.game_ids.includes(game.id) ? 'bg-indigo-50 border-indigo-500 shadow-lg shadow-indigo-500/10' : 'bg-white border-slate-100 hover:border-slate-200'"
                        >
                            <img :src="game.thumbnail" class="w-full aspect-square object-cover rounded-2xl" />
                            <div class="text-[10px] font-black uppercase leading-tight truncate text-slate-800">{{ game.name }}</div>
                            <div v-if="popularForm.game_ids.includes(game.id)" class="absolute top-2 right-2 bg-indigo-500 rounded-full p-2 shadow-lg shadow-indigo-500/50">
                                <Star class="w-3 h-3 text-white fill-current" />
                            </div>
                        </button>
                    </div>
                </div>

                <!-- Landing Page Settings -->
                <div class="lg:col-span-2 bg-white rounded-[32px] border border-slate-200 p-8 space-y-8 shadow-sm">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-6">
                        <div class="flex items-center gap-5">
                            <div class="p-4 bg-emerald-500 rounded-[24px] text-white shadow-lg shadow-emerald-500/20">
                                <Settings class="w-8 h-8" />
                            </div>
                            <div>
                                <h2 class="text-xl font-black uppercase tracking-tight text-slate-800">Profil Toko & Media Sosial</h2>
                                <p class="text-sm text-slate-500 font-medium">Atur identitas website Anda.</p>
                            </div>
                        </div>
                        <button type="button" @click="submitSettings" :disabled="settingsForm.processing" class="bg-emerald-600 hover:bg-emerald-500 text-white px-8 py-4 rounded-2xl text-xs font-black uppercase tracking-widest transition-all shadow-lg active:scale-95 flex items-center gap-2">
                            <Save class="w-5 h-5" /> Simpan Konfigurasi
                        </button>
                    </div>
                    <div v-if="Object.keys(settingsForm.errors).length > 0" class="p-4 bg-rose-50 border border-rose-100 rounded-2xl text-rose-500 text-xs font-bold uppercase tracking-wider">
                        Ada kesalahan pada input pengaturan. Silakan periksa kembali.
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mt-4">
                        <div class="space-y-6">
                            <div class="space-y-4">
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2 px-1">
                                    <Share2 class="w-4 h-4" /> Link Media Sosial
                                </h3>
                                <div class="grid grid-cols-1 gap-5">
                                    <div v-for="social in ['facebook', 'instagram', 'youtube', 'whatsapp']" :key="social" class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase text-slate-500 pl-2 capitalize tracking-widest">{{ social }} Link</label>
                                        <input v-model="(settingsForm as any)['social_' + social]" type="text" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-3.5 text-sm font-bold focus:bg-white focus:border-indigo-500 transition-all font-mono" placeholder="https://..." />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <div class="space-y-4">
                                <h3 class="text-xs font-black uppercase tracking-widest text-slate-400 flex items-center gap-2 px-1">
                                    <Info class="w-4 h-4" /> Informasi Tambahan
                                </h3>
                                <div class="space-y-5">
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase text-slate-500 pl-2 tracking-widest">Logo Platform</label>
                                        <div class="flex items-center gap-4 p-4 bg-slate-50 rounded-2xl border border-slate-200">
                                            <div class="w-16 h-16 rounded-xl bg-white border border-slate-200 flex items-center justify-center overflow-hidden shrink-0">
                                                <img v-if="props.settings.site_logo" :src="'/storage/' + props.settings.site_logo" class="w-full h-full object-contain p-1" />
                                                <ImageIcon v-else class="w-6 h-6 text-slate-300" />
                                            </div>
                                            <div class="flex-grow">
                                                <input @input="(settingsForm as any).site_logo = ($event.target as HTMLInputElement).files?.[0] || null" type="file" class="w-full text-xs text-slate-500 file:bg-indigo-50 file:border-none file:px-4 file:py-2 file:rounded-lg file:text-indigo-600 file:font-black file:uppercase file:mr-4" />
                                                <p class="mt-1 text-[8px] font-bold text-slate-400 uppercase tracking-widest">Rekomendasi: 512x512px (Transparent PNG/WebP)</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase text-slate-500 pl-2 tracking-widest">Nama Platform</label>
                                        <input v-model="settingsForm.site_name" type="text" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-3.5 text-sm font-bold focus:bg-white focus:border-indigo-500 transition-all" />
                                    </div>
                                    <div class="space-y-1.5">
                                        <label class="text-[10px] font-black uppercase text-slate-500 pl-2 tracking-widest">Deskripsi Footer</label>
                                        <textarea v-model="settingsForm.footer_description" class="w-full bg-slate-50 border-slate-200 rounded-2xl px-5 py-4 text-sm font-medium h-40 focus:bg-white focus:border-indigo-500 transition-all leading-relaxed"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.scrollbar-thin::-webkit-scrollbar {
    width: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: transparent;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #334155;
    border-radius: 10px;
}
</style>
