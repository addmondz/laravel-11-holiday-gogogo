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
            <button
              v-if="!isEditMode"
              @click="isEditMode = true"
              class="px-5 py-2 rounded bg-indigo-600 text-white text-sm"
            >
              Edit All Prices
            </button>
            <template v-else>
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
          </div>

          <!-- BASE TABLE -->
          <section style="margin-top: 10px">
            <div class="overflow-x-auto border rounded-lg">
              <table class="min-w-full text-sm">
                <thead class="bg-gray-200">
                  <tr>
                    <th
                      class="px-4 py-2 text-left"
                      :colspan="orderedSlotKeys(room.base).length"
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
                            />
                            <button
                              type="button"
                              class="flex items-center justify-center px-3 border-l border-gray-300 text-gray-500 hover:text-indigo-600 hover:bg-gray-50 rounded-r-md"
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
            style="margin-top: 15px"
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
                          <input
                            type="number"
                            step="0.01"
                            class="w-24 rounded-md border-gray-300 focus:ring-indigo-500 focus:border-indigo-500"
                            v-model.number="room.surch[comboKey][k]"
                          />
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

// compact rooms for UI: [{ room_type_id, id (package_config_id), base: {...}, surch: {...} }]
const compactRooms = ref([]);

// room names map (built from API; falls back to props.packageUniqueRoomTypes)
const roomNamesMap = ref({});

// computed helpers
const canSearch = computed(() => !!selectedSeason.value && !!selectedDateType.value);
const format = (n) => Number(n ?? 0).toFixed(2);

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

  // tiny toast to confirm
  try {
    Swal.fire({
      toast: true,
      timer: 1200,
      showConfirmButton: false,
      icon: "success",
      title: `Copied to ${affected} ${prefix === 'a' ? 'Adult' : prefix === 'c' ? 'Child' : 'Infant'} slot(s)`,
    });
  } catch (_) {
    // Swal might not be available in some contexts; ignore
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
</style>
