<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    forecastData: Array,
    hiringContext: Object,
});
</script>

<template>

    <Head title="AI Demand Forecast" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-[#1C0D82]">AI Inventory Demand Forecast</h2>
                    <p class="text-gray-500 text-sm font-medium">Predicting stock requirements based on historical usage
                        and HR hiring data.</p>
                </div>
                <div class="flex gap-4">
                    <div class="bg-indigo-50 px-4 py-2 rounded-lg border border-indigo-100 flex items-center gap-3">
                        <div class="w-8 h-8 bg-[#1C0D82] text-white rounded flex items-center justify-center">
                            <i class="pi pi-user-plus text-xs"></i>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase font-black leading-none">Hiring Plan</p>
                            <p class="text-lg font-black text-[#1C0D82] leading-none">{{ hiringContext.openPositions }}
                                Open Positions</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Forecast Table -->
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Item
                                    Details</th>
                                <th class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider">Current
                                    Stock</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider text-center">
                                    Daily Usage</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider text-center">
                                    Days Until Empty</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-orange-600 uppercase tracking-wider text-center bg-orange-50/20">
                                    HR Linked Demand</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-indigo-600 uppercase tracking-wider text-center bg-indigo-50/30">
                                    Suggested Reorder</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider text-center">
                                    Suggested Order Date</th>
                                <th
                                    class="px-6 py-4 text-xs font-black text-gray-500 uppercase tracking-wider text-right">
                                    Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="item in forecastData" :key="item.item_id"
                                class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <p class="text-sm font-black text-gray-900 leading-none">{{ item.item_name }}</p>
                                    <p class="text-[10px] text-gray-500 font-bold uppercase mt-1">{{ item.category }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p
                                        class="text-xs font-black text-[#1C0D82] bg-indigo-50 px-2 py-0.5 rounded inline-block">
                                        {{ item.current_stock }}</p>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span class="text-xs font-bold text-gray-600">{{ item.daily_usage }}</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span
                                        :class="['text-xs font-black', parseInt(item.days_until_empty) <= 15 ? 'text-red-600' : 'text-gray-900']">
                                        {{ item.days_until_empty }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center bg-orange-50/10">
                                    <span class="text-xs font-black text-orange-600">{{ item.hr_linked_demand > 0 ? '+'
                                        : '' }}{{ item.hr_linked_demand }} units</span>
                                </td>
                                <td class="px-6 py-4 text-center bg-indigo-50/10">
                                    <span v-if="parseFloat(item.suggested_reorder_qty) > 0"
                                        class="text-xs font-black text-indigo-700">
                                        {{ item.suggested_reorder_qty }}
                                    </span>
                                    <span v-else
                                        class="text-[10px] text-gray-400 font-bold italic uppercase">Good</span>
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <div v-if="item.suggested_order_date !== 'Stock Sufficient'"
                                        class="flex flex-col items-center">
                                        <span class="text-xs font-black text-red-600">{{ item.suggested_order_date
                                            }}</span>
                                        <span
                                            class="text-[9px] text-gray-400 font-bold uppercase tracking-tighter">Recommended</span>
                                    </div>
                                    <span v-else
                                        class="text-[10px] text-green-600 font-bold uppercase italic tracking-widest leading-none">Healthy</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="[
                                        'px-2 py-0.5 rounded text-[10px] font-black uppercase tracking-tight',
                                        item.status === 'Critical' ? 'bg-red-100 text-red-700' :
                                            (item.status === 'Low' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700')
                                    ]">
                                        {{ item.status }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Insights Section -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-[#1C0D82] p-6 rounded-xl text-white shadow-lg overflow-hidden relative">
                    <div class="relative z-10 flex flex-col h-full justify-between">
                        <div class="w-10 h-10 bg-white/20 rounded flex items-center justify-center mb-4 text-white">
                            <i class="pi pi-info-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-black text-lg mb-2">Algorithm Insight</h3>
                            <p class="text-xs text-blue-100 font-medium leading-relaxed">Our AI analyzes historical
                                usage patterns and cross-references them with active recruitment cycles from your HR
                                module to predict near-term demand peaks.</p>
                        </div>
                    </div>
                    <i class="pi pi-bolt absolute -right-4 -bottom-4 text-8xl text-white/5 rotate-12"></i>
                </div>
                <div class="md:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="font-black text-[#1C0D82] mb-4 uppercase text-xs tracking-widest">Efficiency Tips</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs font-black text-gray-900 mb-1 leading-tight">Consolidate Orders</p>
                            <p class="text-[10px] text-gray-500 font-medium">You have 5 items reaching reorder points.
                                Consolidate into a single PO for supplier rating improvement.</p>
                        </div>
                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                            <p class="text-xs font-black text-gray-900 mb-1 leading-tight">Asset Collection</p>
                            <p class="text-[10px] text-gray-500 font-medium">3 employees are exiting next week. Monitor
                                return of high-value assets to reduce procurement costs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
