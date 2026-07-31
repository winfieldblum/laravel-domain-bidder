<script setup lang="ts">
import { store } from '@/actions/App/Http/Controllers/VerifiedOfferController';
import DomainLayout from '@/layouts/DomainLayout.vue';
import { home } from '@/routes';
import { ArrowLeft, CheckCircle2, DollarSign, Loader2, XCircle } from '@lucide/vue';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    valid: boolean;
    domain: {
        hostname: string;
        display_name: string;
    };
    bidder: {
        name: string;
        email: string;
    } | null;
    highestBid: number;
    minimumBid: number;
    token: string;
    expiresAt?: string | null;
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
        <Head :title="`Verified offer — ${domain.hostname}`" />

        <div
            v-if="submitted"
            class="flex min-h-[80vh] items-center justify-center p-4"
        >
            <div class="w-full max-w-md space-y-6 text-center">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-green-100"
                >
                    <CheckCircle2 class="h-10 w-10 text-green-600" />
                </div>
                <h2 class="font-display text-3xl font-bold text-slate-900">
                    Verified Offer Submitted!
                </h2>
                <p class="text-lg text-slate-600">
                    Your bid has been submitted and is already verified. The
                    domain owner will review it shortly.
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

        <div
            v-else-if="!valid"
            class="flex min-h-[80vh] items-center justify-center p-4"
        >
            <div class="w-full max-w-md space-y-6 text-center">
                <div
                    class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-100"
                >
                    <XCircle class="h-10 w-10 text-red-600" />
                </div>
                <h2 class="font-display text-3xl font-bold text-slate-900">
                    Link Invalid or Expired
                </h2>
                <p class="text-lg text-slate-600">
                    This verified rebid link is no longer valid. You can still
                    place a new offer and verify by email.
                </p>
                <div class="pt-8">
                    <Link
                        :href="home()"
                        class="inline-flex h-11 w-full items-center justify-center rounded-xl bg-indigo-700 px-4 font-medium text-white hover:bg-indigo-800"
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
                        Place a Verified Offer
                    </h1>
                    <p class="text-base text-slate-600">
                        Current highest bid:
                        <span class="font-semibold text-slate-900">{{
                            formatMoney(highestBid)
                        }}</span>
                    </p>
                    <p class="text-sm text-slate-500">
                        Your email is already verified for this bid.
                    </p>
                </div>

                <Form
                    v-bind="store.form(token)"
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
                            :value="bidder?.name"
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
                            type="email"
                            :value="bidder?.email"
                            disabled
                            class="h-11 w-full rounded-lg border border-slate-200 bg-slate-50 px-3 text-slate-500"
                        />
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
                        <p v-if="errors.token" class="text-sm text-red-600">
                            {{ errors.token }}
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
                        {{
                            processing
                                ? 'Submitting...'
                                : 'Submit Verified Offer'
                        }}
                    </button>
                </Form>
            </div>
        </div>
    </DomainLayout>
</template>
