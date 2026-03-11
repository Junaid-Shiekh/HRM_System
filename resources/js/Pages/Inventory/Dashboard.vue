<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: Object,
    recentLogs: Array,
    warehouseStocks: Array,
});
</script>

<template>
    <Head title="Inventory Dashboard" />

    <AuthenticatedLayout>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-[#1C0D82]">Inventory Dashboard</h2>
                <div class="flex gap-2">
                     <Link :href="route('inventory.forecast')" class="inline-flex items-center px-4 py-2 bg-[#EAB308] text-white rounded-lg hover:bg-yellow-600 transition-colors font-semibold">
                        <i class="pi pi-bolt mr-2"></i>
                        AI Forecast
                    </Link>
                    <Link :href="route('inventory.purchase-orders.index')" class="inline-flex items-center px-4 py-2 bg-[#1C0D82] text-white rounded-lg hover:bg-blue-900 transition-colors font-semibold">
                        <i class="pi pi-plus mr-2"></i>
                        New PO
                    </Link>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-indigo-50 rounded-lg flex items-center justify-center text-[#1C0D82]">
                        <i class="pi pi-box text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Total Items</p>
                        <p class="text-2xl font-black text-gray-900">{{ stats.totalItems }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 rounded-lg flex items-center justify-center text-green-600">
                        <i class="pi pi-truck text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Suppliers</p>
                        <p class="text-2xl font-black text-gray-900">{{ stats.totalSuppliers }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-blue-50 rounded-lg flex items-center justify-center text-blue-600">
                        <i class="pi pi-home text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Warehouses</p>
                        <p class="text-2xl font-black text-gray-900">{{ stats.totalWarehouses }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-orange-50 rounded-lg flex items-center justify-center text-orange-600">
                        <i class="pi pi-shopping-cart text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Pending POs</p>
                        <p class="text-2xl font-black text-gray-900">{{ stats.pendingPOs }}</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-red-100 shadow-sm flex items-center gap-4">
                    <div class="w-12 h-12 bg-red-50 rounded-lg flex items-center justify-center text-red-600">
                        <i class="pi pi-exclamation-triangle text-2xl"></i>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 uppercase font-bold tracking-wider">Low Stock</p>
                        <p class="text-2xl font-black text-red-600">{{ stats.lowStockItems }}</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Activity -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-[#1C0D82]">Recent Stock Movements</h3>
                        <Link :href="route('inventory.stocks.logs')" class="text-xs font-bold text-[#EAB308] hover:underline uppercase">View All</Link>
                    </div>
                    <div class="p-6">
                        <div v-if="recentLogs.length === 0" class="text-center py-8 text-gray-400">
                            No recent stock movements.
                        </div>
                        <div v-else class="space-y-4">
                            <div v-for="log in recentLogs" :key="log.id" class="flex items-center gap-4 p-3 hover:bg-gray-50 rounded-lg transition-colors border border-transparent hover:border-gray-100">
                                <div :class="[
                                    'w-10 h-10 rounded-full flex items-center justify-center',
                                    log.type === 'In' ? 'bg-green-50 text-green-600' : 
                                    (log.type === 'Out' ? 'bg-red-50 text-red-600' : 'bg-blue-50 text-blue-600')
                                ]">
                                    <i :class="log.type === 'In' ? 'pi pi-arrow-down-left' : (log.type === 'Out' ? 'pi pi-arrow-up-right' : 'pi pi-sync')"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-gray-900">{{ log.item.name }}</p>
                                    <p class="text-xs text-gray-500">{{ log.warehouse.name }} • {{ log.reason }}</p>
                                </div>
                                <div class="text-right">
                                    <p :class="['text-sm font-black', log.quantity > 0 ? 'text-green-600' : 'text-red-600']">
                                        {{ log.quantity > 0 ? '+' : '' }}{{ log.quantity }}
                                    </p>
                                    <p class="text-[10px] text-gray-400 font-medium uppercase">{{ new Date(log.created_at).toLocaleDateString() }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Warehouse Overview -->
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-50 bg-gray-50/50 flex justify-between items-center">
                        <h3 class="font-bold text-[#1C0D82]">Warehouse Stock Summary</h3>
                        <Link :href="route('inventory.warehouses.index')" class="text-xs font-bold text-[#EAB308] hover:underline uppercase">Manage</Link>
                    </div>
                    <div class="p-6">
                        <div v-if="warehouseStocks.length === 0" class="text-center py-8 text-gray-400">
                            No warehouses defined.
                        </div>
                        <div v-else class="space-y-6">
                            <div v-for="warehouse in warehouseStocks" :key="warehouse.id" class="space-y-2">
                                <div class="flex justify-between items-end">
                                    <div>
                                        <h4 class="text-sm font-black text-gray-900 uppercase tracking-tight">{{ warehouse.name }}</h4>
                                        <p class="text-[10px] text-gray-500 font-medium">{{ warehouse.location }}</p>
                                    </div>
                                    <span class="text-xs font-bold px-2 py-0.5 bg-[#1C0D82]/5 text-[#1C0D82] rounded">
                                        {{ warehouse.stocks.length }} Unique Items
                                    </span>
                                </div>
                                <div class="w-full bg-gray-100 h-1.5 rounded-full overflow-hidden">
                                     <div class="bg-[#EAB308] h-full" :style="{ width: '70%' }"></div>
                                </div>
                                <div class="flex gap-4 overflow-x-auto pb-2 scrollbar-hide">
                                    <div v-for="stock in warehouse.stocks.slice(0, 3)" :key="stock.id" class="flex-shrink-0 bg-gray-50 px-3 py-2 rounded border border-gray-100">
                                        <p class="text-[10px] text-gray-500 font-bold uppercase truncate w-24">{{ stock.item.name }}</p>
                                        <p class="text-sm font-black text-[#1C0D82]">{{ stock.quantity }} <span class="text-[10px] font-medium text-gray-400">{{ stock.item.uom }}</span></p>
                                    </div>
                                    <div v-if="warehouse.stocks.length > 3" class="flex-shrink-0 bg-white px-3 py-2 rounded border border-dashed border-gray-200 flex items-center justify-center">
                                         <p class="text-[10px] font-bold text-gray-400">+{{ warehouse.stocks.length - 3 }} more</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
</style>
