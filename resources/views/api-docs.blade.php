<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bot API Documentation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <style>
        .method-badge {
            @apply px-2 py-1 text-xs font-bold rounded;
        }

        .method-post {
            @apply bg-green-100 text-green-800;
        }

        .method-get {
            @apply bg-blue-100 text-blue-800;
        }

        .method-put {
            @apply bg-yellow-100 text-yellow-800;
        }

        .method-delete {
            @apply bg-red-100 text-red-800;
        }

        .endpoint-card {
            @apply bg-white rounded-lg shadow-md border border-gray-200 mb-6 overflow-hidden;
        }

        .endpoint-header {
            @apply bg-gray-50 px-6 py-4 border-b border-gray-200;
        }

        .endpoint-body {
            @apply p-6;
        }

        .code-block {
            @apply bg-gray-900 text-gray-100 rounded-lg p-4 overflow-x-auto;
        }

        .nav-link {
            @apply block px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors;
        }

        .nav-link.active {
            @apply bg-blue-100 text-blue-700 font-medium;
        }
    </style>
</head>

<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-900 mb-4">Bot API Documentation</h1>
            <p class="text-xl text-gray-600">Complete guide for chatbot integration endpoints</p>
            <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                <p class="text-blue-800 font-medium">Base URL: <code class="bg-blue-100 px-2 py-1 rounded">{{ $baseUrl }}/{{ $botPrefix }}</code></p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Navigation Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Navigation</h2>
                    <nav class="flex flex-col gap-2">
                        <a href="#fetch-room-types" class="nav-link">API 1 - Fetch Room Types</a>
                        <a href="#fetch-quotation" class="nav-link">API 2 - Fetch Quotation</a>
                        <a href="#error-responses" class="nav-link">Error Responses</a>
                        <a href="#notes" class="nav-link hidden">Important Notes</a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:w-3/4">
                <!-- Fetch Room Types Section -->
                <section id="fetch-room-types" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">1. Fetch Room Types by Package Name</h2>

                    <div class="endpoint-card">
                        <div class="endpoint-header">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-post">POST</span>
                                <code class="text-lg font-mono">{{ $baseUrl }}/{{ $botPrefix }}/fetch-room-types</code>
                            </div>
                            <p class="text-gray-600 mt-2">Get available room types, package information, and date blockers for a specific package.</p>
                        </div>

                        <div class="endpoint-body">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Body</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "package_name": "Langkawi Island Resort"
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Validation Rules</h3>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h4 class="font-semibold text-yellow-800 mb-2">Required Fields</h4>
                                <ul class="space-y-1 text-sm text-yellow-700">
                                    <li><strong>package_name:</strong> Required string, maximum 255 characters</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "success": true,
    "data": {
        "package": {
            "id": 1,
            "uuid": "25JIBG51603",
            "booking_page_url": "https://example.com/quotation/25JIBG51603",
            "images": [
                "https://example.com/images/package1.jpg",
                "https://example.com/images/package2.jpg"
            ],
            "package_id": "PKG001",
            "name": "Langkawi Island Resort",
            "description": "Beautiful island resort package with stunning sea views",
            "location": "Langkawi, Malaysia",
            "package_nights": 3,
            "package_start_date": "2024-01-01",
            "package_end_date": "2024-12-31",
            "child_max_age_desc": "Children under 12 years",
            "infant_max_age_desc": "Infants under 2 years",
            "package_display_price": 150.00,
            "room_types": [
                {
                    "id": 1,
                    "name": "Deluxe Room",
                    "description": "Spacious room with sea view and modern amenities",
                    "max_occupancy": 4,
                    "images": [
                        "https://example.com/images/deluxe-room1.jpg",
                        "https://example.com/images/deluxe-room2.jpg"
                    ],
                    "date_blockers": [
                        {
                            "start_date": "2024-01-20",
                            "end_date": "2024-01-25"
                        },
                        {
                            "start_date": "2024-02-15",
                            "end_date": "2024-02-20"
                        }
                    ]
                },
                {
                    "id": 2,
                    "name": "Suite Room",
                    "description": "Luxury suite with separate living area",
                    "max_occupancy": 6,
                    "images": [
                        "https://example.com/images/suite-room1.jpg"
                    ],
                    "date_blockers": []
                }
            ]
        }
    }
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response Fields</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm border border-gray-300 rounded-lg">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Field</th>
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Type</th>
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <!-- Package Information -->
                                            <tr class="bg-blue-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-blue-900">Package Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">id</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Internal package ID</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">uuid</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Unique identifier (UUID)</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">booking_page_url</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Direct link to booking page</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">images</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of package image URLs</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">package_id</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Package reference code</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">name</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Package name</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">description</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Package description</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">location</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Package location</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">package_nights</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Minimum nights required</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">package_start_date</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Start date of the package</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">package_end_date</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">End date of the package</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">child_max_age_desc</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Child age policy description</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">infant_max_age_desc</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Infant age policy description</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">package_display_price</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Base display price for adults</td>
                                            </tr>
                                            
                                            <!-- Room Type Information -->
                                            <tr class="bg-green-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-green-900">Room Type Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].id</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Room type ID</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].name</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Room type name</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].description</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Room type description</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].max_occupancy</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Maximum number of guests</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].images</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of room type image URLs</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].date_blockers</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of blocked date ranges</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].date_blockers[].start_date</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Start date of blocked period (YYYY-MM-DD)</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">room_types[].date_blockers[].end_date</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">End date of blocked period (YYYY-MM-DD)</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Error Responses</h3>
                            <div class="space-y-4">
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-800 mb-2">Validation Error (400)</h4>
                                    <div class="code-block">
                                        <pre><code class="language-json">{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "package_id": ["The package id field is required."]
    }
}</code></pre>
                                    </div>
                                </div>

                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-800 mb-2">Package Not Found (404)</h4>
                                    <div class="code-block">
                                        <pre><code class="language-json">{
    "success": false,
    "message": "Package not found"
}</code></pre>
                                    </div>
                                </div>

                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="font-semibold text-red-800 mb-2">Server Error (500)</h4>
                                    <div class="code-block">
                                        <pre><code class="language-json">{
    "success": false,
    "message": "Server error",
    "error": "Database connection failed"
}</code></pre>
                                    </div>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Important Notes</h3>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <ul class="space-y-2 text-sm text-blue-700">
                                    <li><strong>Package Name Matching:</strong> The package name must match exactly (case-sensitive) with the package name in the database.</li>
                                    <li><strong>Image URLs:</strong> All image URLs are automatically prefixed with the base URL and `/images/` path.</li>
                                    <li><strong>Date Blockers:</strong> Date blockers are specific to each room type and indicate periods when the room type is not available.</li>
                                    <li><strong>Booking URL:</strong> The booking_page_url provides a direct link to the quotation page for the specific package.</li>
                                    <li><strong>No Authentication:</strong> This endpoint does not require authentication and is designed for chatbot integration.</li>
                                </ul>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Workflow Integration</h3>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="font-semibold text-green-800 mb-2">Typical Usage Flow</h4>
                                <ol class="space-y-2 text-sm text-green-700 list-decimal list-inside">
                                    <li><strong>Step 1:</strong> Use this endpoint to get available room types and package information</li>
                                    <li><strong>Step 2:</strong> Present room options to the user and collect their preferences</li>
                                    <li><strong>Step 3:</strong> Use the <code class="bg-green-100 px-1 rounded">/fetch-quotation</code> endpoint to get pricing for selected dates and room allocation</li>
                                    <li><strong>Step 4:</strong> Use the <code class="bg-green-100 px-1 rounded">booking_page_url</code> to direct users to the booking page</li>
                                </ol>
                                <div class="mt-3 p-3 bg-green-100 rounded">
                                    <p class="text-green-800 text-sm"><strong>Pro Tip:</strong> The <code class="bg-green-200 px-1 rounded">room_type_id</code> values returned by this endpoint are required for the quotation endpoint.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Fetch Quotation Section -->
                <section id="fetch-quotation" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">2. Fetch Package Quotation</h2>

                    <div class="endpoint-card">
                        <div class="endpoint-header">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-post">POST</span>
                                <code class="text-lg font-mono">{{ $baseUrl }}/{{ $botPrefix }}/fetch-quotation</code>
                            </div>
                            <p class="text-gray-600 mt-2">Get detailed pricing quotation for a specific travel date and room allocation.</p>
                        </div>

                        <div class="endpoint-body">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Body</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "package_id": 1,
    "travel_date_start": "2025-07-15",
    "rooms": [
        {
            "room_type_id": 1,
            "adults": 1,
            "children": 1,
            "infants": 0
        }
    ]
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "success": true,
    "currency": "MYR",
    "nights": [
        {
            "date": "2025-07-15",
            "season": "Peak Season",
            "season_type": "Peak Season",
            "date_type": "Weekday",
            "is_weekend": false,
            "room_type": 1,
            "adults": 1,
            "children": 1,
            "infants": 0,
            "base_charge": {
                "adult": {"price": 646.87, "quantity": 1, "total": 646.87},
                "child": {"price": 410.53, "quantity": 1, "total": 410.53},
                "infant": {"price": 92.66, "quantity": 0, "total": 0},
                "total": 1057.4
            },
            "surcharge": {
                "adult": {"price": 66.68, "quantity": 1, "total": 66.68},
                "child": {"price": 202.88, "quantity": 1, "total": 202.88},
                "infant": {"price": 87.18, "quantity": 0, "total": 0},
                "total": 269.56
            },
            "total": 1326.96
        }
    ],
    "rooms": [
        {
            "room_type_name": "Deluxe Room",
            "room_type": 1,
            "adults": 1,
            "children": 1,
            "nights": [...],
            "summary": {
                "base_charges": {
                    "adult": {
                        "price_per_night": 646.87,
                        "quantity": 1,
                        "total": 3926.97
                    },
                    "child": {
                        "price_per_night": 410.53,
                        "quantity": 1,
                        "total": 3028.29
                    },
                    "infant": {
                        "price_per_night": 92.66,
                        "quantity": 0,
                        "total": 0
                    },
                    "total": 6955.26
                },
                "surcharges": {
                    "adult": {
                        "price_per_night": 66.68,
                        "quantity": 1,
                        "total": 1080.48
                    },
                    "child": {
                        "price_per_night": 202.88,
                        "quantity": 1,
                        "total": 1523.62
                    },
                    "infant": {
                        "price_per_night": 87.18,
                        "quantity": 0,
                        "total": 0
                    },
                    "total": 2604.1
                },
                "total": 9559.36
            }
        }
    ],
    "summary": {
        "total_nights": 7,
        "base_charges": {
            "adult": {"total": 3926.97},
            "child": {"total": 3028.29},
            "infant": {"total": 0},
            "total": 6955.26
        },
        "surcharges": {
            "adult": {"total": 1080.48},
            "child": {"total": 1523.62},
            "infant": {"total": 0},
            "total": 2604.1
        },
        "grand_total": 9559.36
    },
    "total": 9559.36
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response Fields</h3>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm border border-gray-300 rounded-lg">
                                        <thead>
                                            <tr class="border-b border-gray-200">
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Field</th>
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Type</th>
                                                <th class="text-left py-2 px-3 font-semibold text-gray-900">Description</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            <!-- Quotation Information -->
                                            <tr class="bg-blue-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-blue-900">Quotation Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">currency</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Currency code (MYR)</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">nights</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of rooms</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Summary information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total cost for all nights</td>
                                            </tr>
                                            
                                            <!-- Room Information -->
                                            <tr class="bg-green-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-green-900">Room Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].room_type_name</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Room type name</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].room_type</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Room type identifier</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].adults</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Number of adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].children</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Number of children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].infants</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Number of infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights</td>
                                                <td class="py-2 px-3 text-gray-600">array</td>
                                                <td class="py-2 px-3 text-gray-700">Array of nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Room summary</td>
                                            </tr>
                                            
                                            <!-- Nights Information -->
                                            <tr class="bg-purple-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-purple-900">Nights Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].date</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Date of the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].season</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Season of the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].season_type</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Season type of the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].date_type</td>
                                                <td class="py-2 px-3 text-gray-600">string</td>
                                                <td class="py-2 px-3 text-gray-700">Date type of the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].is_weekend</td>
                                                <td class="py-2 px-3 text-gray-600">boolean</td>
                                                <td class="py-2 px-3 text-gray-700">Whether the night is a weekend</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].base_charge</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].base_charge.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].base_charge.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].base_charge.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].base_charge.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total base charge for the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].surcharge</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].surcharge.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].surcharge.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].surcharge.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].surcharge.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total surcharge for the night</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].nights[].total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total cost for the night</td>
                                            </tr>
                                            
                                            <!-- Room Summary -->
                                            <tr class="bg-purple-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-purple-900">Room Summary</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Room summary</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.base_charges</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.base_charges.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.base_charges.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.base_charges.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.base_charges.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total base charge for the room</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.surcharges</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.surcharges.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.surcharges.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.surcharges.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.surcharges.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total surcharge for the room</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">rooms[].summary.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total cost for the room</td>
                                            </tr>
                                            
                                            <!-- Summary Information -->
                                            <tr class="bg-purple-50">
                                                <td colspan="3" class="py-2 px-3 font-semibold text-purple-900">Summary Information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Summary information</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.total_nights</td>
                                                <td class="py-2 px-3 text-gray-600">integer</td>
                                                <td class="py-2 px-3 text-gray-700">Total number of nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.base_charges</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.base_charges.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.base_charges.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.base_charges.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Base charge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.base_charges.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total base charge for all nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.surcharges</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge breakdown</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.surcharges.adult</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for adults</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.surcharges.child</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for children</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.surcharges.infant</td>
                                                <td class="py-2 px-3 text-gray-600">object</td>
                                                <td class="py-2 px-3 text-gray-700">Surcharge for infants</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.surcharges.total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total surcharge for all nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">summary.grand_total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total cost for all nights</td>
                                            </tr>
                                            <tr class="hover:bg-gray-100">
                                                <td class="py-2 px-3 font-mono text-gray-800">grand_total</td>
                                                <td class="py-2 px-3 text-gray-600">decimal</td>
                                                <td class="py-2 px-3 text-gray-700">Total cost for all rooms</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Validation Rules</h3>
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                <h4 class="font-semibold text-yellow-800 mb-2">Required Fields</h4>
                                <ul class="space-y-1 text-sm text-yellow-700">
                                    <li><strong>package_id:</strong> Required integer, must exist in packages table</li>
                                    <li><strong>travel_date_start:</strong> Required date, must be after today</li>
                                    <li><strong>rooms:</strong> Required array, minimum 1 room</li>
                                </ul>
                                <h4 class="font-semibold text-yellow-800 mb-2 mt-4">Room Array Fields</h4>
                                <ul class="space-y-1 text-sm text-yellow-700">
                                    <li><strong>rooms[].room_type_id:</strong> Required integer, must exist in room_types table</li>
                                    <li><strong>rooms[].adults:</strong> Required integer, minimum 1</li>
                                    <li><strong>rooms[].children:</strong> Optional integer, minimum 0</li>
                                    <li><strong>rooms[].infants:</strong> Optional integer, minimum 0</li>
                                </ul>
                                <div class="mt-3 p-3 bg-yellow-100 rounded">
                                    <p class="text-yellow-800 text-sm"><strong>Note:</strong> Total passenger counts are automatically calculated from the sum of all rooms.</p>
                                    <p class="text-yellow-800 text-sm mt-1"><strong>Response Note:</strong> This endpoint returns a comprehensive response with detailed nightly breakdowns, making it ideal for chatbot integration that needs to explain pricing to users.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Error Responses Section -->
                <section id="error-responses" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Error Responses</h2>

                    <div class="space-y-6">
                        <div class="endpoint-card">
                            <div class="endpoint-header">
                                <h3 class="text-lg font-semibold text-gray-900">Validation Error (400)</h3>
                            </div>
                            <div class="endpoint-body">
                                <div class="code-block">
                                    <pre><code class="language-json">{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "package_id": ["The package id field is required."]
    }
}</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="endpoint-card">
                            <div class="endpoint-header">
                                <h3 class="text-lg font-semibold text-gray-900">Package Not Found (404)</h3>
                            </div>
                            <div class="endpoint-body">
                                <div class="code-block">
                                    <pre><code class="language-json">{
    "success": false,
    "message": "Package not found"
}</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="endpoint-card">
                            <div class="endpoint-header">
                                <h3 class="text-lg font-semibold text-gray-900">Date Not Available (400)</h3>
                            </div>
                            <div class="endpoint-body">
                                <div class="code-block">
                                    <pre><code class="language-json">{
    "success": false,
    "message": "Selected date is not available",
    "suggested_dates": ["2024-01-18", "2024-01-19", "2024-01-26"]
}</code></pre>
                                </div>
                            </div>
                        </div>

                        <div class="endpoint-card">
                            <div class="endpoint-header">
                                <h3 class="text-lg font-semibold text-gray-900">Server Error (500)</h3>
                            </div>
                            <div class="endpoint-body">
                                <div class="code-block">
                                    <pre><code class="language-json">{
    "success": false,
    "message": "Server error",
    "error": "Database connection failed"
}</code></pre>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Important Notes Section -->
                <section id="notes" class="mb-12 hidden">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Important Notes</h2>

                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">No Authentication Required</h3>
                                    <p class="text-gray-600">These endpoints are designed for chatbot integration and don't require authentication.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Date Format</h3>
                                    <p class="text-gray-600">All dates should be in <code class="bg-gray-100 px-1 rounded">YYYY-MM-DD</code> format.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Pricing</h3>
                                    <p class="text-gray-600">All prices are in Malaysian Ringgit (MYR).</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-orange-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Room Allocation</h3>
                                    <p class="text-gray-600">The sum of adults, children, and infants in all rooms should match the total pax count.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-red-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Date Blockers</h3>
                                    <p class="text-gray-600">The API automatically checks for blocked dates and suggests alternatives.</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-2 h-2 bg-indigo-500 rounded-full mt-2 flex-shrink-0"></div>
                                <div>
                                    <h3 class="font-semibold text-gray-900">Season/Date Types</h3>
                                    <p class="text-gray-600">The system automatically determines the appropriate season and date type based on the travel date.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-16 text-center text-gray-500 border-t border-gray-200 pt-8 hidden">
            <p>&copy; 2024 Bot API Documentation. Built with Laravel and Tailwind CSS.</p>
        </footer>
    </div>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Active navigation highlighting
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-link');

        window.addEventListener('scroll', () => {
            let current = '';
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                if (scrollY >= (sectionTop - 200)) {
                    current = section.getAttribute('id');
                }
            });

            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === `#${current}`) {
                    link.classList.add('active');
                }
            });
        });

        // Copy code functionality
        document.querySelectorAll('.code-block').forEach(block => {
            const copyButton = document.createElement('button');
            copyButton.textContent = 'Copy';
            copyButton.className = 'absolute top-2 right-2 bg-gray-700 text-white px-2 py-1 rounded text-xs hover:bg-gray-600 transition-colors';
            copyButton.onclick = () => {
                const code = block.querySelector('code').textContent;
                navigator.clipboard.writeText(code).then(() => {
                    copyButton.textContent = 'Copied!';
                    setTimeout(() => {
                        copyButton.textContent = 'Copy';
                    }, 2000);
                });
            };

            block.style.position = 'relative';
            block.appendChild(copyButton);
        });
    </script>
</body>

</html>