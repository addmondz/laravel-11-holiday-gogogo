<template>
  <div class="space-y-6">
    <!-- Filters -->
    <div class="bg-white shadow rounded-lg p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-medium text-gray-700">Season</label>
          <select
            v-model="selectedSeason"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option value="">Select Season</option>
            <option v-for="s in assignedSeasonTypes" :key="s.id" :value="s.id">
              {{ s.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700">Date Type</label>
          <select
            v-model="selectedDateType"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option value="">Select Date Type</option>
            <option v-for="t in assignedDateTypes" :key="t.id" :value="t.id">
              {{ t.name }}
            </option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700"
            >Room Type (Optional)</label
          >
          <select
            v-model="selectedRoomType"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
          >
            <option value="">All Room Types</option>
            <option v-for="(name, id) in packageUniqueRoomTypes" :key="id" :value="id">
              {{ name }}
            </option>
          </select>
        </div>
      </div>

      <div class="mt-6 flex gap-2">
        <button
          @click="fetchPrices"
          :disabled="!canSearch || isPriceLoading"
          class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <span v-if="isPriceLoading">Loading...</span>
          <span v-else>Search Prices</span>
        </button>
        <button
          @click="resetSearch"
          class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-xs"
        >
          Reset
        </button>
      </div>
    </div>

    <!-- Results -->
    <div v-if="searched" class="bg-white shadow rounded-lg p-6">
      <div v-if="isPriceLoading" class="flex items-center justify-center min-h-[300px]">
        <LoadingComponent />
      </div>

      <div v-else-if="!compactRooms.length" class="text-center text-gray-500">
        No price configurations found for the selected filters.
      </div>

      <div v-else class="space-y-10">
        <!-- Action Bar -->
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-lg font-semibold">Price Matrix</h2>
            <p class="text-xs text-gray-500">
              Base charge applies to each slot (Adult&nbsp;1, Adult&nbsp;2, Child&nbsp;1,
              …). Surcharge applies per person (Adult / Child / Infant).
            </p>
          </div>
          <div class="space-x-2">
            <template v-if="isEditMode">
              <button
                @click="cancelEdit"
                class="px-3 py-2 rounded bg-gray-100 text-gray-700 text-sm"
              >
                Cancel
              </button>
              <button
                @click="saveAll"
                class="px-3 py-2 rounded bg-indigo-600 text-white text-sm"
                :disabled="saveLoading"
              >
                <span v-if="saveLoading">Saving...</span>
                <span v-else class="px-3 py-2">Save</span>
              </button>
            </template>
          </div>
        </div>

        <div v-if="isEditMode" class="mb-4">
          <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start space-x-3">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="flex-1">
                <h3 class="text-sm font-medium text-blue-800 mb-1">
                  Quick Copy Feature
                </h3>

                <p class="text-sm text-blue-700">
                  Click the 
                  <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                    <CopyOutlined class="h-3 w-3 mr-1" />
                    copy icon
                  </span>
                  next to any price to instantly apply that same value across matching fields.  
                  The copy behavior differs slightly between <strong>Base Charge</strong> and <strong>Surcharges</strong>:
                </p>

                <ul class="mt-3 text-xs text-blue-700 list-disc list-inside space-y-3">
                  <li>
                    <span class="font-semibold text-blue-800">Base Charge:</span>
                    Copies the value to all fields with the same <strong>person type</strong> (e.g. Adults, Children) within the <strong>same row</strong>.
                    <div class="mt-1 text-blue-600">
                      <span class="font-medium">Example:</span> Clicking the copy icon beside <strong>Adult 1</strong> in the row 
                      “4 Adults + 0 Children + 0 Infants” will automatically copy that price to 
                      <strong>Adult 2</strong>, <strong>Adult 3</strong>, and <strong>Adult 4</strong> in that same row.
                    </div>
                  </li>

                  <li>
                    <span class="font-semibold text-blue-800">Surcharges:</span>
                    Copies the value to all fields with the same <strong>person type</strong> within the <strong>same room</strong>.
                    <div class="mt-1 text-blue-600">
                      <span class="font-medium">Example:</span> Clicking the copy icon beside <strong>Adult 1</strong> under 
                      “Room A” will automatically copy that surcharge value to all the adults surcharge within that same room.
                    </div>
                  </li>
                </ul>

                <div class="mt-3 text-xs text-blue-600">
                  <span class="font-bold">Remember:</span> Always click <em>Save</em> after copying values to confirm your changes.
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- PER ROOM -->
        <div
          v-for="room in compactRooms"
          :key="room.room_type_id"
          class="space-y-8"
          :data-package-config-id="room.id"
        >
          <div class="flex items-center justify-between">
            <h3 class="font-medium text-gray-900">
              {{ room.room_type_name }} ( {{ room.room_type_capacity }} pax )
            </h3>
            <div class="space-x-2">
              <button
                v-if="!isEditMode"
                @click="isEditMode = true"
                class="px-5 py-2 rounded bg-indigo-600 text-white text-sm"
              >
                Edit All Prices
              </button>
              <button
                v-if="!isEditMode && compactRooms.length > 0"
                @click="duplicateToMultipleBtnClick"
                class="px-5 py-2 rounded bg-green-600 text-white text-sm hover:bg-green-700"
              >
                Duplicate to Multiple
              </button>
            </div>
          </div>

          <!-- BASE TABLE -->
          <section style="margin-top: 10px">
            <div class="overflow-x-auto border rounded-lg">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                  <tr>
                    <th
                      class="px-4 py-2 text-left"
                      :colspan="getLargestIndexNumber(room.base)"
                    >
                      Base Charge
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="([comboKey, slots], idx) in comboEntries(room.base)"
                    :key="comboKey"
                    :class="idx % 2 ? 'bg-white' : 'bg-gray-50'"
                  >
                    <td
                      class="px-4 py-2 align-middle whitespace-nowrap font-medium text-gray-800"
                    >
                      {{ comboVerbose(comboKey) }}
                    </td>
                    <td
                      class="px-4 py-2"
                      v-for="slotKey in orderedSlotKeys(slots)"
                      :key="slotKey"
                    >
                      <div class="flex flex-col items-start justify-start w-32">
                        <span class="text-xs text-gray-600">{{
                          slotLabel(slotKey)
                        }}</span>
                        <template v-if="isEditMode">
                          <div class="flex items-center rounded-md shadow-sm border border-gray-300 focus-within:ring-2 focus-within:ring-indigo-500">
                            <input
                              type="number"
                              step="0.01"
                              class="w-full rounded-l-md border-none focus:ring-0 px-3 py-2"
                              v-model.number="room.base[comboKey][slotKey]"
                              @input="checkSameCategorySamePrice(room, comboKey, slotKey)"
                            />
                            <button
                              type="button"
                              class="flex items-center justify-center px-2 border-l border-gray-300 text-gray-500 hover:text-indigo-600 hover:bg-gray-50 rounded-r-md"
                              title="Copy this pax type's value across this row"
                              @click="copyPriceTypeToSameRow(room, comboKey, slotKey)"
                            >
                              <CopyOutlined class="h-5 w-5" />
                            </button>
                          </div>
                        </template>
                        <template v-else>
                          <span class="mt-1 font-medium"
                            >MYR {{ format(room.base[comboKey][slotKey]) }}</span
                          >
                        </template>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          <!-- SURCH TABLE (aligned like Base) -->
          <section
            v-if="room.surch && Object.keys(room.surch || {}).length"
            style="margin-top: 0"
          >
            <div class="overflow-x-auto border rounded-lg">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                  <tr>
                    <th class="px-4 py-2 text-left">Surcharge</th>
                    <th v-for="k in ['a', 'c', 'i']" :key="k" class="px-4 py-2 text-left">
                      {{ surchargeLabel(k) }}
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="([comboKey, slots], idx) in comboEntries(room.surch)"
                    :key="comboKey"
                    :class="idx % 2 ? 'bg-white' : 'bg-gray-50'"
                  >
                    <td class="px-4 py-3 font-medium text-gray-800 whitespace-nowrap">
                      {{ comboVerbose(comboKey) }}
                    </td>

                    <!-- Adult / Child / Infant columns -->
                    <td v-for="k in ['a', 'c', 'i']" :key="k" class="px-4 py-2">
                      <template v-if="slots[k] !== undefined">
                        <template v-if="isEditMode">
                          <div class="flex">
                            <input
                              type="number"
                              step="0.01"
                              class="w-24 rounded-l-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                              v-model.number="room.surch[comboKey][k]"
                            />
                            <button
                              type="button"
                              class="flex items-center justify-center px-2 border-l border-gray-300 text-gray-500 hover:text-indigo-600 hover:bg-gray-50 rounded-r-md"
                              title="Copy this surcharge value to all rows"
                              @click="copySurchargeTypeToSameRow(room, comboKey, k)"
                            >
                              <CopyOutlined class="h-5 w-5" />
                            </button>
                          </div>
                        </template>
                        <template v-else> MYR {{ format(slots[k]) }} </template>
                      </template>
                      <!-- if key missing, leave blank to keep column alignment -->
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>
        </div>
      </div>
    </div>

    <!-- Duplicate Modal -->
    <div v-if="showDuplicateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" style="margin-top: 0;">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-4/5 lg:w-3/4 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Duplicate Price Configuration</h3>
            <button @click="closeDuplicateModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          <small class="text-gray-500 mb-4 block">
            If you wish to duplicate to all room types, please select "All Room Types" for both target room type and source room type.
          </small>
          
          <div class="space-y-6">
            <!-- Source Configuration -->
            <div class="bg-gray-50 p-4 rounded-lg">
              <h4 class="text-md font-medium text-gray-900 mb-3">Source Configuration (Copy From)</h4>
              <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Season</label>
                  <select v-model="duplicateForm.sourceSeason" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Season</option>
                    <option v-for="s in assignedSeasonTypes" :key="s.id" :value="s.id">
                      {{ s.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Date Type</label>
                  <select v-model="duplicateForm.sourceDateType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">Select Date Type</option>
                    <option v-for="t in assignedDateTypes" :key="t.id" :value="t.id">
                      {{ t.name }}
                    </option>
                  </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Room Type (Optional)</label>
                  <select v-model="duplicateForm.sourceRoomType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">All Room Types</option>
                    <option v-for="(name, id) in packageUniqueRoomTypes" :key="id" :value="id">
                      {{ name }}
                    </option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Target Configuration -->
            <div class="bg-green-50 p-4 rounded-lg">
              <div class="flex items-center justify-between">
                <h4 class="text-md font-medium text-gray-900 mb-4">Target Configuration (Copy To)</h4>
                <div>
                  <span class="text-sm text-gray-500 mr-2">Target Count: {{ targetLength }}</span>
                  <button @click="addTargetConfiguration" class="px-2 py-1 bg-green-600 text-white rounded-md hover:bg-green-700 text-xs">
                    Add Target
                  </button>
                </div>
              </div>
               <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4" v-for="i in targetLength" :key="i">
                 <div>
                   <label class="block text-sm font-medium text-gray-700 mb-1">Season</label>
                   <select v-model="duplicateForm.targetConfigurations[i-1].season" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                     <option value="">Select Season</option>
                     <option v-for="s in assignedSeasonTypes" :key="s.id" :value="s.id">
                       {{ s.name }}
                     </option>
                   </select>
                 </div>
                 <div>
                   <label class="block text-sm font-medium text-gray-700 mb-1">Date Type</label>
                   <select v-model="duplicateForm.targetConfigurations[i-1].dateType" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                     <option value="">Select Date Type</option>
                     <option v-for="t in assignedDateTypes" :key="t.id" :value="t.id">
                       {{ t.name }}
                     </option>
                   </select>
                 </div>
                 <div>
                   <label class="block text-sm font-medium text-gray-700 mb-1">Room Type (Optional)</label>
                   <div class="flex gap-2">
                     <select v-model="duplicateForm.targetConfigurations[i-1].roomType" class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                       <option value="">All Room Types</option>
                       <option v-for="(name, id) in packageUniqueRoomTypes" :key="id" :value="id">
                         {{ name }}
                       </option>
                     </select>
                     <button
                       v-if="targetLength > 1"
                       @click="removeTargetConfiguration(i-1)"
                       class="px-2 py-1 bg-red-600 text-white rounded-md hover:bg-red-700 text-xs"
                       title="Remove this target"
                     >
                       ×
                     </button>
                   </div>
                 </div>
               </div>
            </div>

            <!-- Target Configurations List -->
            <div v-if="targetConfigurations.length > 0" class="bg-green-50 p-4 rounded-lg">
              <h4 class="text-md font-medium text-gray-900 mb-3">Target Configurations to Update</h4>
              <div class="space-y-2 max-h-48 overflow-y-auto">
                <div v-for="config in targetConfigurations" :key="`${config.room_type_id}-${config.id}`" class="flex items-center">
                  <input
                    type="checkbox"
                    :id="`target-${config.room_type_id}-${config.id}`"
                    :value="config"
                    v-model="duplicateForm.selectedTargetConfigs"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                  >
                  <label :for="`target-${config.room_type_id}-${config.id}`" class="ml-2 text-sm text-gray-700">
                    {{ config.room_type_name }} ({{ config.room_type_capacity }} pax)
                    <span class="text-gray-500">- ID: {{ config.id }}</span>
                  </label>
                </div>
              </div>
            </div>

            <!-- Duplicate Options -->
            <div class="bg-yellow-50 p-4 rounded-lg">
              <label class="block text-sm font-medium text-gray-700 mb-2">Duplicate Options</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="duplicateForm.includeBaseCharges"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    checked
                  >
                  <span class="ml-2 text-sm text-gray-700">Include Base Charges</span>
                </label>
                <label class="flex items-center">
                  <input
                    type="checkbox"
                    v-model="duplicateForm.includeSurcharges"
                    class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                    checked
                  >
                  <span class="ml-2 text-sm text-gray-700">Include Surcharges</span>
                </label>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                @click="closeDuplicateModal"
                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400"
              >
                Cancel
              </button>
              <button
                @click="executeDuplication"
                :disabled="!canExecuteDuplication || duplicateLoading"
                class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span v-if="duplicateLoading">Duplicating...</span>
                <span v-else>Duplicate Configuration</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import axios from "axios";
import Swal from "sweetalert2";
import LoadingComponent from "@/Components/LoadingComponent.vue";
import { CopyOutlined } from "@ant-design/icons-vue";

const props = defineProps({
  packageId: { type: Number, required: true },
  seasonTypes: { type: Array, required: true },
  dateTypes: { type: Array, required: true },
  packageUniqueRoomTypes: { type: Object, required: true },
  allSeasonTypes: { type: Array, required: true },
  allDateTypes: { type: Array, required: true },
  assignedSeasonTypes: { type: Array, required: true },
  assignedDateTypes: { type: Array, required: true },
});

// filters / state
const selectedSeason = ref("");
const selectedDateType = ref("");
const selectedRoomType = ref("");
const isPriceLoading = ref(false);
const searched = ref(false);
const isEditMode = ref(false);
const saveLoading = ref(false);

// Duplicate functionality
const showDuplicateModal = ref(false);
const duplicateLoading = ref(false);
const sourceLoading = ref(false);
const targetLoading = ref(false);
const sourceConfigurations = ref([]);
const targetConfigurations = ref([]);
const duplicateForm = ref({
  // Source configuration
  sourceSeason: "",
  sourceDateType: "",
  sourceRoomType: "",
  selectedSourceConfig: null,
  
  // Target configuration
  targetConfigurations: [
    {
      season: "",
      dateType: "",
      roomType: ""
    }
  ],
  
  // Options
  includeBaseCharges: true,
  includeSurcharges: true
});
const targetLength = ref(1);
const addTargetConfiguration = () => {
  duplicateForm.value.targetConfigurations.push({
    season: "",
    dateType: "",
    roomType: ""
  });
  targetLength.value++;
};

const removeTargetConfiguration = (index) => {
  if (duplicateForm.value.targetConfigurations.length > 1) {
    duplicateForm.value.targetConfigurations.splice(index, 1);
    targetLength.value--;
  }
};

const duplicateToMultipleBtnClick = () => {
  useCurrentSearchAsSource();
  showDuplicateModal.value = true;
};

// compact rooms for UI: [{ room_type_id, id (package_config_id), base: {...}, surch: {...} }]
const compactRooms = ref([]);

// room names map (built from API; falls back to props.packageUniqueRoomTypes)
const roomNamesMap = ref({});

// computed helpers
const canSearch = computed(() => !!selectedSeason.value && !!selectedDateType.value);
const format = (n) => Number(n ?? 0).toFixed(2);

// Duplicate functionality computed properties
const canLoadSource = computed(() => {
  return duplicateForm.value.sourceSeason && duplicateForm.value.sourceDateType;
});

const canLoadTarget = computed(() => {
  return duplicateForm.value.targetSeason && duplicateForm.value.targetDateType;
});

const canUseCurrentSearch = computed(() => {
  return selectedSeason.value && selectedDateType.value && compactRooms.value.length > 0;
});

const canExecuteDuplication = computed(() => {
  // Check if source and target are the same
  const hasValidTargets = duplicateForm.value.targetConfigurations.some(target => 
    target.season && target.dateType
  );
  
  if (!hasValidTargets) {
    return false;
  }
  
  // Check if any target matches source exactly
  const sourceMatchesTarget = duplicateForm.value.targetConfigurations.some(target => 
    target.season === duplicateForm.value.sourceSeason &&
    target.dateType === duplicateForm.value.sourceDateType &&
    target.roomType === duplicateForm.value.sourceRoomType
  );
  
  if (sourceMatchesTarget) {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "Source and target cannot be the same",
    });
    return false;
  }

  return true;
});

// Generic object->entries helper
const entriesOf = (obj) => Object.entries(obj ?? {});

// ---- Combo helpers ---------------------------------------------------------
const comboPattern = /^(\d+)_a_(\d+)_c_(\d+)_i$/;
const parseCombo = (key) => {
  const m = key?.match?.(comboPattern);
  if (!m) return null;
  return { a: +m[1], c: +m[2], i: +m[3] };
};
const comboVerbose = (key) => {
  const p = parseCombo(key);
  if (!p) return key || "";
  const plural = (n, s, p2) => `${n} ${n === 1 ? s : p2 || s + "s"}`;
  return `${plural(p.a, "Adult")} + ${plural(p.c, "Child", "Children")} + ${plural(
    p.i,
    "Infant"
  )}`;
};
const sortComboKeys = (keys) => {
  return [...keys].sort((k1, k2) => {
    const p1 = parseCombo(k1) || { a: 0, c: 0, i: 0 };
    const p2 = parseCombo(k2) || { a: 0, c: 0, i: 0 };
    if (p1.a !== p2.a) return p2.a - p1.a;
    if (p1.c !== p2.c) return p2.c - p1.c;
    return p2.i - p1.i;
  });
};
const comboEntries = (obj) => {
  const keys = Object.keys(obj || {});
  const ordered = sortComboKeys(keys);
  return ordered.map((k) => [k, obj[k]]);
};

// ---- Base slot helpers -----------------------------------------------------
const isPlainSurchargeKey = (k) => k === "a" || k === "c" || k === "i";
const orderedSlotKeys = (obj) => {
  const keys = Object.keys(obj ?? {});
  const order = (k) => {
    const prefix = k[0]; // a/c/i
    const hasNum = !isPlainSurchargeKey(k);
    const num = hasNum ? parseInt(k.slice(1), 10) || 0 : -1;
    return ({ a: 1, c: 2, i: 3 }[prefix] || 9) * 100 + num;
  };
  return keys.sort((x, y) => order(x) - order(y));
};
const slotLabel = (key) => {
  if (!key) return "";
  const prefix = key[0];
  const num = key.slice(1);
  if (prefix === "a") return `Adult ${num}`;
  if (prefix === "c") return `Child ${num}`;
  if (prefix === "i") return `Infant ${num}`;
  return key;
};

const copyPriceTypeToSameRow = (room, comboKey, slotKey) => {
  if (!room || !comboKey || !slotKey) return;

  const row = room.base?.[comboKey];
  if (!row) return;

  const value = row[slotKey];
  if (value === undefined || value === null || value === "") return;

  // pax type prefix: 'a' | 'c' | 'i'
  const prefix = String(slotKey)[0];

  let affected = 0;
  Object.keys(row).forEach((k) => {
    if (k.startsWith(prefix)) {
      row[k] = Number(value); // normalize to number
      affected++;
    }
  });

};

const copySurchargeTypeToSameRow = (room, comboKey, slotKey) => {
  if (!room || !comboKey || !slotKey) return;

  const sourceRow = room.surch?.[comboKey];
  if (!sourceRow) return;

  const value = sourceRow[slotKey];
  if (value === undefined || value === null || value === "") return;

  // For surcharges, slotKey is just 'a', 'c', or 'i'
  // We need to copy this value to all rows' same person type column

  // Copy to all other rows in the same room
  Object.keys(room.surch).forEach((rowKey) => {
    const targetRow = room.surch[rowKey];
    if (targetRow && targetRow[slotKey] !== undefined) {
      targetRow[slotKey] = Number(value); // normalize to number
    }
  });

};

// Track last warning to prevent alert spam
let lastWarningKey = '';
let lastWarningTime = 0;

const checkSameCategorySamePrice = (room, comboKey, slotKey) => {
  const row = room.base?.[comboKey];
  if (!row) return;

  // Get the person type prefix (a/c/i)
  const prefix = String(slotKey)[0];

  // Get all slots of the same category in this row
  const sameCategorySlots = Object.entries(row).filter(([k]) => k.startsWith(prefix));

  // If only one slot of this category, no need to check
  if (sameCategorySlots.length <= 1) return;

  // Check if all same-category slots have identical prices
  const prices = sameCategorySlots.map(([, v]) => Number(v));
  const allSame = prices.every((p) => p === prices[0]);

  if (allSame && prices[0] !== 0 && prices[0] !== '' && !isNaN(prices[0])) {
    // Debounce: prevent showing same warning within 3 seconds
    const warningKey = `${room.room_type_id}-${comboKey}-${prefix}-${prices[0]}`;
    const now = Date.now();
    if (warningKey === lastWarningKey && now - lastWarningTime < 3000) {
      return;
    }
    lastWarningKey = warningKey;
    lastWarningTime = now;

    const categoryName = prefix === 'a' ? 'Adult' : prefix === 'c' ? 'Child' : 'Infant';
    Swal.fire({
      icon: "warning",
      title: "Same Price Detected",
      text: `All ${categoryName} prices in this row are the same (${prices[0]}). Is this intended?`,
      timer: 3000,
      showConfirmButton: true,
      confirmButtonText: 'OK',
    });
  }
};

// ---- Display helpers -------------------------------------------------------
const displayRoomName = (id) =>
  roomNamesMap.value[id] || props.packageUniqueRoomTypes?.[id] || `Room Type #${id}`;

const surchargeLabel = (k) =>
  k === "a" ? "Adult (per pax)" : k === "c" ? "Child (per pax)" : "Infant (per pax)";

// ---- Fetch & transform -----------------------------------------------------
const fetchPrices = () => {
  isPriceLoading.value = true;
  searched.value = true;
  isEditMode.value = false;

  axios
    .post(route("configuration-prices.fetchPricesRoomTypes"), {
      package_id: props.packageId,
      season_type_id: selectedSeason.value,
      date_type_id: selectedDateType.value,
      room_type_id: selectedRoomType.value || undefined,
    })
    .then((res) => {
      let rows = res?.data;

      // Support controller returning a raw JSON string during testing.
      if (typeof rows === "string") {
        const sanitized = rows.replace(/,\s*(?=[}\]])/g, "");
        try {
          rows = JSON.parse(sanitized);
        } catch (e) {
          console.error("Failed to parse API string after sanitizing:", e);
          rows = [];
        }
      }

      rows = Array.isArray(rows) ? rows : [];

      // Build room names map from response
      roomNamesMap.value = rows.reduce((acc, row) => {
        const id = row?.room_type_id ?? row?.room_type?.id;
        const nm = row?.room_type?.name;
        if (id && nm) acc[id] = nm;
        return acc;
      }, {});

      // Each row has prices[0] containing { base, surch }
      compactRooms.value = rows.map((row) => {
        const p0 = Array.isArray(row?.prices) && row.prices.length ? row.prices[0] : null;
        return {
          room_type_id: row?.room_type_id ?? row?.room_type?.id ?? 0,
          room_type_capacity: row?.room_type_capacity ?? 0,
          room_type_name: row?.room_type_name ?? '',
          id: row?.id ?? 0, // <-- package configuration id captured here
          base: p0?.base ?? {},
          surch: p0?.surch ?? {},
        };
      });
    })
    .catch((err) => {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: "Failed to fetch price configurations",
      });
      compactRooms.value = [];
    })
    .finally(() => {
      isPriceLoading.value = false;
    });
};

// ---- Reset / Edit / Save ---------------------------------------------------
const resetSearch = () => {
  selectedSeason.value = "";
  selectedDateType.value = "";
  selectedRoomType.value = "";
  compactRooms.value = [];
  roomNamesMap.value = {};
  searched.value = false;
  isEditMode.value = false;
};

const cancelEdit = () => {
  isEditMode.value = false;
  fetchPrices();
};

const saveAll = () => {
  saveLoading.value = true;

  // Map each room into the exact shape the API expects, including the config id.
  const roomsPayload = compactRooms.value.map((r) => ({
    package_configuration_id: Number(r.id) || null, // <-- INCLUDED HERE
    room_type_id: Number(r.room_type_id) || null,
    base: r.base ?? {},
    surch: r.surch ?? {},
  }));

  const payload = {
    package_id: props.packageId,
    season_type_id: selectedSeason.value,
    date_type_id: selectedDateType.value,
    rooms: roomsPayload,
  };

  axios
    .put(route("configuration-prices.updateRoomTypePrices"), payload)
    .then(() => {
      isEditMode.value = false;
      Swal.fire({
        icon: "success",
        title: "Saved",
        timer: 1200,
        showConfirmButton: false,
      });
      fetchPrices();
    })
    .catch((err) => {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Error",
        text: err.response?.data?.message || "Failed to save",
      });
    })
    .finally(() => {
      saveLoading.value = false;
    });
};

// ---- Duplicate functionality methods -----------------------------------------
const closeDuplicateModal = () => {
  showDuplicateModal.value = false;
  sourceConfigurations.value = [];
  targetConfigurations.value = [];
  duplicateForm.value = {
    // Source configuration
    sourceSeason: "",
    sourceDateType: "",
    sourceRoomType: "",
    selectedSourceConfig: null,
    
    // Target configuration
    targetConfigurations: [
      {
        season: "",
        dateType: "",
        roomType: ""
      }
    ],
    
    // Options
    includeBaseCharges: true,
    includeSurcharges: true
  };
  targetLength.value = 1;
};

const getLargestIndexNumber = (data) => {
  let max = 0;

  Object.values(data ?? {}).forEach(group => {
    Object.keys(group ?? {}).forEach(key => {
      const num = parseInt(key.slice(1), 10);
      if (!isNaN(num)) {
        max = Math.max(max, num);
      }
    });
  });

  return max + 1;
};

const loadSourceConfigurations = async () => {
  if (!canLoadSource.value) return;
  
  sourceLoading.value = true;
  sourceConfigurations.value = [];
  
  try {
    const response = await axios.post(route("configuration-prices.fetchPricesRoomTypes"), {
      package_id: props.packageId,
      season_type_id: duplicateForm.value.sourceSeason,
      date_type_id: duplicateForm.value.sourceDateType,
      room_type_id: duplicateForm.value.sourceRoomType || undefined,
    });
    
    let rows = response?.data;
    
    if (typeof rows === "string") {
      const sanitized = rows.replace(/,\s*(?=[}\]])/g, "");
      try {
        rows = JSON.parse(sanitized);
      } catch (e) {
        console.error("Failed to parse API string after sanitizing:", e);
        rows = [];
      }
    }
    
    rows = Array.isArray(rows) ? rows : [];
    
    sourceConfigurations.value = rows.map((row) => ({
      id: row?.id ?? 0,
      room_type_id: row?.room_type_id ?? row?.room_type?.id ?? 0,
      room_type_capacity: row?.room_type_capacity ?? 0,
      room_type_name: row?.room_type_name ?? '',
      base: row?.prices?.[0]?.base ?? {},
      surch: row?.prices?.[0]?.surch ?? {},
    }));
    
  } catch (error) {
    console.error("Error loading source configurations:", error);
    await Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load source configurations",
    });
  } finally {
    sourceLoading.value = false;
  }
};

const useCurrentSearchAsSource = () => {
  // Set the source form to match current search
  duplicateForm.value.sourceSeason = selectedSeason.value;
  duplicateForm.value.sourceDateType = selectedDateType.value;
  duplicateForm.value.sourceRoomType = selectedRoomType.value;
  
  // Use the current compactRooms as source configurations
  sourceConfigurations.value = compactRooms.value.map((room) => ({
    id: room.id,
    room_type_id: room.room_type_id,
    room_type_capacity: room.room_type_capacity,
    room_type_name: room.room_type_name,
    base: room.base,
    surch: room.surch,
  }));
  
  // Auto-select the first configuration if only one exists
  if (sourceConfigurations.value.length === 1) {
    duplicateForm.value.selectedSourceConfig = sourceConfigurations.value[0];
  }
};

const loadTargetConfigurations = async () => {
  if (!canLoadTarget.value) return;
  
  targetLoading.value = true;
  targetConfigurations.value = [];
  
  try {
    const response = await axios.post(route("configuration-prices.fetchPricesRoomTypes"), {
      package_id: props.packageId,
      season_type_id: duplicateForm.value.targetSeason,
      date_type_id: duplicateForm.value.targetDateType,
      room_type_id: duplicateForm.value.targetRoomType || undefined,
    });
    
    let rows = response?.data;
    
    if (typeof rows === "string") {
      const sanitized = rows.replace(/,\s*(?=[}\]])/g, "");
      try {
        rows = JSON.parse(sanitized);
      } catch (e) {
        console.error("Failed to parse API string after sanitizing:", e);
        rows = [];
      }
    }
    
    rows = Array.isArray(rows) ? rows : [];
    
    targetConfigurations.value = rows.map((row) => ({
      id: row?.id ?? 0,
      room_type_id: row?.room_type_id ?? row?.room_type?.id ?? 0,
      room_type_capacity: row?.room_type_capacity ?? 0,
      room_type_name: row?.room_type_name ?? '',
      base: row?.prices?.[0]?.base ?? {},
      surch: row?.prices?.[0]?.surch ?? {},
    }));
    
  } catch (error) {
    console.error("Error loading target configurations:", error);
    await Swal.fire({
      icon: "error",
      title: "Error",
      text: "Failed to load target configurations",
    });
  } finally {
    targetLoading.value = false;
  }
};

const executeDuplication = async () => {
  if (!canExecuteDuplication.value) return;
  
  duplicateLoading.value = true;
  
  try {
    // Filter out empty target configurations
    const validTargets = duplicateForm.value.targetConfigurations.filter(target => 
      target.season && target.dateType
    );
    
    // Prepare the duplication payload
    const payload = {
      package_id: props.packageId,
      // source
      source_season_type_id: duplicateForm.value.sourceSeason,
      source_date_type_id: duplicateForm.value.sourceDateType,
      source_room_type_id: duplicateForm.value.sourceRoomType,
      // target configurations
      target_configurations: validTargets.map(target => ({
        season_type_id: target.season,
        date_type_id: target.dateType,
        room_type_id: target.roomType
      })),
      // includes
      include_base_charges: duplicateForm.value.includeBaseCharges,
      include_surcharges: duplicateForm.value.includeSurcharges,
    };

    // Call the duplication API
    await axios.post(route("configuration-prices.duplicateToMultiple"), payload);
    
    // Show success message
    await Swal.fire({
      icon: "success",
      title: "Duplication Successful",
      text: `Price configuration duplicated to ${validTargets.length} target configuration(s)`,
      timer: 2000,
      showConfirmButton: false,
    });
    
    // Close modal
    closeDuplicateModal();
    
  } catch (error) {
    console.error("Duplication error:", error);
    await Swal.fire({
      icon: "error",
      title: "Duplication Failed",
      text: error.response?.data?.message || "Failed to duplicate price configuration",
    });
  } finally {
    duplicateLoading.value = false;
  }
};
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Hide number input arrows in Chrome, Safari, Edge, Opera */
input[type="number"]::-webkit-inner-spin-button,
input[type="number"]::-webkit-outer-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

/* Hide number input arrows in Firefox */
input[type="number"] {
  -moz-appearance: textfield;
}

</style>
