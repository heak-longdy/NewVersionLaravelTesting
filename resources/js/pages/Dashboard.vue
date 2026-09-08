<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import {
    ArrowUpLeft,
    ArrowUpRight,
    BarChart2,
    Building,
    ChevronDown,
    Circle,
    CreditCard,
    DollarSign,
    Gamepad2,
    Headphones,
    Layers,
    RotateCcw,
    ShoppingBag,
    ShoppingCart,
    TrendingUp,
    Wallet,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import { Switch } from '@/components/ui/switch';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isMonthlyEarningsActive = ref(true);
const selectedMonth = ref('March 2025');
const monthOptions = ['January 2025', 'February 2025', 'March 2025', 'April 2025', 'May 2025'];
const showMonthDropdown = ref(false);

const selectMonth = (m: string) => {
    selectedMonth.value = m;
    showMonthDropdown.value = false;
};

// Data for Recent Transactions timeline
const transactions = [
    { time: '09:30 am', text: 'Payment received from John Doe of $385.90', color: 'border-blue-500 text-blue-400' },
    { time: '10:00 am', text: 'New sale recorded #ML-3467', color: 'border-cyan-400 text-cyan-300' },
    { time: '12:00 am', text: 'Payment was made of $64.95 to Michael', color: 'border-emerald-500 text-emerald-400' },
    { time: '09:30 am', text: 'New sale recorded #ML-3467', color: 'border-blue-400 text-blue-300' },
    { time: '09:30 am', text: 'New arrival recorded #ML-3467', color: 'border-amber-400 text-amber-300' },
    { time: '12:00 am', text: 'Payment Done', color: 'border-emerald-400 text-emerald-300' },
];

// Data for Product Performance table
const products = [
    {
        name: 'Gaming Console',
        category: 'Electronics',
        icon: Gamepad2,
        iconBg: 'bg-amber-500/20 text-amber-400 border-amber-500/30',
        progress: '78.5%',
        priority: 'Low',
        priorityClass: 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
        budget: '$3.9k',
    },
    {
        name: 'Leather Purse',
        category: 'Fashion',
        icon: ShoppingBag,
        iconBg: 'bg-pink-500/20 text-pink-400 border-pink-500/30',
        progress: '58.6%',
        priority: 'Medium',
        priorityClass: 'bg-amber-500/15 text-amber-400 border-amber-500/30',
        budget: '$3.5k',
    },
    {
        name: 'Red Velvate Dress',
        category: 'Womens Fashion',
        icon: Building,
        iconBg: 'bg-rose-500/20 text-rose-400 border-rose-500/30',
        progress: '25%',
        priority: 'Very High',
        priorityClass: 'bg-blue-500/15 text-blue-400 border-blue-500/30',
        budget: '$3.5k',
    },
    {
        name: 'Headphone Boat',
        category: 'Electronics',
        icon: Headphones,
        iconBg: 'bg-orange-500/20 text-orange-400 border-orange-500/30',
        progress: '96.3%',
        priority: 'High',
        priorityClass: 'bg-rose-500/15 text-rose-400 border-rose-500/30',
        budget: '$3.5k',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-background text-foreground p-4 sm:p-6 lg:p-8 space-y-6 transition-colors duration-200">
        <!-- ROW 1: Top Hero Banner & Two Mini Stat Cards -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Hero Welcome Card (8 Cols) -->
            <div class="lg:col-span-8 relative overflow-hidden rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div class="relative z-10 space-y-6 max-w-xl">
                    <!-- User Header -->
                    <div class="flex items-center gap-3">
                        <img
                            src="/images/user-avatar.jpg"
                            alt="Avatar"
                            class="size-11 rounded-full ring-2 ring-cyan-500/80 object-cover shadow-xs"
                        />
                        <h2 class="text-lg font-semibold text-foreground tracking-tight">
                            Welcome back {{ user?.name || 'Long dy' }}!
                        </h2>
                    </div>

                    <!-- Hero Stats -->
                    <div class="flex flex-wrap items-center gap-8 pt-2">
                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 text-2xl sm:text-3xl font-bold text-foreground">
                                <span>$2,340</span>
                                <ArrowUpRight class="size-5 text-cyan-500 stroke-[2.5]" />
                            </div>
                            <p class="text-xs font-medium text-muted-foreground">Today's Sales</p>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center gap-1.5 text-2xl sm:text-3xl font-bold text-foreground">
                                <span>35%</span>
                                <ArrowUpRight class="size-5 text-cyan-500 stroke-[2.5]" />
                            </div>
                            <p class="text-xs font-medium text-muted-foreground">Performance</p>
                        </div>
                    </div>
                </div>

                <!-- 3D Character Illustration Background Element -->
                <div class="hidden sm:block absolute right-0 bottom-0 top-0 w-80 pointer-events-none overflow-hidden">
                    <img
                        src="/images/dashboard-hero.jpg"
                        alt="Analytics Assistant"
                        class="w-full h-full object-cover object-left opacity-80 mix-blend-luminosity dark:mix-blend-screen transition-opacity"
                    />
                    <div class="absolute inset-0 bg-gradient-to-r from-card via-card/40 to-transparent"></div>
                </div>
            </div>

            <!-- Top Right Grid (4 Cols: Expense & Sales Cards) -->
            <div class="lg:col-span-4 grid grid-cols-2 gap-4">
                <!-- Expense Card -->
                <div class="rounded-2xl bg-card border border-border/80 p-5 shadow-xs flex flex-col justify-between transition-all">
                    <div>
                        <h3 class="text-xl font-bold text-foreground">$10,230</h3>
                        <p class="text-xs font-medium text-muted-foreground mt-0.5">Expense</p>
                    </div>
                    <!-- Donut Chart SVG -->
                    <div class="flex items-center justify-center py-2">
                        <svg class="size-20 -rotate-90" viewBox="0 0 36 36">
                            <!-- Background ring -->
                            <circle cx="18" cy="18" r="14" fill="none" class="stroke-muted" stroke-width="4" />
                            <!-- Expense Cyan segment -->
                            <circle
                                cx="18" cy="18" r="14"
                                fill="none"
                                stroke="#38bdf8"
                                stroke-width="4.5"
                                stroke-dasharray="60, 100"
                                stroke-linecap="round"
                            />
                            <!-- Blue segment -->
                            <circle
                                cx="18" cy="18" r="14"
                                fill="none"
                                stroke="#3b82f6"
                                stroke-width="4.5"
                                stroke-dasharray="20, 100"
                                stroke-dashoffset="-65"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>
                </div>

                <!-- Sales Card -->
                <div class="rounded-2xl bg-card border border-border/80 p-5 shadow-xs flex flex-col justify-between transition-all">
                    <div>
                        <h3 class="text-xl font-bold text-foreground">$65,432</h3>
                        <p class="text-xs font-medium text-muted-foreground mt-0.5">Sales</p>
                    </div>
                    <!-- Mini Vertical Bar Chart -->
                    <div class="flex items-end justify-center gap-1.5 h-16 pt-2">
                        <div class="w-1.5 h-6 bg-cyan-400/50 rounded-full"></div>
                        <div class="w-1.5 h-10 bg-cyan-400 rounded-full"></div>
                        <div class="w-1.5 h-7 bg-cyan-400/70 rounded-full"></div>
                        <div class="w-1.5 h-12 bg-cyan-300 rounded-full"></div>
                        <div class="w-1.5 h-9 bg-cyan-400/80 rounded-full"></div>
                        <div class="w-1.5 h-11 bg-cyan-400 rounded-full"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 2: Revenue Updates, Sales Overview, & Monthly Earnings Column -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1: Revenue Updates -->
            <div class="rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-base font-semibold text-foreground">Revenue Updates</h3>
                            <p class="text-xs text-muted-foreground">Overview of Profit</p>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="flex items-center gap-4 mt-4 text-xs font-medium text-muted-foreground">
                        <div class="flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-cyan-400"></span>
                            <span class="text-foreground">Footware</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="size-2 rounded-full bg-blue-600"></span>
                            <span class="text-foreground">Fashionware</span>
                        </div>
                    </div>
                </div>

                <!-- Bi-directional Bar Chart -->
                <div class="py-6">
                    <div class="grid grid-cols-5 gap-3 h-44 items-center relative">
                        <!-- Horizontal zero reference line -->
                        <div class="absolute inset-x-0 top-1/2 border-t border-border"></div>

                        <!-- Jan -->
                        <div class="flex flex-col items-center justify-center h-full gap-1 z-10">
                            <div class="h-10 w-2.5 bg-blue-500 rounded-t-sm self-center"></div>
                            <div class="h-12 w-2.5 bg-cyan-400 rounded-b-sm self-center"></div>
                            <span class="text-[11px] text-muted-foreground mt-2">Jan</span>
                        </div>

                        <!-- Feb -->
                        <div class="flex flex-col items-center justify-center h-full gap-1 z-10">
                            <div class="h-16 w-2.5 bg-blue-500 rounded-t-sm self-center"></div>
                            <div class="h-6 w-2.5 bg-cyan-400 rounded-b-sm self-center"></div>
                            <span class="text-[11px] text-muted-foreground mt-2">Feb</span>
                        </div>

                        <!-- Mar -->
                        <div class="flex flex-col items-center justify-center h-full gap-1 z-10">
                            <div class="h-12 w-2.5 bg-blue-500 rounded-t-sm self-center"></div>
                            <div class="h-14 w-2.5 bg-cyan-400 rounded-b-sm self-center"></div>
                            <span class="text-[11px] text-muted-foreground mt-2">Mar</span>
                        </div>

                        <!-- Apr -->
                        <div class="flex flex-col items-center justify-center h-full gap-1 z-10">
                            <div class="h-10 w-2.5 bg-blue-500 rounded-t-sm self-center"></div>
                            <div class="h-8 w-2.5 bg-cyan-400 rounded-b-sm self-center"></div>
                            <span class="text-[11px] text-muted-foreground mt-2">Apr</span>
                        </div>

                        <!-- May -->
                        <div class="flex flex-col items-center justify-center h-full gap-1 z-10">
                            <div class="h-8 w-2.5 bg-blue-500 rounded-t-sm self-center"></div>
                            <div class="h-10 w-2.5 bg-cyan-400 rounded-b-sm self-center"></div>
                            <span class="text-[11px] text-muted-foreground mt-2">May</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Sales Overview -->
            <div class="rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <h3 class="text-base font-semibold text-foreground">Sales Overview</h3>
                    <p class="text-xs text-muted-foreground">Every month</p>
                </div>

                <!-- Big Circular Gauge with Center Total -->
                <div class="flex items-center justify-center py-4 relative">
                    <svg class="size-44 -rotate-90" viewBox="0 0 100 100">
                        <circle cx="50" cy="50" r="40" fill="none" class="stroke-muted" stroke-width="8" />
                        <circle
                            cx="50" cy="50" r="40"
                            fill="none"
                            stroke="url(#salesGradient)"
                            stroke-width="8.5"
                            stroke-dasharray="210, 252"
                            stroke-linecap="round"
                        />
                        <defs>
                            <linearGradient id="salesGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#38bdf8" />
                                <stop offset="100%" stop-color="#2563eb" />
                            </linearGradient>
                        </defs>
                    </svg>
                    <!-- Centered Amount -->
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                        <span class="text-2xl sm:text-3xl font-bold text-foreground tracking-tight">$500,458</span>
                    </div>
                </div>

                <!-- Bottom Profit & Expense tiles -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-border">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                            <Layers class="size-4" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">$23,450</p>
                            <p class="text-[11px] text-muted-foreground">Profit</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-cyan-500/10 text-cyan-500">
                            <Layers class="size-4" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground">$23,450</p>
                            <p class="text-[11px] text-muted-foreground">Expense</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: 2 Mini Stats + Monthly Earnings -->
            <div class="flex flex-col gap-6">
                <!-- Top 2 Mini Stat Cards -->
                <div class="grid grid-cols-2 gap-4">
                    <!-- Mini 1: Sales -->
                    <div class="rounded-2xl bg-card border border-border/80 p-4 shadow-xs space-y-3 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                                <ShoppingCart class="size-4" />
                            </div>
                            <div class="flex items-center gap-1">
                                <span class="size-1.5 rounded-full bg-blue-500"></span>
                                <span class="size-1.5 rounded-full bg-blue-500/40"></span>
                                <span class="size-1.5 rounded-full bg-blue-500/40"></span>
                            </div>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-lg font-bold text-foreground">
                                <span>$16.5k</span>
                                <ArrowUpRight class="size-4 text-cyan-500 stroke-[2.5]" />
                            </div>
                            <p class="text-xs text-muted-foreground">Sales</p>
                        </div>
                    </div>

                    <!-- Mini 2: Growth -->
                    <div class="rounded-2xl bg-card border border-border/80 p-4 shadow-xs space-y-3 transition-all">
                        <div class="flex items-center justify-between">
                            <div class="p-2 rounded-lg bg-cyan-500/10 text-cyan-500">
                                <BarChart2 class="size-4" />
                            </div>
                            <!-- Mini line sparkline -->
                            <svg class="w-10 h-4" viewBox="0 0 40 16">
                                <path d="M0 12 Q 10 2, 20 8 T 40 4" fill="none" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" />
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-1 text-lg font-bold text-foreground">
                                <span>24%</span>
                                <ArrowUpRight class="size-4 text-cyan-500 stroke-[2.5]" />
                            </div>
                            <p class="text-xs text-muted-foreground">Growth</p>
                        </div>
                    </div>
                </div>

                <!-- Bottom: Monthly Earnings Card with Wave -->
                <div class="rounded-2xl bg-card border border-border/80 p-5 shadow-xs flex-1 flex flex-col justify-between overflow-hidden transition-all">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-foreground">Monthly Earnings</h3>
                        <Switch
                            id="monthly-toggle"
                            :checked="isMonthlyEarningsActive"
                            @update:checked="isMonthlyEarningsActive = $event"
                        />
                    </div>

                    <div class="pt-4">
                        <div class="flex items-baseline gap-2">
                            <span class="text-2xl font-bold text-foreground">$6,820</span>
                            <span class="flex items-center text-xs font-semibold text-emerald-500">
                                <ArrowUpLeft class="size-3 stroke-[2.5]" />
                                +9%
                            </span>
                        </div>
                    </div>

                    <!-- Full-width Sine Wave SVG -->
                    <div class="w-full pt-4">
                        <svg class="w-full h-14" viewBox="0 0 300 60" preserveAspectRatio="none">
                            <defs>
                                <linearGradient id="waveGradient" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#38bdf8" stop-opacity="0.35" />
                                    <stop offset="100%" stop-color="#38bdf8" stop-opacity="0.0" />
                                </linearGradient>
                            </defs>
                            <path
                                d="M0 30 Q 37.5 10, 75 30 T 150 30 T 225 30 T 300 30 L 300 60 L 0 60 Z"
                                fill="url(#waveGradient)"
                            />
                            <path
                                d="M0 30 Q 37.5 10, 75 30 T 150 30 T 225 30 T 300 30"
                                fill="none"
                                stroke="#38bdf8"
                                stroke-width="2.5"
                                stroke-linecap="round"
                            />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- ROW 3: Weekly Stats, Yearly Sales, & Payment Gateways -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Card 1: Weekly Stats -->
            <div class="rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <h3 class="text-base font-semibold text-foreground">Weekly Stats</h3>
                    <p class="text-xs text-muted-foreground">Average sales</p>
                </div>

                <!-- Smooth Wave Curve (Two Arcs) -->
                <div class="py-4">
                    <svg class="w-full h-16" viewBox="0 0 200 60" fill="none">
                        <!-- Left Arc -->
                        <path
                            d="M 10 50 Q 55 10, 100 50"
                            stroke="#3b82f6"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                        <!-- Right Arc -->
                        <path
                            d="M 130 50 Q 165 20, 195 35"
                            stroke="#3b82f6"
                            stroke-width="2.5"
                            stroke-linecap="round"
                        />
                    </svg>
                </div>

                <!-- List Items with Badges -->
                <div class="space-y-3 pt-2">
                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                                <Layers class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Top Sales</p>
                                <p class="text-xs text-muted-foreground">Johnathan Doe</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-blue-500/15 text-blue-500 text-xs font-semibold">+68</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-emerald-500/10 text-emerald-500">
                                <Layers class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Best Seller</p>
                                <p class="text-xs text-muted-foreground">Footware</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-emerald-500/15 text-emerald-500 text-xs font-semibold">+45</span>
                    </div>

                    <div class="flex items-center justify-between p-2 rounded-xl hover:bg-muted/50 transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="p-2 rounded-lg bg-rose-500/10 text-rose-500">
                                <Layers class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Most Commented</p>
                                <p class="text-xs text-muted-foreground">Fashionware</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-rose-500/15 text-rose-500 text-xs font-semibold">+14</span>
                    </div>
                </div>
            </div>

            <!-- Card 2: Yearly Sales -->
            <div class="rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <h3 class="text-base font-semibold text-foreground">Yearly Sales</h3>
                    <p class="text-xs text-muted-foreground">Total Sales</p>
                </div>

                <!-- Bar Chart (Apr to Sept with June highlighted) -->
                <div class="py-6 flex items-end justify-between h-48 px-2 gap-2">
                    <!-- Apr -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-muted rounded-t-md h-12"></div>
                        <span class="text-[10px] text-muted-foreground">Apr</span>
                    </div>
                    <!-- May -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-muted rounded-t-md h-20"></div>
                        <span class="text-[10px] text-muted-foreground">May</span>
                    </div>
                    <!-- June (Highlight) -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-blue-600 rounded-t-md h-36 shadow-md shadow-blue-500/25"></div>
                        <span class="text-[10px] text-blue-600 dark:text-blue-400 font-semibold">June</span>
                    </div>
                    <!-- July -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-muted rounded-t-md h-16"></div>
                        <span class="text-[10px] text-muted-foreground">July</span>
                    </div>
                    <!-- Aug -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-muted rounded-t-md h-22"></div>
                        <span class="text-[10px] text-muted-foreground">Aug</span>
                    </div>
                    <!-- Sept -->
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <div class="w-4 bg-muted rounded-t-md h-14"></div>
                        <span class="text-[10px] text-muted-foreground">Sept</span>
                    </div>
                </div>

                <!-- Bottom Summary -->
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-border">
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-blue-500/10 text-blue-500">
                            <Layers class="size-4" />
                        </div>
                        <div>
                            <p class="text-[11px] text-muted-foreground">Salary</p>
                            <p class="text-sm font-bold text-foreground">$36,358</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="p-2 rounded-lg bg-cyan-500/10 text-cyan-500">
                            <Layers class="size-4" />
                        </div>
                        <div>
                            <p class="text-[11px] text-muted-foreground">Expance</p>
                            <p class="text-sm font-bold text-foreground">$5,296</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Payment Gateways -->
            <div class="rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <h3 class="text-base font-semibold text-foreground">Payment Gateways</h3>
                    <p class="text-xs text-muted-foreground">Platform For Income</p>
                </div>

                <div class="space-y-4 my-auto py-2">
                    <!-- Paypal -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-9 rounded-xl bg-blue-600/15 text-blue-600 flex items-center justify-center font-bold text-sm">
                                P
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Paypal</p>
                                <p class="text-xs text-muted-foreground">Big Brands</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-emerald-500">+$6235</span>
                    </div>

                    <!-- Wallet -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-9 rounded-xl bg-emerald-600/15 text-emerald-500 flex items-center justify-center">
                                <Wallet class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Wallet</p>
                                <p class="text-xs text-muted-foreground">Bill payment</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-rose-500">-$345</span>
                    </div>

                    <!-- Credit Card -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-9 rounded-xl bg-amber-500/15 text-amber-500 flex items-center justify-center">
                                <CreditCard class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Credit Card</p>
                                <p class="text-xs text-muted-foreground">Money reversed</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-emerald-500">+$2235</span>
                    </div>

                    <!-- Refund -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="size-9 rounded-xl bg-rose-500/15 text-rose-500 flex items-center justify-center">
                                <RotateCcw class="size-4" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-foreground">Refund</p>
                                <p class="text-xs text-muted-foreground">Bill Payment</p>
                            </div>
                        </div>
                        <span class="text-sm font-semibold text-rose-500">-$32</span>
                    </div>
                </div>

                <!-- View All Transactions Button -->
                <button
                    type="button"
                    class="w-full py-2.5 px-4 rounded-xl border border-border bg-muted/40 hover:bg-muted text-xs font-semibold text-foreground transition-all shadow-xs cursor-pointer"
                >
                    View all transactions
                </button>
            </div>
        </div>

        <!-- ROW 4: Recent Transactions & Product Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Recent Transactions (4 Cols) -->
            <div class="lg:col-span-4 rounded-2xl bg-card border border-border/80 p-6 shadow-xs flex flex-col justify-between transition-all">
                <div>
                    <h3 class="text-base font-semibold text-foreground">Recent Transactions</h3>
                </div>

                <!-- Timeline List -->
                <div class="relative pl-6 space-y-6 pt-4 before:absolute before:left-2 before:top-6 before:bottom-3 before:w-0.5 before:bg-border">
                    <div
                        v-for="(t, i) in transactions"
                        :key="i"
                        class="relative flex items-start gap-4 text-xs"
                    >
                        <!-- Timeline Node Dot -->
                        <span
                            class="absolute -left-6 top-0.5 size-3.5 rounded-full border-2 bg-card z-10"
                            :class="t.color"
                        ></span>
                        <span class="font-semibold text-muted-foreground whitespace-nowrap">{{ t.time }}</span>
                        <span class="text-foreground leading-relaxed">{{ t.text }}</span>
                    </div>
                </div>
            </div>

            <!-- Product Performance Table (8 Cols) -->
            <div class="lg:col-span-8 rounded-2xl bg-card border border-border/80 p-6 shadow-xs space-y-6 transition-all">
                <!-- Header with Month Dropdown -->
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-foreground">Product Performance</h3>
                    </div>

                    <!-- Month Select Dropdown -->
                    <div class="relative">
                        <button
                            type="button"
                            @click="showMonthDropdown = !showMonthDropdown"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-border bg-muted/50 text-xs font-medium text-foreground hover:bg-muted cursor-pointer transition-colors"
                        >
                            <span>{{ selectedMonth }}</span>
                            <ChevronDown class="size-3.5 text-muted-foreground" />
                        </button>
                        <div
                            v-if="showMonthDropdown"
                            class="absolute right-0 mt-1.5 w-36 rounded-xl border border-border bg-popover text-popover-foreground shadow-2xl z-30 py-1"
                        >
                            <button
                                v-for="m in monthOptions"
                                :key="m"
                                @click="selectMonth(m)"
                                class="w-full text-left px-3 py-1.5 text-xs text-foreground hover:bg-muted cursor-pointer"
                            >
                                {{ m }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-border text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                                <th class="pb-3 pr-4">Product</th>
                                <th class="pb-3 px-4">Progress</th>
                                <th class="pb-3 px-4">Priority</th>
                                <th class="pb-3 px-4">Budget</th>
                                <th class="pb-3 pl-4 text-right">Chart</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr
                                v-for="p in products"
                                :key="p.name"
                                class="hover:bg-muted/40 transition-colors"
                            >
                                <!-- Product & Category -->
                                <td class="py-3.5 pr-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="size-9 rounded-xl border flex items-center justify-center shrink-0"
                                            :class="p.iconBg"
                                        >
                                            <component :is="p.icon" class="size-4" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-foreground">{{ p.name }}</p>
                                            <p class="text-xs text-muted-foreground">{{ p.category }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Progress -->
                                <td class="py-3.5 px-4 text-xs font-semibold text-foreground">
                                    {{ p.progress }}
                                </td>

                                <!-- Priority Badge -->
                                <td class="py-3.5 px-4">
                                    <span
                                        class="px-2.5 py-1 rounded-md text-[11px] font-semibold border"
                                        :class="p.priorityClass"
                                    >
                                        {{ p.priority }}
                                    </span>
                                </td>

                                <!-- Budget -->
                                <td class="py-3.5 px-4 text-xs font-semibold text-foreground">
                                    {{ p.budget }}
                                </td>

                                <!-- Sparkline Wave Chart -->
                                <td class="py-3.5 pl-4 text-right">
                                    <svg class="w-20 h-6 inline-block" viewBox="0 0 80 24">
                                        <path
                                            d="M 0 12 Q 20 2, 40 12 T 80 12"
                                            fill="none"
                                            stroke="#38bdf8"
                                            stroke-width="2"
                                            stroke-linecap="round"
                                        />
                                    </svg>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>
