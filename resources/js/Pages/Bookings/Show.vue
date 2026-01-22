<template>
    <Head title="Booking Details" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Booking Details</h2>
                <Link
                    :href="route('bookings.edit', booking.id)"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700"
                >
                    Edit Booking
                </Link>
            </div>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-6" />

                        <!-- Payment Status -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 border border-blue-200">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div 
                                            :class="[
                                                'w-12 h-12 rounded-full flex items-center justify-center',
                                                getPaymentStatusColor(booking.status).bg
                                            ]"
                                        >
                                            <svg 
                                                :class="['w-6 h-6', getPaymentStatusColor(booking.status).icon]"
                                                fill="none" 
                                                stroke="currentColor" 
                                                viewBox="0 0 24 24"
                                            >
                                                <!-- A tick icon -->
                                                <path
                                                    v-if="[2,4].includes(booking.status)"
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M5 13l4 4L19 7"
                                                />
                                                <!-- a loading icon -->
                                                <path
                                                    v-else
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"
                                                />
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">Booking Status</p>
                                        <p class="text-lg font-semibold text-gray-900">
                                            {{ formatPaymentStatus(booking.status) }}
                                        </p>
                                        <div v-if="booking.approval_status && booking.approval_status !== 'pending'" class="mt-1">
                                            <p class="text-xs text-gray-500">
                                                {{ booking.approval_status === 'approved' ? 'Approved' : 'Rejected' }} by 
                                                <span class="font-medium">{{ booking.approver?.name || 'Admin' }}</span>
                                                <span v-if="booking.approval_date" class="ml-1">
                                                    on {{ formatDate(booking.approval_date) }}
                                                </span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Approval Actions -->
                                <div v-if="booking.status === 0" class="flex items-center gap-3">
                                    <button 
                                        @click="confirmApprove"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        Approve Booking
                                    </button>
                                    <button 
                                        @click="confirmReject"
                                        class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200"
                                    >
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Reject Booking
                                    </button>
                                </div>
                                
                                <!-- Status Badge for other statuses -->
                                <div v-else class="flex items-center">
                                    <span 
                                        :class="[
                                            'px-4 py-2 text-sm font-semibold rounded-full',
                                            booking.status == 0 ? 'bg-yellow-100 text-yellow-800' :
                                            booking.status == 1 ? 'bg-green-100 text-green-800' :
                                            booking.status == 2 ? 'bg-red-100 text-red-800' :
                                            booking.status == 3 ? 'bg-blue-100 text-blue-800' :
                                            booking.status == 4 ? 'bg-slate-100 text-slate-800' :
                                            'bg-gray-100 text-gray-800'
                                        ]"
                                    >
                                        {{ formatPaymentStatus(booking.status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Booking Information -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Booking Information</h3>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Booking Name</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.booking_name }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.phone_number }}</p>
                                    </div>
                                    <div>
                                        <!-- <label class="block text-sm font-medium text-gray-700">IC/Passport Number</label> -->
                                        <label class="block text-sm font-medium text-gray-700">Tin Number</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.booking_ic || '-' }}</p>
                                    </div>
                                    <div v-if="booking.special_remarks">
                                        <label class="block text-sm font-medium text-gray-700">Special Remarks</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.special_remarks || '-' }}</p>
                                    </div>
                                    <div v-if="booking.uuid">
                                        <label class="block text-sm font-medium text-gray-700">Booking Reference</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ booking.uuid || '-' }}</p>
                                    </div>

                                    <div v-if="booking.package">
                                        <label class="block text-sm font-medium text-gray-700">Pay now / view payment</label>
                                        <div class="mt-1 text-sm text-gray-900 flex items-center gap-2">
                                            <Link :href="route('quotation.with-hash', booking.package.uuid) + '?booking=' + booking.uuid" class="text-indigo-600 hover:text-indigo-900 mr-2">
                                                Go to payment page
                                            </Link>

                                            <button @click="copyLinkToClipboard(route('quotation.with-hash', booking.package.uuid) + '?booking=' + booking.uuid)" class="flex items-center gap-2 bg-gray-100 px-2 rounded-sm border border-gray-300">
                                                copy link
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Package Details</h3>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Package</label>
                                        <div class="mt-1 flex items-center gap-2">
                                            <span class="text-sm text-gray-900">{{ booking.package?.name || 'N/A' }}</span>
                                            <div v-if="!booking.package" class="relative group">
                                                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <div class="absolute left-1/2 -translate-x-1/2 bottom-full mb-2 hidden group-hover:block z-50 w-56 p-3 text-xs text-white bg-gray-800 rounded-lg shadow-xl whitespace-normal">
                                                    Package might be deleted. Please check with admin.
                                                    <div class="absolute top-full left-1/2 -translate-x-1/2 w-0 h-0 border-l-4 border-r-4 border-t-4 border-transparent border-t-gray-800"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Dates</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ new Date(booking.start_date).toLocaleDateString() }} - {{ new Date(booking.end_date).toLocaleDateString() }}
                                            ({{ Math.ceil((new Date(booking.end_date) - new Date(booking.start_date)) / (1000 * 60 * 60 * 24)) }} nights)
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Guests</label>
                                        <p class="mt-1 text-sm text-gray-900">
                                            {{ booking.adults }} Adult{{ booking.adults > 1 ? 's' : '' }}
                                            <span v-if="booking.children > 0">
                                                , {{ booking.children }} Child{{ booking.children > 1 ? 'ren' : '' }}
                                            </span>
                                        </p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Total Price</label>
                                        <p class="mt-1 text-sm font-medium text-gray-900">MYR {{ formatNumber(booking.total_price) }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8">
                    <div class="p-6 text-gray-900">
                        <!-- Room Details -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Room Details</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Guests</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="room in booking.rooms" :key="room.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ room.room_type?.name || 'N/A' }}
                                                <span v-if="room.room_type?.max_occupancy" class="text-xs text-indigo-600 bg-indigo-50 px-2 py-1 rounded whitespace-nowrap">
                                                    Max {{ room.room_type.max_occupancy }} pax
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.adults }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.infants }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ room.adults + room.children + room.infants }}</div>
                                        </td>
                                    </tr>
                                    <!-- Summary Row -->
                                    <!-- <tr class="bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Total</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.adults }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.adults + booking.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-500">-</div>
                                        </td>
                                    </tr> -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pricing Breakdown Section -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8" v-if="priceBreakdown?.guest_breakdown">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing Breakdown</h3>

                        <!-- Room Cards with Guest Breakdown -->
                        <div class="space-y-4">
                            <div
                                v-for="room in guestsByRoom"
                                :key="room.room_number"
                                class="bg-gray-50 rounded-lg border border-gray-200 p-4 space-y-3"
                            >
                                <!-- Room Header -->
                                <div class="border-b border-gray-200 pb-2">
                                    <div class="font-semibold text-gray-900">Room {{ room.room_number }} - {{ room.room_type_name }}</div>
                                </div>

                                <!-- Guest List -->
                                <div class="space-y-3">
                                    <div v-for="guest in room.guests" :key="`${guest.guest_type}_${guest.guest_number}`"
                                         class="text-sm py-2 bg-white rounded-lg p-3">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span :class="['px-2 py-0.5 text-xs font-semibold rounded-full', getGuestTypeBadgeClass(guest.guest_type)]">
                                                {{ guest.guest_type.charAt(0).toUpperCase() + guest.guest_type.slice(1) }}
                                            </span>
                                            <span class="text-gray-900 font-medium">{{ guest.guest_type.charAt(0).toUpperCase() + guest.guest_type.slice(1) }} {{ guest.guest_number }}</span>
                                        </div>
                                        <div class="pl-2 space-y-1 text-xs">
                                            <div :class="['flex', 'justify-between', 'pb-1', getGuestAddOnItems(room.room_number, guest.guest_type, guest.guest_number).length > 0 ? 'border-b border-gray-200' : '']">
                                                <span class="text-gray-500">Package:</span>
                                                <span class="text-gray-700">MYR {{ formatNumber(getPackagePrice(guest)) }}</span>
                                            </div>
                                            <div v-for="(addOnItem, addOnIndex) in getGuestAddOnItems(room.room_number, guest.guest_type, guest.guest_number)" :key="'addon-' + addOnIndex" class="flex pb-1">
                                                <span class="flex-1 text-gray-500">{{ addOnItem.name }}:</span>
                                                <span class="flex-1 text-gray-700 text-right">MYR {{ formatNumber(addOnItem.price) }}</span>
                                            </div>
                                            <div class="flex justify-between font-medium pt-1 mt-1 bg-indigo-50 -mx-2 px-2 py-1 rounded">
                                                <span class="text-indigo-700">Total for {{ guest.guest_type.charAt(0).toUpperCase() + guest.guest_type.slice(1) }} {{ guest.guest_number }}:</span>
                                                <span class="text-indigo-900">MYR {{ formatNumber(Math.floor(getPackagePrice(guest)) + Math.floor(getGuestAddOnTotal(room.room_number, guest.guest_type, guest.guest_number))) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Total -->
                                <div class="flex justify-between pt-2 border-t border-gray-200">
                                    <span class="font-semibold text-gray-900">Room Total:</span>
                                    <span class="font-bold text-indigo-600">MYR {{ formatNumber(getRoomTotalWithSst(room)) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Grand Total Summary -->
                        <div class="mt-6 bg-indigo-50 rounded-lg p-4 border border-indigo-200">
                            <div class="flex justify-between items-center">
                                <span class="font-semibold text-indigo-900 text-lg">Grand Total:</span>
                                <span class="font-bold text-indigo-900 text-xl">MYR {{ formatNumber(guestsByRoom.reduce((sum, room) => sum + getRoomTotalWithSst(room), 0)) }}</span>
                            </div>
                            <div v-if="priceBreakdown.sst > 0" class="mt-2 text-sm text-indigo-700">
                                (Includes SST {{ sstPercent }}%)
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Fallback when breakdown unavailable -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8" v-else-if="booking.package">
                    <div class="p-6 text-gray-900">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing Breakdown</h3>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start gap-2 text-yellow-800">
                                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <div>
                                    <span class="text-sm">Detailed pricing breakdown is not available for this booking.</span>
                                    <p v-if="priceBreakdownError" class="text-xs mt-1 text-yellow-700">
                                        Reason: {{ priceBreakdownError }}
                                    </p>
                                    <p v-else class="text-xs mt-1 text-yellow-700">
                                        This may occur if the package configuration has been modified.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8" v-if="booking.add_ons && booking.add_ons.length > 0">
                    <div class="p-6 text-gray-900">
                        <!-- Add-ons Details -->
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Add-ons Details</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Add-on Name</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Adults</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Children</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Infants</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prices</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="addOn in booking.add_ons" :key="addOn.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ addOn.package_add_on?.name || 'N/A' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900">{{ addOn.package_add_on?.description || '-' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ addOn.adults }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ addOn.children }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-900">{{ addOn.infants }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-gray-600 space-y-1">
                                                <div v-if="addOn.package_add_on?.adult_price">
                                                    Adult: MYR {{ formatNumber(applySST(addOn.package_add_on.adult_price)) }}
                                                </div>
                                                <div v-if="addOn.package_add_on?.child_price">
                                                    Child: MYR {{ formatNumber(applySST(addOn.package_add_on.child_price)) }}
                                                </div>
                                                <div v-if="addOn.package_add_on?.infant_price">
                                                    Infant: MYR {{ formatNumber(applySST(addOn.package_add_on.infant_price)) }}
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                MYR {{ formatNumber(
                                                    (addOn.adults * applySST(addOn.package_add_on?.adult_price || 0)) +
                                                    (addOn.children * applySST(addOn.package_add_on?.child_price || 0)) +
                                                    (addOn.infants * applySST(addOn.package_add_on?.infant_price || 0))
                                                ) }}
                                            </div>
                                        </td>
                                    </tr>
                                    <!-- Summary Row -->
                                    <tr class="bg-gray-50">
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">Add-ons Total</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">
                                                MYR {{ formatNumber(
                                                    booking.add_ons.reduce((total, addOn) => {
                                                        return total +
                                                            (addOn.adults * applySST(addOn.package_add_on?.adult_price || 0)) +
                                                            (addOn.children * applySST(addOn.package_add_on?.child_price || 0)) +
                                                            (addOn.infants * applySST(addOn.package_add_on?.infant_price || 0));
                                                    }, 0)
                                                ) }}
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-8" v-if="booking.transactions && booking.transactions.length > 0">
                    <div class="p-6 text-gray-900">
                        <!-- Transaction History -->
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Transaction History</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Transaction ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="transaction in booking.transactions" :key="transaction.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ new Date(transaction.created_at).toLocaleDateString() }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.transaction_id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ transaction.payment_method }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                MYR {{ formatNumber(transaction.amount) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span 
                                                    :class="[
                                                        'px-2 py-1 text-sm font-semibold rounded-full',
                                                        transaction.status === 'completed' ? 'bg-green-100 text-green-800' :
                                                        transaction.status === 'pending' ? 'bg-yellow-100 text-yellow-800' :
                                                        'bg-red-100 text-red-800'
                                                    ]"
                                                >
                                                    {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed } from 'vue';
import Swal from 'sweetalert2';
import moment from 'moment';

const props = defineProps({
    booking: Object,
    sstPercent: {
        type: Number,
        default: 0
    },
    priceBreakdown: {
        type: Object,
        default: null
    },
    priceBreakdownError: {
        type: String,
        default: null
    }
});

const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(number);
};

// Apply SST and floor to a price
const applySST = (price) => {
    const priceWithSst = price * (1 + props.sstPercent / 100);
    return Math.floor(priceWithSst);
};

const formatDate = (date) => {
    return moment(date).format('DD MMM YYYY, h:mm A');
};

// Computed property to group guests by room for breakdown view
const guestsByRoom = computed(() => {
    if (!props.priceBreakdown?.guest_breakdown) return [];
    const guests = Object.values(props.priceBreakdown.guest_breakdown);
    const grouped = {};

    guests.forEach(guest => {
        const roomNum = guest.room_number;
        if (!grouped[roomNum]) {
            grouped[roomNum] = {
                room_number: roomNum,
                room_type_name: guest.room_type_name,
                nights: guest.nights,
                guests: [],
                total: 0
            };
        }
        grouped[roomNum].guests.push(guest);
        grouped[roomNum].total += guest.total;
    });

    return Object.values(grouped);
});

// Get surcharge per guest by type for a specific room
const getGuestSurcharge = (guestType, roomNumber) => {
    if (!props.priceBreakdown?.rooms) return 0;
    const roomIndex = roomNumber - 1;
    const room = props.priceBreakdown.rooms[roomIndex];
    if (!room?.summary?.surcharges) return 0;

    const surcharges = room.summary.surcharges;
    if (guestType === 'adult' && room.adults > 0) {
        return (surcharges.adult?.total || 0) / room.adults;
    }
    if (guestType === 'child' && room.children > 0) {
        return (surcharges.child?.total || 0) / room.children;
    }
    if (guestType === 'infant' && room.infants > 0) {
        return (surcharges.infant?.total || 0) / room.infants;
    }
    return 0;
};

// Calculate package price per guest (base + surcharge + SST)
const getPackagePrice = (guest) => {
    const base = guest.base_charge?.total || 0;
    const surcharge = getGuestSurcharge(guest.guest_type, guest.room_number);
    // Calculate SST as percentage of (base + surcharge)
    const sstPercent = props.sstPercent || 0;
    const sst = (base + surcharge) * (sstPercent / 100);
    return base + surcharge + sst;
};

// Get add-on total for a specific guest
const getGuestAddOnTotal = (roomNumber, guestType, guestNumber) => {
    if (!props.priceBreakdown?.add_ons?.length) return 0;

    const roomAddOns = props.priceBreakdown.add_ons.filter(a => Number(a.room_number) === Number(roomNumber));
    let total = 0;

    roomAddOns.forEach(addOn => {
        if (guestType === 'adult' && addOn.adults >= guestNumber) {
            total += Math.floor(parseFloat(addOn.adult_price) || 0);
        } else if (guestType === 'child' && addOn.children >= guestNumber) {
            total += Math.floor(parseFloat(addOn.child_price) || 0);
        } else if (guestType === 'infant' && addOn.infants >= guestNumber) {
            total += Math.floor(parseFloat(addOn.infant_price) || 0);
        }
    });

    return total;
};

// Get add-on items for a specific guest with names and prices
const getGuestAddOnItems = (roomNumber, guestType, guestNumber) => {
    if (!props.priceBreakdown?.add_ons?.length) return [];

    const roomAddOns = props.priceBreakdown.add_ons.filter(a => Number(a.room_number) === Number(roomNumber));
    const items = [];

    roomAddOns.forEach(addOn => {
        let price = 0;
        let applicable = false;

        if (guestType === 'adult' && addOn.adults >= guestNumber) {
            price = Math.floor(parseFloat(addOn.adult_price) || 0);
            applicable = true;
        } else if (guestType === 'child' && addOn.children >= guestNumber) {
            price = Math.floor(parseFloat(addOn.child_price) || 0);
            applicable = true;
        } else if (guestType === 'infant' && addOn.infants >= guestNumber) {
            price = Math.floor(parseFloat(addOn.infant_price) || 0);
            applicable = true;
        }

        if (applicable && price > 0) {
            items.push({ name: addOn.name, price });
        }
    });

    return items;
};

// Get room total by summing individual guest totals (package price + add-ons), floored per guest
const getRoomTotalWithSst = (room) => {
    if (!room.guests?.length) return 0;
    let total = 0;
    room.guests.forEach(guest => {
        // Floor each guest's total individually to match displayed values
        const guestPackage = Math.floor(getPackagePrice(guest));
        const guestAddOn = Math.floor(getGuestAddOnTotal(room.room_number, guest.guest_type, guest.guest_number));
        total += guestPackage + guestAddOn;
    });
    return total;
};

// Get badge class for guest type
const getGuestTypeBadgeClass = (guestType) => {
    switch (guestType) {
        case 'adult':
            return 'bg-indigo-100 text-indigo-800';
        case 'child':
            return 'bg-green-100 text-green-800';
        case 'infant':
            return 'bg-yellow-100 text-yellow-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const breadcrumbs = computed(() => [
    { title: 'Bookings', link: route('bookings.index') },
    { title: 'Booking Details' }
]);

const getPaymentStatusColor = (status) => {
    switch (status) {
        case 0:
            return {
                bg: 'bg-yellow-100',
                icon: 'text-yellow-600'
            };
        case 1:
            return {            
                bg: 'bg-green-100',
                icon: 'text-green-600'
            };
        case 2:
            return {
                bg: 'bg-red-100',
                icon: 'text-red-600'   
            };
        case 3:
            return {
                bg: 'bg-blue-100',
                icon: 'text-blue-600'
            };      
        case 4:
            return {
                bg: 'bg-white',
                icon: 'text-slate-600'
            };
        default:
            return {
                bg: 'bg-gray-100',
                icon: 'text-gray-600'
            };
    }
};

const formatPaymentStatus = (status) => {
    switch (status) {
        case 0:
            return 'Pending Approval';
        case 1:
            return 'Approved';
        case 2:
            return 'Rejected';
        case 3: 
            return 'Payment Completed';
        case 4: 
            return 'Refunded';
        default:
            return '';  
    }
};

const copyLinkToClipboard = (link) => {
    navigator.clipboard.writeText(link);
    alert('Link copied to clipboard');
};

const confirmApprove = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "Are you sure you want to approve this booking?",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, approve it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            approveBooking();
        }
    });
};

const confirmReject = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: "Are you sure you want to reject this booking?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, reject it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            rejectBooking();
        }
    });
};

const approveBooking = async () => {
    try {
        const response = await fetch(route('bookings.approve', props.booking.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire(
                'Approved!',
                'Booking has been approved successfully.',
                'success'
            ).then(() => {
                router.reload();
            });
        } else {
            Swal.fire(
                'Error!',
                data.message || 'Failed to approve booking.',
                'error'
            );
        }
    } catch (error) {
        Swal.fire(
            'Error!',
            'An error occurred while approving the booking.',
            'error'
        );
    }
};

const rejectBooking = async () => {
    try {
        const response = await fetch(route('bookings.reject', props.booking.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        const data = await response.json();

        if (data.success) {
            Swal.fire(
                'Rejected!',
                'Booking has been rejected successfully.',
                'success'
            ).then(() => {
                router.reload();
            });
        } else {
            Swal.fire(
                'Error!',
                data.message || 'Failed to reject booking.',
                'error'
            );
        }
    } catch (error) {
        Swal.fire(
            'Error!',
            'An error occurred while rejecting the booking.',
            'error'
        );
    }
};
</script>
