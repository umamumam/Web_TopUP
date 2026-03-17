<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Selamat Datang Kembali"
        description="Silakan masuk ke akun Anda untuk melanjutkan transaksi"
    >
        <Head title="Masuk" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-bold text-emerald-400 bg-emerald-500/10 py-3 rounded-xl border border-emerald-500/20"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400 pl-1" for="email">Alamat Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="nama@email.com"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between pl-1">
                        <Label class="text-[10px] font-black uppercase tracking-widest text-slate-400" for="password">Kata Sandi</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request().url"
                            class="text-[10px] font-black uppercase tracking-widest text-indigo-400 hover:text-indigo-300"
                            :tabindex="5"
                        >
                            Lupa sandi?
                        </TextLink>
                    </div>
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="bg-white/5 border-white/10 text-white placeholder:text-slate-600 focus:bg-white/10 focus:border-indigo-500 h-12 rounded-xl transition-all font-bold"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between px-1">
                    <Label for="remember" class="flex items-center space-x-3 cursor-pointer group">
                        <Checkbox id="remember" name="remember" :tabindex="3" class="border-white/20 data-[state=checked]:bg-indigo-600" />
                        <span class="text-xs font-bold text-slate-400 group-hover:text-slate-300 transition-colors">Ingat saya</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full h-14 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 text-sm font-black uppercase tracking-widest rounded-2xl shadow-xl shadow-indigo-500/20 border-none transition-all duration-300 hover:scale-[1.02] active:scale-[0.98]"
                    :tabindex="4"
                    :disabled="processing"
                >
                    <Spinner v-if="processing" class="mr-2" />
                    Masuk Sekarang
                </Button>
            </div>

            <div
                class="text-center text-xs font-bold text-slate-500"
                v-if="canRegister"
            >
                Belum punya akun?
                <TextLink :href="register().url" :tabindex="5" class="text-white hover:text-indigo-400 ml-1">Daftar Gratis</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>
