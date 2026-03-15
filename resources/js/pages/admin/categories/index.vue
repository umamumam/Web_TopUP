<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { Layers, Plus, Pencil, Trash2 } from 'lucide-vue-next';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

defineProps<{
    categories: any[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Kategori', href: '/admin/categories' },
];

const form = useForm({
    id: null as number | null,
    name: '',
});

const isEditing = computed(() => form.id !== null);

const editCategory = (category: any) => {
    form.id = category.id;
    form.name = category.name;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const cancelEdit = () => {
    form.reset();
    form.id = null;
};

const submit = () => {
    if (isEditing.value) {
        form.put(`/admin/categories/${form.id}`, {
            onSuccess: () => {
                cancelEdit();
                alert('Kategori diperbarui!');
            }
        });
    } else {
        form.post('/admin/categories', {
            onSuccess: () => {
                form.reset();
                alert('Kategori ditambahkan!');
            }
        });
    }
};

const deleteCategory = (id: number) => {
    if (confirm('Hapus kategori ini? Semua game di dalamnya mungkin akan kehilangan referensi kategori.')) {
        form.delete(`/admin/categories/${id}`);
    }
};
</script>

<template>
    <Head title="Kelola Kategori" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="p-4 bg-purple-500 rounded-3xl shadow-lg shadow-purple-500/20 text-white">
                        <Layers class="w-8 h-8" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-black uppercase tracking-tight">Kategori Game</h1>
                        <p class="text-slate-500 font-medium">Pisahkan game berdasarkan platform atau jenis.</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Form Area -->
                <div class="bg-white dark:bg-slate-900 p-8 rounded-[32px] border-2 border-slate-200 dark:border-slate-800 shadow-xl self-start transition-all" :class="isEditing ? 'border-purple-500/50' : ''">
                    <h2 class="text-xl font-bold mb-6 flex items-center justify-between">
                        <span class="flex items-center gap-2">
                            <Plus v-if="!isEditing" class="w-5 h-5 text-purple-500" />
                            <Pencil v-else class="w-5 h-5 text-purple-500" />
                            {{ isEditing ? 'Edit Kategori' : 'Tambah Kategori' }}
                        </span>
                        <button v-if="isEditing" @click="cancelEdit" class="text-xs text-slate-400 hover:text-red-500 font-bold uppercase tracking-widest transition-colors">Batal</button>
                    </h2>
                    <form @submit.prevent="submit" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-[10px] font-black uppercase text-slate-400 tracking-widest pl-1">Nama Kategori</label>
                            <input 
                                v-model="form.name"
                                type="text" 
                                placeholder="Contoh: Mobile Games"
                                class="w-full bg-slate-50 dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 rounded-2xl px-5 py-3.5 focus:border-purple-500 focus:ring-4 focus:ring-purple-500/5 outline-none transition-all font-bold"
                            />
                        </div>
                        <button 
                            type="submit"
                            :disabled="form.processing"
                            class="w-full bg-purple-600 hover:bg-purple-500 disabled:bg-slate-300 text-white font-black py-4 rounded-2xl transition-all shadow-lg shadow-purple-500/20 active:scale-95 uppercase tracking-widest text-sm"
                        >
                            {{ isEditing ? 'Simpan Perubahan' : 'Tambah Kategori' }}
                        </button>
                    </form>
                </div>

                <!-- List Table -->
                <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-[32px] border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50 dark:bg-slate-800/50 text-slate-500 text-[10px] uppercase font-black tracking-widest">
                            <tr>
                                <th class="px-8 py-5">Kategori</th>
                                <th class="px-8 py-5">Slug</th>
                                <th class="px-8 py-5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            <tr v-for="category in categories" :key="category.id" class="group hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2 h-2 rounded-full bg-purple-500 shadow-[0_0_10px_rgba(168,85,247,0.5)]"></div>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ category.name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-sm font-medium text-slate-500">{{ category.slug }}</td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex gap-2 justify-end">
                                        <button @click="editCategory(category)" class="p-2.5 text-blue-500 bg-blue-50 dark:bg-blue-500/10 hover:bg-blue-500 hover:text-white rounded-xl transition-all active:scale-90">
                                            <Pencil class="w-4 h-4" />
                                        </button>
                                        <button @click="deleteCategory(category.id)" class="p-2.5 text-red-500 bg-red-50 dark:bg-red-500/10 hover:bg-red-500 hover:text-white rounded-xl transition-all active:scale-90">
                                            <Trash2 class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    
                    <div v-if="categories.length === 0" class="p-12 text-center">
                        <p class="text-slate-400 font-bold uppercase tracking-widest text-xs">Belum ada kategori</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
