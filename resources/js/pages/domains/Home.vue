<script setup lang="ts">
import DomainLayout from '@/layouts/DomainLayout.vue';
import { create as offerCreate } from '@/actions/App/Http/Controllers/OfferController';
import {
    ArrowRight,
    Building2,
    CheckCircle2,
    Globe,
    Laptop,
    Rocket,
    Shield,
    Sparkles,
    TrendingUp,
    Users,
    Zap,
} from '@lucide/vue';
import { Head, Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

const iconMap: Record<string, Component> = {
    Globe,
    Shield,
    TrendingUp,
    Zap,
    Users,
    CheckCircle2,
    Sparkles,
    Rocket,
    Building2,
    Laptop,
};

type Feature = {
    icon: string;
    title: string;
    description: string;
    color: string | null;
};

defineProps<{
    domain: {
        hostname: string;
        display_name: string;
        tagline: string;
        description: string;
        features: Feature[];
        selling_points: string[];
    };
    otherDomains: {
        hostname: string;
        display_name: string;
        tagline: string;
        url: string;
    }[];
    highestBid: number;
    minimumBid: number;
}>();

function formatMoney(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
}

function resolveIcon(name: string): Component {
    return iconMap[name] ?? CheckCircle2;
}
</script>

<template>
    <DomainLayout :hostname="domain.hostname">
        <Head :title="`${domain.hostname} for sale`" />

        <section class="relative overflow-hidden py-20 lg:py-32">
            <div
                class="pointer-events-none absolute inset-0 -z-10 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-indigo-100/50 via-slate-50 to-white"
            />

            <div class="container mx-auto px-4">
                <div class="@container mx-auto max-w-4xl text-center">
                    <span
                        class="mb-6 inline-block rounded-full border border-indigo-100 bg-indigo-50 px-4 py-1.5 text-sm font-medium text-indigo-700"
                    >
                        Premium Domain For Sale
                    </span>
                    <h1
                        class="font-display mb-6 max-w-full font-bold tracking-tighter break-words text-slate-900 text-[clamp(2rem,12cqw,6rem)] leading-[1.05]"
                    >
                        {{ domain.hostname }}
                    </h1>
                    <p
                        class="mx-auto mb-10 max-w-2xl text-xl leading-relaxed text-balance text-slate-600 md:text-2xl"
                    >
                        {{ domain.tagline }}
                    </p>

                    <div
                        class="glass-card mx-auto mb-12 max-w-md rounded-2xl border border-gray-100 bg-white p-8 shadow-[0_8px_30px_rgb(0,0,0,0.04)] md:p-10"
                    >
                        <div
                            class="mb-2 text-sm font-medium tracking-wider text-slate-500 uppercase"
                        >
                            Current Highest Offer
                        </div>
                        <div
                            class="font-display mb-2 text-5xl font-bold text-slate-900 tabular-nums"
                        >
                            {{ formatMoney(highestBid) }}
                        </div>
                        <p class="mb-6 text-sm text-slate-500">USD</p>

                        <Link
                            :href="offerCreate()"
                            class="inline-flex h-14 w-full items-center justify-center rounded-xl bg-indigo-700 text-lg font-medium text-white shadow-lg shadow-indigo-500/20 transition hover:bg-indigo-800"
                        >
                            Place Your Bid
                            <ArrowRight class="ml-2 h-5 w-5" />
                        </Link>
                        <p class="mt-4 text-xs text-slate-400">
                            Secure transaction via Escrow.com available
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="border-y border-slate-100 bg-white py-24">
            <div class="container mx-auto px-4">
                <div class="grid gap-12 md:grid-cols-3">
                    <div
                        v-for="(feature, index) in domain.features"
                        :key="index"
                        class="px-4 text-center"
                    >
                        <div
                            class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border border-slate-100 bg-slate-50"
                        >
                            <component
                                :is="resolveIcon(feature.icon)"
                                class="h-6 w-6"
                                :class="feature.color || 'text-indigo-600'"
                            />
                        </div>
                        <h3 class="mb-3 text-xl font-bold">
                            {{ feature.title }}
                        </h3>
                        <p class="leading-relaxed text-slate-600">
                            {{ feature.description }}
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-slate-50 py-20">
            <div class="container mx-auto px-4 text-center">
                <h3 class="font-display mb-10 text-2xl font-bold">
                    Why acquire this domain?
                </h3>
                <div class="mx-auto grid max-w-3xl gap-6 text-left">
                    <div
                        v-for="(point, index) in domain.selling_points"
                        :key="index"
                        class="flex items-center rounded-xl border border-slate-200 bg-white p-4 shadow-sm"
                    >
                        <CheckCircle2
                            class="mr-4 h-6 w-6 shrink-0 text-indigo-700"
                        />
                        <span class="text-lg text-slate-800">{{ point }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section
            v-if="otherDomains.length > 0"
            class="border-t border-slate-100 bg-white py-20"
        >
            <div class="container mx-auto px-4 text-center">
                <h3 class="font-display mb-3 text-2xl font-bold">
                    Also available for sale
                </h3>
                <p class="mx-auto mb-10 max-w-xl text-slate-600">
                    Explore other premium domains currently open for offers.
                </p>
                <div class="mx-auto grid max-w-3xl gap-4 text-left">
                    <a
                        v-for="other in otherDomains"
                        :key="other.hostname"
                        :href="other.url"
                        class="group flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-5 py-4 transition hover:border-indigo-200 hover:bg-white hover:shadow-sm"
                    >
                        <div class="min-w-0">
                            <div
                                class="font-display truncate text-lg font-bold tracking-tight text-slate-900 group-hover:text-indigo-700"
                            >
                                {{ other.hostname }}
                            </div>
                            <p class="mt-1 truncate text-sm text-slate-600">
                                {{ other.tagline }}
                            </p>
                        </div>
                        <ArrowRight
                            class="h-5 w-5 shrink-0 text-slate-400 transition group-hover:translate-x-0.5 group-hover:text-indigo-700"
                        />
                    </a>
                </div>
            </div>
        </section>
    </DomainLayout>
</template>
