<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <AuthBase
        title="Daftar Akun Baru"
        description="Lengkapi data diri Anda untuk bergabung menjadi member"
    >
        <Head title="Daftar" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1" for="name">Nama Lengkap</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="Masukkan nama lengkap"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1" for="email">Alamat Email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="nama@email.com"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1" for="password">Kata Sandi</Label>
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="••••••••"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="grid gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1" for="password_confirmation">Konfirmasi Sandi</Label>
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full h-14 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-indigo-500/20 border-none transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    tabindex="5"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    Buat Akun Sekarang
                </Button>
            </div>

            <div class="text-center text-xs font-bold text-slate-500">
                Sudah punya akun?
                <Link
                    :href="login().url"
                    class="text-white hover:text-indigo-400 ml-1 transition-colors"
                    :tabindex="6"
                    >Masuk Disini</Link
                >
            </div>
        </Form>
    </AuthBase>
</template>
