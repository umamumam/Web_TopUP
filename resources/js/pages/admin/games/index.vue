<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Gamepad2, Plus, Pencil, Trash2, Image } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    games: any[];
    categories: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Game', href: '/admin/games' },
];

const form = useForm({
    id: null as number | null,
    category_id: '' as string | number,
    name: '',
    thumbnail: '',
    input_label: 'Masukkan User ID',
    has_server: false,
});

const isEditing = computed(() => form.id !== null);

const editGame = (game: any) => {
    form.id = game.id;
    form.category_id = game.category_id;
    form.name = game.name;
    form.thumbnail = game.thumbnail;
    form.input_label = game.input_label;
    form.has_server = game.has_server ? true : false;
    
    // Scroll to top of form
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelEdit = () => {
    form.reset();
    form.id = null;
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/games/${form.id}`, {
            onSuccess: () => {
                cancelEdit();
                alert('Game berhasil diperbarui!');
            }
        });
    } else {
        form.post('/admin/games', {
            onSuccess: () => {
                form.reset();
                alert('Game berhasil ditambahkan!');
            }
        });
    }
};

const deleteGame = (id: number) => {
    if (confirm('Hapus game ini?')) {
        form.delete(`/admin/games/${id}`);
    }
};
</script>

<template>
    <Head title="Kelola Game" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-orange-500 rounded-3xl shadow-lg shadow-orange-500/20 text-white">
                        <Gamepad2 class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tight">Koleksi Game</h1>
                        <p class="text-slate-500 font-medium">Daftar game yang tersedia untuk Top Up.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 xl:grid-cols-4 gap-8">
                <!-- Form Section -->
                <div class="xl:col-span-1">
                    <div class="sticky top-6 bg-white dark:bg-slate-900 p-8 rounded-3xl border-2 border-slate-200 dark:border-slate-800 shadow-xl self-start transition-all" :class="isEditing ? 'border-orange-500/50 shadow-orange-500/5' : ''">
                        <h2 class="text-xl font-bold mb-6 flex items-center justify-between">
                            <span class="flex items-center gap-2 text-slate-900 dark:text-white">
                                <Plus v-if="!isEditing" class="w-5 h-5 text-orange-500" />
                                <Pencil v-else class="w-5 h-5 text-orange-500" />
                                {{ isEditing ? 'Edit Game' : 'Tambah Game' }}
                            </span>
                            <button v-if="isEditing" @click="cancelEdit" class="text-xs text-slate-400 hover:text-red-500 font-bold uppercase tracking-widest transition-colors">Batal</button>
                        </h2>
                        
                        <form @submit.prevent="submit" class="space-y-5">
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Kategori</label>
                                <select v-model="form.category_id" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/5 outline-none transition-all appearance-none font-bold">
                                    <option value="" disabled>Pilih Kategori</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Nama Game</label>
                                <input v-model="form.name" type="text" placeholder="Contoh: Mobile Legends" class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/5 outline-none transition-all font-bold" />
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Thumbnail URL</label>
                                <input v-model="form.thumbnail" type="text" placeholder="https://..." class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/5 outline-none transition-all font-medium text-sm" />
                            </div>
                            
                            <div class="space-y-2">
                                <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Label Input ID</label>
                                <input v-model="form.input_label" type="text" placeholder="Masukkan User ID..." class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3 focus:border-orange-500 focus:ring-4 focus:ring-orange-500/5 outline-none transition-all font-bold" />
                            </div>
                            
                            <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-2xl border border-slate-100 dark:border-slate-800 flex items-center justify-between">
                                <label for="has_server" class="text-xs font-bold uppercase text-slate-500 tracking-tight cursor-pointer">Butuh Input Server ID?</label>
                                <div class="relative inline-flex items-center cursor-pointer">
                                    <input v-model="form.has_server" type="checkbox" id="has_server" class="sr-only peer" />
                                    <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-orange-500 rounded-full"></div>
                                </div>
                            </div>
                            
                            <button type="submit" :disabled="form.processing" class="w-full bg-orange-600 hover:bg-orange-500 disabled:bg-slate-400 text-white font-black py-4 rounded-2xl transition-all active:scale-95 shadow-lg shadow-orange-500/20 uppercase tracking-widest text-sm">
                                {{ isEditing ? 'Simpan Perubahan' : 'Tambah Game' }}
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Games List Section -->
                <div class="xl:col-span-3">
                    <div class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                        <div v-for="game in games" :key="game.id" class="group bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 p-6 flex gap-5 hover:border-orange-500/50 hover:shadow-2xl hover:shadow-orange-500/10 transition-all duration-300">
                            <div class="w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 overflow-hidden flex-shrink-0 shadow-inner group-hover:scale-110 transition-transform duration-500">
                                <img v-if="game.thumbnail" :src="game.thumbnail" class="w-full h-full object-cover" />
                                <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
                                    <Image class="w-8 h-8" />
                                </div>
                            </div>
                            <div class="flex-1 flex flex-col justify-between py-1">
                                <div>
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="px-2 py-0.5 bg-orange-500/10 text-orange-500 rounded-lg text-[9px] font-black uppercase tracking-widest border border-orange-500/20">
                                            {{ game.category?.name }}
                                        </span>
                                    </div>
                                    <h3 class="text-lg font-black tracking-tight leading-tight transition-colors group-hover:text-orange-500">{{ game.name }}</h3>
                                    <p class="text-[10px] text-slate-400 font-bold uppercase mt-1 tracking-widest">{{ game.slug }}</p>
                                </div>
                                <div class="flex gap-2 mt-4">
                                    <button @click="editGame(game)" class="flex-1 bg-slate-100 dark:bg-slate-800 hover:bg-orange-500 hover:text-white p-2.5 rounded-xl transition-all flex items-center justify-center gap-2 text-xs font-bold shadow-sm active:scale-95 group/btn">
                                        <Pencil class="w-3.5 h-3.5 group-hover/btn:rotate-12 transition-transform" />
                                        <span>Edit</span>
                                    </button>
                                    <button @click="deleteGame(game.id)" class="bg-red-50 dark:bg-red-500/10 hover:bg-red-500 hover:text-white p-2.5 rounded-xl text-red-500 transition-all flex items-center justify-center w-11 shadow-sm active:scale-95">
                                        <Trash2 class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Empty State -->
                    <div v-if="games.length === 0" class="flex flex-col items-center justify-center py-24 bg-slate-50/50 dark:bg-slate-900/50 rounded-[48px] border-4 border-dashed border-slate-200 dark:border-slate-800">
                        <div class="p-8 bg-white dark:bg-slate-800 rounded-[32px] shadow-2xl mb-6 text-slate-300 scale-125">
                            <Gamepad2 class="w-12 h-12" />
                        </div>
                        <h3 class="text-2xl font-black">Belum ada game</h3>
                        <p class="text-slate-500 font-medium mt-2 max-w-xs text-center">Gunakan formulir di samping untuk menambahkan game pertama Anda.</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
