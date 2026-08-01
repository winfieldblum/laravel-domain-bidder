<script setup lang="ts">
import { ArrowUpRight } from '@lucide/vue';

const href = 'https://medicationtimer.com/?utm_source=domain_bidder&utm_medium=banner&utm_campaign=domain_promo';
</script>

<template>
    <section
        class="relative overflow-hidden border-y border-black bg-[#070a0a] text-white"
        aria-label="Medication Timer promotion"
    >
        <div
            class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_85%_40%,rgba(32,178,170,0.22),transparent_55%)]"
        />
        <div
            class="pointer-events-none absolute -right-16 -bottom-24 h-64 w-64 rounded-full bg-[#20B2AA]/10 blur-3xl"
        />

        <div class="relative container mx-auto px-4 py-14 md:py-16">
            <a
                :href="href"
                target="_blank"
                rel="noopener noreferrer"
                class="group grid items-center gap-8 md:grid-cols-[auto_1fr_auto] md:gap-10"
            >
                <div class="flex items-center gap-4">
                    <img
                        src="/images/medication-timer/logo-icon-teal.png"
                        alt=""
                        width="64"
                        height="64"
                        class="h-14 w-14 rounded-2xl shadow-[0_0_0_1px_rgba(32,178,170,0.35)] transition duration-500 group-hover:scale-[1.03] group-hover:shadow-[0_0_24px_rgba(32,178,170,0.35)] md:h-16 md:w-16"
                    />
                    <img
                        src="/images/medication-timer/logo-wordmark-dark.png"
                        alt="Medication Timer"
                        width="250"
                        height="34"
                        class="hidden h-7 w-auto sm:block md:h-8"
                    />
                </div>

                <div class="min-w-0 text-left">
                    <h2
                        class="font-display text-2xl font-bold tracking-tight text-balance text-white md:text-3xl"
                    >
                        Every dose, every family member.
                    </h2>
                    <p
                        class="mt-2 max-w-xl text-sm leading-relaxed text-white/65 md:text-base"
                    >
                        Safety timers, dose routines, and fever tracking in one
                        calm place for the whole household — free to start, no
                        ads, never sells health data.
                    </p>
                </div>

                <span
                    class="inline-flex h-12 items-center justify-center gap-2 rounded-full bg-[#20B2AA] px-6 text-sm font-semibold text-[#042f2e] shadow-[0_10px_30px_rgba(32,178,170,0.25)] transition duration-300 group-hover:translate-x-0.5 group-hover:bg-[#2bc4bb]"
                >
                    Try Medication Timer Today
                    <ArrowUpRight
                        class="h-4 w-4 transition duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"
                    />
                </span>
            </a>
        </div>
    </section>
</template>
