<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { 
    LayoutGrid, 
    Gamepad2, 
    Package, 
    ShoppingCart, 
    Settings, 
    Layers,
    CreditCard,
    BookOpen, 
    FolderGit2 
} from 'lucide-vue-next';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import admin from '@/routes/admin';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
        icon: LayoutGrid,
    },
    {
        title: 'Pesanan',
        href: admin.orders.index().url,
        icon: ShoppingCart,
    },
    {
        title: 'Kelola Game',
        href: admin.games.index().url,
        icon: Gamepad2,
    },
    {
        title: 'Kelola Produk',
        href: admin.products.index().url,
        icon: Package,
    },
    {
        title: 'Kategori',
        href: admin.categories.index().url,
        icon: Layers,
    },
    {
        title: 'Manajemen Payment',
        href: admin.payments.index().url,
        icon: CreditCard,
    },
    {
        title: 'Landing Page',
        href: admin.landingPage.index().url,
        icon: LayoutGrid,
    },
    {
        title: 'Pengaturan',
        href: admin.settings.index().url,
        icon: Settings,
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard().url">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
