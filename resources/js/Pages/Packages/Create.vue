<template>
    <Head title="Create Package" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            </h2>
        </template>

        <div class="pb-12 pt-6">
            <div class="max-w-9xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <BreadcrumbComponent :breadcrumbs="breadcrumbs" class="mb-9" />
                        
                        <form @submit.prevent="submit">
                            <div class="grid grid-cols-1 gap-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        v-model="form.name"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.name }"
                                        required
                                    />
                                    <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                    <textarea
                                        id="description"
                                        v-model="form.description"
                                        rows="3"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.description }"
                                    ></textarea>
                                    <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.description }}
                                    </div>
                                </div>

                                <!-- Images Section -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Package Images</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md">
                                        <div class="space-y-1 text-center">
                                            <!-- Display uploaded images -->
                                            <div v-if="form.images && form.images.length > 0" class="flex gap-4 mb-4">
                                                <div v-for="(image, index) in imagePreviews" :key="index" class="relative group">
                                                    <img :src="image" class="h-24 w-full object-cover rounded-lg" />
                                                    <button
                                                        type="button"
                                                        @click="removeImage(index)"
                                                        class="absolute top-0 right-0 bg-red-500 text-white rounded-full p-1 text-xs opacity-0 group-hover:opacity-100 transition-opacity"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="text-sm text-gray-600">
                                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                    <span>Upload Images</span>
                                                    <input
                                                        type="file"
                                                        @change="handleImagesUpload"
                                                        accept="image/*"
                                                        multiple
                                                        class="sr-only"
                                                    />
                                                </label>
                                            </div>
                                            <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                                        </div>
                                    </div>
                                    <div v-if="form.errors.images" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.images }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-3 gap-6">
                                    <div>
                                        <!-- <label for="display_price_adult" class="block text-sm font-medium text-gray-700"><b class="text-[15px]">Display Price</b> / Adult Base Price</label> -->
                                        <label for="display_price_adult" class="block text-sm font-medium text-gray-700">Display Price</label>
                                        <input
                                            type="number"
                                            id="display_price_adult"
                                            v-model="form.display_price_adult"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.display_price_adult }"
                                        />
                                        <div v-if="form.errors.display_price_adult" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.display_price_adult }}
                                        </div>
                                    </div>
                                    <!-- <div>
                                        <label for="display_price_child" class="block text-sm font-medium text-gray-700">Child Base Price</label>
                                        <input
                                            type="number"
                                            id="display_price_child"
                                            v-model="form.display_price_child"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.display_price_child }"
                                        />
                                        <div v-if="form.errors.display_price_child" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.display_price_child }}
                                        </div>
                                    </div> -->
                                    <!-- <div>
                                        <label for="display_price_infant" class="block text-sm font-medium text-gray-700">Infant Base Price</label>
                                        <input
                                            type="number"
                                            id="display_price_infant"
                                            v-model="form.display_price_infant"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.display_price_infant }"
                                        />
                                        <div v-if="form.errors.display_price_infant" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.display_price_infant }}
                                        </div>
                                    </div> -->
                                    <!-- <div>
                                        <label for="adult_surcharge" class="block text-sm font-medium text-gray-700">Adult Surcharge</label>
                                        <input
                                            type="number"
                                            id="adult_surcharge"
                                            v-model="form.adult_surcharge"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.adult_surcharge }"
                                        />
                                        <div v-if="form.errors.adult_surcharge" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.adult_surcharge }}
                                        </div>
                                    </div> -->
                                    <!-- <div>
                                        <label for="child_surcharge" class="block text-sm font-medium text-gray-700">Child Surcharge</label>
                                        <input
                                            type="number"
                                            id="child_surcharge"
                                            v-model="form.child_surcharge"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.child_surcharge }"
                                        />
                                        <div v-if="form.errors.child_surcharge" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.child_surcharge }}
                                        </div>
                                    </div> -->
                                    <!-- <div>
                                        <label for="infant_surcharge" class="block text-sm font-medium text-gray-700">Default Infant Surcharge</label>
                                        <input
                                            type="number"
                                            id="infant_surcharge"
                                            v-model="form.infant_surcharge"
                                            step="0.01"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.infant_surcharge }"
                                        />
                                        <div v-if="form.errors.infant_surcharge" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.infant_surcharge }}
                                        </div>
                                    </div> -->
                                </div>
                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="infant_max_age_desc" class="block text-sm font-medium text-gray-700">Infant Age Description</label>
                                        <input
                                            type="text"
                                            id="infant_max_age_desc"
                                            v-model="form.infant_max_age_desc"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.infant_max_age_desc }"
                                            placeholder="e.g. 0 - 11 months old"
                                        />
                                        <div v-if="form.errors.infant_max_age_desc" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.infant_max_age_desc }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="child_max_age_desc" class="block text-sm font-medium text-gray-700">Child Age Description</label>
                                        <input
                                            type="text"
                                            id="child_max_age_desc"
                                            v-model="form.child_max_age_desc"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.child_max_age_desc }"
                                            placeholder="e.g. 1 - 12 years old"
                                        />
                                        <div v-if="form.errors.child_max_age_desc" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.child_max_age_desc }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="package_days" class="block text-sm font-medium text-gray-700">Package Duration (Nights)</label>
                                        <input
                                            type="number"
                                            id="package_days"
                                            v-model="form.package_days"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.package_days }"
                                            required
                                        />
                                        <div v-if="form.errors.package_days" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_days }}
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Weekend Days</label>
                                    <div class="grid grid-cols-7 gap-2">
                                        <div v-for="(day, index) in weekDays" :key="index" class="flex items-center">
                                            <input
                                                type="checkbox"
                                                :id="'day-' + index"
                                                :value="index"
                                                v-model="form.weekend_days"
                                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                                            />
                                            <label :for="'day-' + index" class="ml-2 text-sm text-gray-700">
                                                {{ day }}
                                            </label>
                                        </div>
                                    </div>
                                    <p class="mt-1 text-sm text-gray-500">Select which days are considered weekends for pricing purposes.</p>
                                    <div v-if="form.errors.weekend_days" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.weekend_days }}
                                    </div>
                                </div>

                                <div>
                                    <label for="no_children_and_infant" class="block text-sm font-medium text-gray-700">No Children and Infant</label>
                                    <input
                                        type="checkbox"
                                        id="no_children_and_infant"
                                        v-model="form.no_children_and_infant"
                                        class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    />
                                    <span class="text-xs text-gray-500">
                                        If checked, children and infant input fields will be hidden on the quotation page.
                                    </span>
                                    <div v-if="form.errors.no_children_and_infant" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.no_children_and_infant }}
                                    </div>
                                </div>

                                <div>
                                    <label for="terms_and_conditions" class="block text-sm font-medium text-gray-700">
                                        Terms and Conditions
                                        <span class="text-xs text-gray-500 font-normal ml-2">(Enter each point on a new line)</span>
                                    </label>
                                    <textarea
                                        id="terms_and_conditions"
                                        v-model="form.terms_and_conditions"
                                        rows="8"
                                        placeholder="Enter each term or condition on a new line. They will be displayed as bullet points on the quotation page."
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.terms_and_conditions }"
                                    ></textarea>
                                    <p class="mt-1 text-xs text-gray-500">
                                        Each line will be displayed as a bullet point. Use line breaks to separate different terms.
                                    </p>
                                    <div v-if="form.errors.terms_and_conditions" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.terms_and_conditions }}
                                    </div>
                                    
                                    <!-- Preview Section -->
                                    <div v-if="form.terms_and_conditions" class="mt-4 p-4 bg-gray-50 rounded-md border border-gray-200">
                                        <p class="text-xs font-medium text-gray-700 mb-2">Preview:</p>
                                        <div class="text-sm text-gray-600" v-html="formatTermsAndConditions(form.terms_and_conditions)"></div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Extra Remark</label>
                                    <div class="quill-container">
                                        <QuillEditor
                                            v-model:content="form.extra_remark"
                                            contentType="html"
                                            theme="snow"
                                            toolbar="essential"
                                        />
                                    </div>
                                    <div v-if="form.errors.extra_remark" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.extra_remark }}
                                    </div>
                                </div>

                                <div>
                                    <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                                    <input
                                        type="text"
                                        id="location"
                                        v-model="form.location"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.location }"
                                    />
                                    <div v-if="form.errors.location" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.location }}
                                    </div>
                                </div>

                                <div>
                                    <label for="wordpress_link" class="block text-sm font-medium text-gray-700">WordPress Link</label>
                                    <input
                                        type="url"
                                        id="wordpress_link"
                                        v-model="form.wordpress_link"
                                        placeholder="https://example.com/package"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        :class="{ 'border-red-500': form.errors.wordpress_link }"
                                    />
                                    <p class="mt-1 text-xs text-gray-500">
                                        Enter the full URL to the WordPress page for this package.
                                    </p>
                                    <div v-if="form.errors.wordpress_link" class="mt-1 text-sm text-red-600">
                                        {{ form.errors.wordpress_link }}
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-6">
                                    <div>
                                        <label for="package_start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                                        <input
                                            type="date"
                                            id="package_start_date"
                                            v-model="form.package_start_date"
                                            :max="form.package_end_date ? form.package_end_date : undefined"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.package_start_date || form.errors.package_end_date }"
                                            required
                                            @change="validateDates"
                                        />
                                        <div v-if="form.errors.package_start_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_start_date }}
                                        </div>
                                    </div>
                                    <div>
                                        <label for="package_end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                        <input
                                            type="date"
                                            id="package_end_date"
                                            v-model="form.package_end_date"
                                            :min="form.package_start_date ? new Date(new Date(form.package_start_date).getTime() + 86400000).toISOString().split('T')[0] : undefined"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            :class="{ 'border-red-500': form.errors.package_end_date }"
                                            required
                                        />
                                        <div v-if="form.errors.package_end_date" class="mt-1 text-sm text-red-600">
                                            {{ form.errors.package_end_date }}
                                        </div>
                                        <div v-if="dateError" class="mt-1 text-sm text-red-600">
                                            {{ dateError }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Room Types Section -->
                                <div class="mt-6 border-t border-gray-600 pt-6">
                                    <div class="flex justify-between items-center mb-4">
                                        <h3 class="text-lg font-medium text-gray-900">Room Types</h3>
                                        <button
                                            type="button"
                                            @click="addRoomType"
                                            class="px-4 py-2 text-xs bg-indigo-100 text-indigo-700 rounded-md hover:bg-indigo-200"
                                        >
                                            Add Room Type
                                        </button>
                                    </div>

                                    <!-- Room Types General Error -->
                                    <div v-if="form.errors.room_types" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-md">
                                        <div class="text-sm text-red-600">
                                            {{ form.errors.room_types }}
                                        </div>
                                    </div>

                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-gray-200">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Room Type</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Occupancy</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Adults</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Children</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Max Infants</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody class="bg-white divide-y divide-gray-200">
                                                <tr v-for="(roomType, index) in form.room_types" :key="index">
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="text"
                                                            v-model="roomType.name"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            :class="{ 'border-red-500': form.errors[`room_types.${index}.name`] }"
                                                            placeholder="Room Type Name"
                                                            required
                                                        />
                                                        <div v-if="form.errors[`room_types.${index}.name`]" class="mt-1 text-sm text-red-600">
                                                            {{ form.errors[`room_types.${index}.name`] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            v-model="roomType.max_occupancy"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            :class="{ 'border-red-500': form.errors[`room_types.${index}.max_occupancy`] }"
                                                            min="1"
                                                            required
                                                        />
                                                        <div v-if="form.errors[`room_types.${index}.max_occupancy`]" class="mt-1 text-sm text-red-600">
                                                            {{ form.errors[`room_types.${index}.max_occupancy`] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            v-model="roomType.max_adults"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            :class="{ 'border-red-500': form.errors[`room_types.${index}.max_adults`] }"
                                                            min="1"
                                                        />
                                                        <div v-if="form.errors[`room_types.${index}.max_adults`]" class="mt-1 text-sm text-red-600">
                                                            {{ form.errors[`room_types.${index}.max_adults`] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            v-model="roomType.max_children"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            :class="{ 'border-red-500': form.errors[`room_types.${index}.max_children`] }"
                                                            min="1"
                                                        />
                                                        <div v-if="form.errors[`room_types.${index}.max_children`]" class="mt-1 text-sm text-red-600">
                                                            {{ form.errors[`room_types.${index}.max_children`] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap">
                                                        <input
                                                            type="number"
                                                            v-model="roomType.max_infants"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            :class="{ 'border-red-500': form.errors[`room_types.${index}.max_infants`] }"
                                                            min="1"
                                                        />
                                                        <div v-if="form.errors[`room_types.${index}.max_infants`]" class="mt-1 text-sm text-red-600">
                                                            {{ form.errors[`room_types.${index}.max_infants`] }}
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <input
                                                            type="text"
                                                            v-model="roomType.description"
                                                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                            placeholder="Description"
                                                        />
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <div class="space-y-2">
                                                            <!-- Image Preview -->
                                                            <div v-if="roomType.imagePreviews?.length" class="flex flex-wrap gap-2">
                                                                <div v-for="(preview, previewIndex) in roomType.imagePreviews" :key="previewIndex" class="relative">
                                                                    <img :src="preview" class="h-20 w-20 object-cover rounded" />
                                                                    <button
                                                                        type="button"
                                                                        @click="removeRoomTypeImage(index, previewIndex)"
                                                                        class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600"
                                                                    >
                                                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                                        </svg>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                            <!-- Upload Button -->
                                                            <div class="flex justify-center border-2 border-gray-300 border-dashed rounded-md">
                                                                <div class="space-y-1 text-center">
                                                                    <div class="text-sm text-gray-600">
                                                                        <label :for="'room-type-images-' + index" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                                                                            <span>Upload images</span>
                                                                            <input
                                                                                :id="'room-type-images-' + index"
                                                                                type="file"
                                                                                multiple
                                                                                accept="image/*"
                                                                                class="sr-only"
                                                                                @change="handleRoomTypeImagesUpload($event, index)"
                                                                            />
                                                                        </label>
                                                                        <p class="pl-1">or drag and drop</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                        <button
                                                            type="button"
                                                            @click="removeRoomType(index)"
                                                            class="text-red-600 hover:text-red-900"
                                                        >
                                                            Remove
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-6 flex justify-end">
                                <Link
                                    :href="route('packages.index')"
                                    class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 mr-3 text-xs"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700 text-xs"
                                    :disabled="form.processing"
                                >
                                    Create Package
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Swal from 'sweetalert2';
import { Head } from '@inertiajs/vue3';
import BreadcrumbComponent from '@/Components/BreadcrumbComponent.vue';
import { computed, ref, onUnmounted } from 'vue';
import { QuillEditor } from '@vueup/vue-quill';
import '@vueup/vue-quill/dist/vue-quill.snow.css';

const form = useForm({
    name: '',
    description: '',
    images: [],
    display_price_adult: null,
    display_price_child: 0,
    display_price_infant: 0,
    adult_surcharge: 0,
    child_surcharge: 0,
    infant_surcharge: 0,
    infant_max_age_desc: '',
    child_max_age_desc: '',
    package_days: 2,
    weekend_days: [0, 6], // Default to Saturday and Sunday
    terms_and_conditions: '',
    extra_remark: '',
    location: '',
    no_children_and_infant: false,
    wordpress_link: '',
    package_start_date: '',
    package_end_date: '',
    room_types: [{
        name: '',
        max_occupancy: 2,
        max_adults: null,
        max_children: null,
        max_infants: null,
        description: '',
        images: [],
        imagePreviews: []
    }]
});

const dateError = ref('');
const imagePreviews = ref([]);
const weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const addRoomType = () => {
    form.room_types.push({
        name: '',
        max_occupancy: 2,
        max_adults: null,
        max_children: null,
        max_infants: null,
        description: '',
        images: [],
        imagePreviews: []
    });
};

const removeRoomType = (index) => {
    form.room_types.splice(index, 1);
};

const handleImagesUpload = (event) => {
    const files = Array.from(event.target.files);
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const errors = [];

    files.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            errors.push(`${file.name} is not a valid image file. Only JPG, PNG, and GIF are allowed.`);
        }
        if (file.size > maxSize) {
            errors.push(`${file.name} is too large. Maximum file size is 10MB.`);
        }
    });

    if (errors.length > 0) {
        Swal.fire({
            title: 'Invalid Images',
            html: errors.join('<br>'),
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    form.images = [...form.images, ...files];
    files.forEach(file => {
        imagePreviews.value.push(URL.createObjectURL(file));
    });
};

const removeImage = (index) => {
    form.images.splice(index, 1);
    URL.revokeObjectURL(imagePreviews.value[index]);
    imagePreviews.value.splice(index, 1);
};

const handleRoomTypeImagesUpload = (event, index) => {
    const files = Array.from(event.target.files);
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const errors = [];

    files.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            errors.push(`${file.name} is not a valid image file. Only JPG, PNG, and GIF are allowed.`);
        }
        if (file.size > maxSize) {
            errors.push(`${file.name} is too large. Maximum file size is 10MB.`);
        }
    });

    if (errors.length > 0) {
        Swal.fire({
            title: 'Invalid Images',
            html: errors.join('<br>'),
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Add files to the room type's images array
    form.room_types[index].images = [...(form.room_types[index].images || []), ...files];
    
    // Create preview URLs
    files.forEach(file => {
        if (!form.room_types[index].imagePreviews) {
            form.room_types[index].imagePreviews = [];
        }
        form.room_types[index].imagePreviews.push(URL.createObjectURL(file));
    });
};

const removeRoomTypeImage = (roomTypeIndex, imageIndex) => {
    // Remove the image from the array
    form.room_types[roomTypeIndex].images.splice(imageIndex, 1);
    
    // Revoke the preview URL to prevent memory leaks
    URL.revokeObjectURL(form.room_types[roomTypeIndex].imagePreviews[imageIndex]);
    form.room_types[roomTypeIndex].imagePreviews.splice(imageIndex, 1);
};

// Format terms and conditions as bullet points (same as quotation page)
const linkify = (text) => {
    if (!text) return "";
    const urlPattern = /(https?:\/\/[^\s]+)/g;
    return text.replace(urlPattern, '<a href="$1" target="_blank" class="text-blue-600 underline">$1</a>');
};

const formatTermsAndConditions = (text) => {
    if (!text) return "";
    
    // Split by newlines and filter out empty lines
    const lines = text.split(/\r?\n/).filter(line => line.trim().length > 0);
    
    if (lines.length === 0) return "";
    
    // If only one line or no newlines, return as paragraph with linkify
    if (lines.length === 1) {
        return `<p>${linkify(lines[0])}</p>`;
    }
    
    // Convert to bullet points
    const bulletPoints = lines.map(line => {
        const linkedLine = linkify(line.trim());
        return `<li class="mb-1">${linkedLine}</li>`;
    }).join('');
    
    return `<ul class="list-disc list-inside space-y-1">${bulletPoints}</ul>`;
};

// Clean up preview URLs when component is unmounted
onUnmounted(() => {
    form.room_types.forEach(roomType => {
        if (roomType.imagePreviews) {
            roomType.imagePreviews.forEach(url => URL.revokeObjectURL(url));
        }
    });
    imagePreviews.value.forEach(url => URL.revokeObjectURL(url));
});

const validateDates = () => {
    dateError.value = '';
    if (form.package_start_date && form.package_end_date) {
        const startDate = new Date(form.package_start_date);
        const endDate = new Date(form.package_end_date);
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (endDate <= startDate) {
            dateError.value = 'End date must be after start date';
        } else if (diffDays < 1) {
            dateError.value = 'End date must be at least one day after start date';
        }
    }
};

const submit = () => {
    // Validate images before submission
    const maxSize = 10 * 1024 * 1024; // 10MB in bytes
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    const imageErrors = [];

    form.images.forEach(file => {
        if (!allowedTypes.includes(file.type)) {
            imageErrors.push(`${file.name} is not a valid image file. Only JPG, PNG, and GIF are allowed.`);
        }
        if (file.size > maxSize) {
            imageErrors.push(`${file.name} is too large. Maximum file size is 10MB.`);
        }
    });

    if (imageErrors.length > 0) {
        Swal.fire({
            title: 'Invalid Images',
            html: imageErrors.join('<br>'),
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Validate dates before submission
    validateDates();
    if (dateError.value) {
        Swal.fire({
            title: 'Validation Error',
            text: dateError.value,
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Validate room types before submission
    const roomTypeErrors = [];
    form.room_types.forEach((roomType, index) => {
        if (!roomType.name?.trim()) {
            roomTypeErrors.push(`Room type ${index + 1}: Name is required`);
        }
        if (!roomType.max_occupancy || roomType.max_occupancy < 1) {
            roomTypeErrors.push(`Room type ${index + 1}: Maximum occupancy must be at least 1`);
        }
        
        // Validate room type images
        if (roomType.images) {
            roomType.images.forEach((file, fileIndex) => {
                if (!allowedTypes.includes(file.type)) {
                    roomTypeErrors.push(`Room type ${index + 1}, Image ${fileIndex + 1}: Invalid file type. Only JPG, PNG, and GIF are allowed.`);
                }
                if (file.size > maxSize) {
                    roomTypeErrors.push(`Room type ${index + 1}, Image ${fileIndex + 1}: File too large. Maximum size is 10MB.`);
                }
            });
        }
    });

    if (roomTypeErrors.length > 0) {
        Swal.fire({
            title: 'Validation Error',
            html: roomTypeErrors.join('<br>'),
            icon: 'error',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Use Inertia's standard form submission to properly handle validation errors
    form.post(route('packages.store'), {
        onSuccess: () => {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Package created successfully'
            });
        },
        onError: (errors) => {
            console.log('Validation errors:', errors);
            // The errors will automatically be bound to form.errors
            // No need to manually handle them here as they'll show in the template
        }
    });
};

const breadcrumbs = computed(() => [
    { title: 'Packages', link: route('packages.index') },
	{ title: 'Create Package', },
]);
</script>

<style scoped>
.quill-container {
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
}

.quill-container :deep(.ql-toolbar) {
    border: none;
    border-bottom: 1px solid #d1d5db;
}

.quill-container :deep(.ql-container) {
    border: none;
    min-height: 150px;
}

.quill-container :deep(.ql-editor) {
    min-height: 150px;
}
</style>
