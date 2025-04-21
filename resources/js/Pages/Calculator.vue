<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Head } from '@inertiajs/vue3';

interface Package {
  id: number
  name: string
  description: string
  icon_photo: string
  display_price_adult: string
  display_price_child: string
  package_min_days: number
  package_max_days: number
  package_start_date: string
  package_end_date: string
}

const packages = ref<Package[]>([])
const roomTypes = ref([]);
const selectedPackageId = ref<number | null>(null)
const travelStartDate = ref<string>('')
const adults = ref<number>(1)
const children = ref<number>(0)
const roomType = ref<string>('')

const totalPrice = ref(0)
const calculating = ref(false)

const breakdown = ref<null | {
  base_charge: {
    per_adult: string
    adult_qty: number
    adult_total: string
    per_child: string
    child_qty: number
    child_total: string
    total: string
  }
  surcharge: {
    reason: string
    amount: string
  }
  extra_charges: {
    items: {
      name: string
      adult_price: string
      adult_qty: number
      adult_total: string
      child_price: string
      child_qty: number
      child_total: string
      total: string
    }[]
    total: string
  }
  add_ons: {
    items: {
      name: string
      adult_price: string
      adult_qty: number
      adult_total: string
      child_price: string
      child_qty: number
      child_total: string
      total: string
    }[]
    total: string
  }
}>(null)

const selectedPackage = computed(() =>
  packages.value.find(pkg => pkg.id === selectedPackageId.value)
)

const travelEndDate = computed(() => {
  if (!selectedPackage.value || !travelStartDate.value) return ''
  const start = new Date(travelStartDate.value)
  const end = new Date(start)
  end.setDate(start.getDate() + selectedPackage.value.package_min_days)
  return end.toISOString().split('T')[0]
})

const durationText = computed(() => {
  const days = selectedPackage.value?.package_min_days
  if (!days) return ''
  const nights = days - 1
  return `${days} day${days > 1 ? 's' : ''}, ${nights} night${nights !== 1 ? 's' : ''}`
})

const loadData = async () => {
  const response = await fetch('/calculator/api/get-resources')
  const data = await response.json()

  packages.value = data.packages
}

const fetchRoomTypes = async () => {
  const response = await fetch(`/calculator/api/get-room-types/${selectedPackageId.value}`)
  const data = await response.json()

  roomTypes.value = data
}

const calculateBackendTotal = async () => {
  if (!selectedPackageId.value || !travelStartDate.value || !roomType.value) return;

  calculating.value = true;

  try {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const response = await fetch('/calculator/api/calculate-total', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
        'X-Requested-With': 'XMLHttpRequest',
      },
      body: JSON.stringify({
        package_id: selectedPackageId.value,
        adults: adults.value,
        children: children.value,
        travel_date: travelStartDate.value,
        room_type: roomType.value
      }),
    });

    if (!response.ok) {
      throw new Error(`HTTP ${response.status}`);
    }

    const data = await response.json();
    totalPrice.value = parseFloat(data.total);
    breakdown.value = data.breakdown;
  } catch (error) {
    console.error('Calculation error:', error);
    breakdown.value = null;
  } finally {
    calculating.value = false;
  }
};

onMounted(() => {
  loadData()
})

watch([selectedPackageId, travelStartDate, adults, children, roomType], () => {
  calculateBackendTotal()
})

watch(selectedPackageId, () => {
  travelStartDate.value = ''
  fetchRoomTypes()
})
</script>

<template>
    <Head title="Travel Fee Calculator" />
  <div class="my-7 max-w-7xl mx-auto p-10 bg-white rounded-2xl shadow space-y-10 border border-gray-500">
    <h1 class="text-3xl font-bold text-gray-800">Travel Fee Calculator</h1>

    <!-- Package Selection -->
    <div class="space-y-2">
      <label class="block text-lg font-medium text-gray-700">Select Package</label>
      <select v-model="selectedPackageId" class="border border-gray-300 rounded-lg p-3 w-full">
        <option disabled value="">-- Select a package --</option>
        <option v-for="pkg in packages" :key="pkg.id" :value="pkg.id">{{ pkg.name }}</option>
      </select>
    </div>

    <!-- Travel Start Date -->
    <div class="space-y-2">
      <label class="block text-lg font-medium text-gray-700">Travel Start Date</label>
      <input
        type="date"
        v-model="travelStartDate"
        class="border border-gray-300 rounded-lg p-3 w-full"
        :min="selectedPackage?.package_start_date"
        :max="selectedPackage?.package_end_date"
        :disabled="!selectedPackage"
      />
    </div>

    <!-- Duration -->
    <div v-if="travelStartDate && selectedPackage" class="text-gray-700 mt-2 text-lg">
      <p>Start Date: <strong>{{ travelStartDate }}</strong></p>
      <p>End Date: <strong>{{ travelEndDate }}</strong></p>
      <p>Duration: <strong>{{ durationText }}</strong></p>
    </div>

    <!-- People Count -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
      <div class="space-y-2">
        <label class="block text-lg font-medium text-gray-700">Number of Adults</label>
        <input type="number" min="1" v-model="adults" class="border border-gray-300 rounded-lg p-3 w-full" />
      </div>
      <div class="space-y-2">
        <label class="block text-lg font-medium text-gray-700">Number of Children</label>
        <input type="number" min="0" v-model="children" class="border border-gray-300 rounded-lg p-3 w-full" />
      </div>
      <div class="space-y-2" v-if="roomTypes.length > 0">
        <label class="block text-lg font-medium text-gray-700">Room Type</label>
        <select v-model="roomType" class="border border-gray-300 rounded-lg p-3 w-full">
          <option disabled value="">-- Select a room type --</option>
          <option v-for="roomType in roomTypes" :key="roomType" :value="roomType">{{ roomType }}</option>
        </select>
      </div>
    </div>

    <!-- Total Price -->
    <div class="text-right mt-10 space-y-4">
      <p class="text-2xl font-semibold text-gray-900">
        {{ calculating ? 'Calculating...' : `Total Price: RM ${totalPrice.toFixed(2)}` }}
      </p>

      <!-- Breakdown -->
        <div v-if="breakdown" class="text-left mt-6 space-y-8 text-sm text-gray-800">
        <!-- Base Charge -->
        <div>
            <h2 class="font-semibold text-lg mb-2">Base Charge</h2>
            <table class="w-full border">
            <thead class="bg-gray-100">
                <tr><th class="p-2">Type</th><th>Unit Price</th><th>Qty</th><th>Total</th></tr>
            </thead>
            <tbody>
                <tr>
                <td class="p-2">Adult</td>
                <td>RM {{ breakdown.base_charge.per_adult }}</td>
                <td>{{ breakdown.base_charge.adult_qty }}</td>
                <td>RM {{ breakdown.base_charge.adult_total }}</td>
                </tr>
                <tr>
                <td class="p-2">Child</td>
                <td>RM {{ breakdown.base_charge.per_child }}</td>
                <td>{{ breakdown.base_charge.child_qty }}</td>
                <td>RM {{ breakdown.base_charge.child_total }}</td>
                </tr>
                <tr class="font-semibold border-t">
                <td colspan="3" class="p-2">Total Base Charge</td>
                <td>RM {{ breakdown.base_charge.total }}</td>
                </tr>
            </tbody>
            </table>
        </div>

        <!-- Surcharge -->
        <div>
            <h2 class="font-semibold text-lg mb-2">Surcharge</h2>
            <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                <th class="p-2">Type</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td class="p-2">Adult</td>
                <td>RM {{ breakdown.surcharge.per_adult }}</td>
                <td>{{ breakdown.surcharge.adult_qty }}</td>
                <td>RM {{ breakdown.surcharge.adult_total }}</td>
                </tr>
                <tr>
                <td class="p-2">Child</td>
                <td>RM {{ breakdown.surcharge.per_child }}</td>
                <td>{{ breakdown.surcharge.child_qty }}</td>
                <td>RM {{ breakdown.surcharge.child_total }}</td>
                </tr>
                <tr class="font-semibold border-t">
                <td colspan="3" class="p-2">Total Surcharge</td>
                <td>RM {{ breakdown.surcharge.total }}</td>
                </tr>
            </tbody>
            </table>
        </div>

        <!-- Extra Charges -->
        <div>
            <h2 class="font-semibold text-lg mb-2">Extra Charges</h2>
            <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                <th class="p-2">Type</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                <td class="p-2">Adult</td>
                <td>RM {{ breakdown.extra_charges.per_adult }}</td>
                <td>{{ breakdown.extra_charges.adult_qty }}</td>
                <td>RM {{ breakdown.extra_charges.adult_total }}</td>
                </tr>
                <tr>
                <td class="p-2">Child</td>
                <td>RM {{ breakdown.extra_charges.per_child }}</td>
                <td>{{ breakdown.extra_charges.child_qty }}</td>
                <td>RM {{ breakdown.extra_charges.child_total }}</td>
                </tr>
                <tr class="font-semibold border-t">
                <td colspan="3" class="p-2">Total Extra Charges</td>
                <td>RM {{ breakdown.extra_charges.total }}</td>
                </tr>
            </tbody>
            </table>
        </div>

        <!-- Add-ons -->
        <div v-if="breakdown.add_ons.items.length">
            <h2 class="font-semibold text-lg mb-2">Selected Add-ons</h2>
            <table class="w-full border">
            <thead class="bg-gray-100">
                <tr>
                <th class="p-2">Add-on</th>
                <th>Type</th>
                <th>Unit Price</th>
                <th>Qty</th>
                <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(item, i) in breakdown.add_ons.items" :key="i + '-adult'">
                <td class="p-2">{{ item.name }}</td>
                <td>Adult</td>
                <td>RM {{ item.adult_price }}</td>
                <td>{{ item.adult_qty }}</td>
                <td>RM {{ item.adult_total }}</td>
                </tr>
                <tr v-for="(item, i) in breakdown.add_ons.items" :key="i + '-child'">
                <td class="p-2">{{ item.name }}</td>
                <td>Child</td>
                <td>RM {{ item.child_price }}</td>
                <td>{{ item.child_qty }}</td>
                <td>RM {{ item.child_total }}</td>
                </tr>
                <tr class="font-semibold border-t">
                <td colspan="4" class="p-2">Total Add-ons</td>
                <td>RM {{ breakdown.add_ons.total }}</td>
                </tr>
            </tbody>
            </table>
        </div>
        </div>

    </div>
  </div>
</template>
