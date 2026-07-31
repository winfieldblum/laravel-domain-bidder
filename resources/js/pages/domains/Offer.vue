<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/OfferController';
import DomainLayout from '@/layouts/DomainLayout.vue';
import { home } from '@/routes';
import { ArrowLeft, DollarSign, Loader2 } from '@lucide/vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

defineProps<{
    domain: {
        hostname: string;
        display_name: string;
    };
    highestBid: number;
    minimumBid: number;
}>();

const page = usePage();
const submitted = computed(
    () =>
        Boolean(
            (page.props.flash as { offer_submitted?: boolean } | undefined)
                ?.offer_submitted,
        ),
);

function formatMoney(amount: number): string {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <DomainLayout :hostname="domain.hostname">
        <Head :title="`Make an offer — ${domain.hostname}`" />

        <div
            v-if="submitted"
            class="flex min-h-[80vh] items-center justify-center p-4"
        >
            <div class="w-full max-w-md space-y-6 text-center">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100"
                >
                    <DollarSign class="h-10 w-10 text-green-600" />
                </div>
                <h2 class="font-display text-3xl font-bold text-slate-900">
                    Offer Received!
                </h2>
                <p class="text-lg text-slate-600">
                    Thank you for your offer! Check your email for a
                    verification link from {{ domain.display_name }}. Once
                    confirmed, the domain owner will review your bid.
                </p>
                <div class="pt-8">
                    <Link
                        :href="home()"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 font-medium text-slate-800 hover:bg-slate-50"
                    >
                        Return Home
                    </Link>
                </div>
            </div>
        </div>

        <div v-else class="container mx-auto max-w-lg px-4 py-12">
            <Link
                :href="home()"
                class="mb-8 inline-flex items-center text-slate-500 transition-colors hover:text-slate-900"
            >
                <ArrowLeft class="mr-2 h-4 w-4" />
                Back to home
            </Link>

            <div
                class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl shadow-slate-200/50"
            >
                <div
                    class="space-y-1 border-b border-slate-50 bg-slate-50/50 px-6 py-6"
                >
                    <h1 class="font-display text-2xl font-bold">
                        Make an Offer
                    </h1>
                    <p class="text-base text-slate-600">
                        Current highest bid:
                        <span class="font-semibold text-slate-900">{{
                            formatMoney(highestBid)
                        }}</span>
                    </p>
                </div>

                <Form
                    v-bind="store.form()"
                    class="space-y-6 p-6"
                    v-slot="{ errors, processing }"
                >
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Full Name</label
                        >
                        <input
                            name="name"
                            type="text"
                            required
                            placeholder="John Doe"
                            class="h-11 w-full rounded-lg border border-slate-200 px-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="errors.name" class="text-sm text-red-600">
                            {{ errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Email Address</label
                        >
                        <input
                            name="email"
                            type="email"
                            required
                            placeholder="john@company.com"
                            class="h-11 w-full rounded-lg border border-slate-200 px-3 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                        />
                        <p v-if="errors.email" class="text-sm text-red-600">
                            {{ errors.email }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <label class="text-sm font-medium text-slate-700"
                            >Offer Amount (USD)</label
                        >
                        <div class="relative">
                            <DollarSign
                                class="absolute top-3 left-3 h-5 w-5 text-slate-400"
                            />
                            <input
                                name="amount"
                                type="number"
                                required
                                :min="minimumBid"
                                :value="minimumBid"
                                class="h-11 w-full rounded-lg border border-slate-200 py-2 pr-3 pl-10 text-lg font-medium outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"
                            />
                        </div>
                        <p class="text-xs text-slate-500">
                            Minimum bid: {{ formatMoney(minimumBid) }}
                        </p>
                        <p v-if="errors.amount" class="text-sm text-red-600">
                            {{ errors.amount }}
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="mt-4 inline-flex h-12 w-full items-center justify-center rounded-xl bg-indigo-700 text-lg font-medium text-white hover:bg-indigo-800 disabled:opacity-60"
                        :disabled="processing"
                    >
                        <Loader2
                            v-if="processing"
                            class="mr-2 h-5 w-5 animate-spin"
                        />
                        {{ processing ? 'Submitting...' : 'Submit Offer' }}
                    </button>
                </Form>
            </div>
        </div>
    </DomainLayout>
</template>
