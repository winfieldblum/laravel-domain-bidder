<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    hostname: string;
}>();

const brandLetter = computed(() => props.hostname.charAt(0).toUpperCase());
</script>

<template>
    <div class="flex min-h-screen flex-col bg-slate-50 text-slate-900">
        <header
            class="sticky top-0 z-40 border-b border-slate-200/70 bg-white/80 backdrop-blur-lg"
        >
            <div
                class="container mx-auto flex h-16 items-center justify-between px-4"
            >
                <Link href="/" class="flex items-center gap-2 font-semibold">
                    <span
                        class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-700 text-sm text-white"
                    >
                        {{ brandLetter }}
                    </span>
                    <span class="font-display tracking-tight">{{
                        hostname
                    }}</span>
                </Link>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-slate-200 bg-white py-10">
            <div
                class="container mx-auto px-4 text-center text-sm text-slate-500"
            >
                {{ hostname }} is offered for sale. Secure transaction via
                Escrow.com available.
            </div>
        </footer>
    </div>
</template>
