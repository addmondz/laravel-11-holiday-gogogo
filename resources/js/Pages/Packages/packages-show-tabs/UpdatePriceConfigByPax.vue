<template>
  <div class="max-w-2xl bg-white space-y-8">
    <h3 class="text-md font-medium text-gray-900">Update Price Config by Pax</h3>

    <div class="space-y-6">
      <!-- Pax selectors -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="adult">Adult</label>
          <select v-model="form.adult" @change="updateDropdowns" class="w-full rounded-lg border-gray-300 px-3 py-2">
            <option v-for="a in adultOptions" :key="a" :value="a">{{ a }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="child">Child</label>
          <select v-model="form.child" @change="updateDropdowns" class="w-full rounded-lg border-gray-300 px-3 py-2">
            <option v-for="c in childOptions" :key="c" :value="c">{{ c }}</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="infant">Infant</label>
          <select v-model="form.infant" @change="updateDropdowns" class="w-full rounded-lg border-gray-300 px-3 py-2">
            <option v-for="i in infantOptions" :key="i" :value="i">{{ i }}</option>
          </select>
        </div>
      </div>

      <!-- Amounts -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="adult_amount">Adult Base Charge (RM)</label>
          <input
            v-model.number="form.adult_base_charge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 120"
          />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="child_amount">Child Base Charge (RM)</label>
          <input
            v-model.number="form.child_base_charge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 90"
          />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="infant_amount">Infant Base Charge (RM)</label>
          <input
            v-model.number="form.infant_base_charge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 50"
          />
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="adult_surcharge">Adult Surcharge (RM)</label>
          <input
            v-model.number="form.adult_surcharge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 120"
          />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="child_surcharge">Child Surcharge (RM)</label>
          <input
            v-model.number="form.child_surcharge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 90"
          />
        </div>
        <div>
          <label class="block text-sm text-gray-600 mb-1" for="infant_surcharge">Infant Surcharge (RM)</label>
          <input
            v-model.number="form.infant_surcharge"
            type="number"
            class="w-full rounded-lg border-gray-300 px-3 py-2"
            placeholder="e.g. 50"
          />
        </div>
      </div>
    </div>

    <div>
      <button
        @click="submit"
        class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
      >
        Update
      </button>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, computed, watch } from 'vue'
import Swal from 'sweetalert2'
import axios from 'axios'

const props = defineProps({
  package: Object,
})

const VALID_OCCUPANCY_COMBINATIONS = [
  { adults: 1, children: 0, infants: 0 },
  { adults: 1, children: 0, infants: 1 },
  { adults: 1, children: 0, infants: 2 },
  { adults: 1, children: 0, infants: 3 },
  { adults: 1, children: 1, infants: 0 },
  { adults: 1, children: 1, infants: 1 },
  { adults: 1, children: 1, infants: 2 },
  { adults: 1, children: 2, infants: 0 },
  { adults: 1, children: 2, infants: 1 },
  { adults: 1, children: 3, infants: 0 },
  { adults: 2, children: 0, infants: 0 },
  { adults: 2, children: 0, infants: 1 },
  { adults: 2, children: 0, infants: 2 },
  { adults: 2, children: 1, infants: 0 },
  { adults: 2, children: 1, infants: 1 },
  { adults: 2, children: 2, infants: 0 },
  { adults: 2, children: 0, infants: 2 },
  { adults: 3, children: 0, infants: 0 },
  { adults: 3, children: 0, infants: 1 },
  { adults: 3, children: 1, infants: 0 },
  { adults: 4, children: 0, infants: 0 }
]

const form = reactive({
  package_id: props.package.id,
  adult: 1,
  child: 0,
  infant: 0,
  adult_base_charge: 0,
  child_base_charge: 0,
  infant_base_charge: 0,
  adult_surcharge: 0,
  child_surcharge: 0,
  infant_surcharge: 0,
})

const adultOptions = computed(() => {
  const unique = new Set(VALID_OCCUPANCY_COMBINATIONS.map(c => c.adults))
  return [...unique].sort((a, b) => a - b)
})

const childOptions = ref([])
const infantOptions = ref([])

const updateDropdowns = () => {
  childOptions.value = [
    ...new Set(
      VALID_OCCUPANCY_COMBINATIONS
        .filter(c => c.adults === form.adult)
        .map(c => c.children)
    )
  ].sort((a, b) => a - b)

  if (!childOptions.value.includes(form.child)) {
    form.child = childOptions.value[0] ?? 0
  }

  infantOptions.value = [
    ...new Set(
      VALID_OCCUPANCY_COMBINATIONS
        .filter(c => c.adults === form.adult && c.children === form.child)
        .map(c => c.infants)
    )
  ].sort((a, b) => a - b)

  if (!infantOptions.value.includes(form.infant)) {
    form.infant = infantOptions.value[0] ?? 0
  }
}

// Initialize dropdowns on load
updateDropdowns()

watch(() => form.adult, updateDropdowns)
watch(() => form.child, updateDropdowns)

const submit = async () => {
  const result = await Swal.fire({
    title: 'Confirm Update?',
    text: 'Do you want to save these pax prices?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Yes, update it!',
  })

  if (result.isConfirmed) {
    try {
      await axios.post(route('configuration-prices.updatePriceConfigurationByPax'), form)

      Swal.fire({
        title: 'Success!',
        text: 'Price configuration updated successfully.',
        icon: 'success',
        confirmButtonColor: '#4f46e5',
      })
    } catch (error) {
      Swal.fire({
        title: 'Error!',
        text: error.response?.data?.message || 'Something went wrong.',
        icon: 'error',
        confirmButtonColor: '#e11d48',
      })
    }
  }
}
</script>
