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
        .method-post { @apply bg-green-100 text-green-800; }
        .method-get { @apply bg-blue-100 text-blue-800; }
        .method-put { @apply bg-yellow-100 text-yellow-800; }
        .method-delete { @apply bg-red-100 text-red-800; }
        
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
                <p class="text-blue-800 font-medium">Base URL: <code class="bg-blue-100 px-2 py-1 rounded">{{ $baseUrl }}/bot-api</code></p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Navigation Sidebar -->
            <div class="lg:w-1/4">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Navigation</h2>
                    <nav class="space-y-2">
                        <a href="#overview" class="nav-link active">Overview</a>
                        <a href="#fetch-room-types" class="nav-link">Fetch Room Types</a>
                        <a href="#fetch-quotation" class="nav-link">Fetch Quotation</a>
                        <a href="#error-responses" class="nav-link">Error Responses</a>
                        <a href="#examples" class="nav-link">Usage Examples</a>
                        <!-- <a href="#notes" class="nav-link">Important Notes</a> -->
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="lg:w-3/4">
                <!-- Overview Section -->
                <section id="overview" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Overview</h2>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <p class="text-gray-700 mb-4">
                            This API provides endpoints for chatbot integration to fetch package information and quotations. 
                            All endpoints are designed to be easily consumed by chatbots and other applications.
                        </p>
                        <div class="grid md:grid-cols-2 gap-4 mt-6 hidden">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-green-800 mb-2">✅ No Authentication Required</h3>
                                <p class="text-green-700 text-sm">Perfect for chatbot integration</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-blue-800 mb-2">📅 Smart Date Handling</h3>
                                <p class="text-blue-700 text-sm">Automatic date blocker checking</p>
                            </div>
                            <div class="bg-purple-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-purple-800 mb-2">💰 Detailed Pricing</h3>
                                <p class="text-purple-700 text-sm">Complete breakdown of costs</p>
                            </div>
                            <div class="bg-orange-50 p-4 rounded-lg">
                                <h3 class="font-semibold text-orange-800 mb-2">🎯 Easy Integration</h3>
                                <p class="text-orange-700 text-sm">Simple JSON request/response</p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- Fetch Room Types Section -->
                <section id="fetch-room-types" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">1. Fetch Room Types by Package Name</h2>
                    
                    <div class="endpoint-card">
                        <div class="endpoint-header">
                            <div class="flex items-center gap-3">
                                <span class="method-badge method-post">POST</span>
                                <code class="text-lg font-mono">{{ $baseUrl }}/bot-api/fetch-room-types</code>
                            </div>
                            <p class="text-gray-600 mt-2">Get available room types, dates, and package information for a specific package.</p>
                        </div>
                        
                        <div class="endpoint-body">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Body</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "package_name": "Langkawi Island Resort"
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "success": true,
    "data": {
        "package": {
            "id": 1,
            "name": "Langkawi Island Resort",
            "description": "Beautiful island resort package",
            "location": "Langkawi, Malaysia",
            "min_days": 2,
            "max_days": 7,
            "child_max_age_desc": "Children under 12 years",
            "infant_max_age_desc": "Infants under 2 years",
            "display_prices": {
                "adult": 150.00,
                "child": 75.00,
                "infant": 0.00
            }
        },
        "room_types": [
            {
                "id": 1,
                "name": "Deluxe Room",
                "description": "Spacious room with sea view",
                "max_occupancy": 4,
                "images": ["room1.jpg", "room2.jpg"],
                "available_dates": ["2024-01-15", "2024-01-16", "2024-01-17"]
            }
        ],
        "date_blockers": [
            {
                "start_date": "2024-01-20",
                "end_date": "2024-01-25",
                "room_type_id": 1,
                "room_type_name": "Deluxe Room"
            }
        ],
        "season_types": ["Peak Season", "Off Peak"],
        "date_types": ["Weekday", "Weekend"]
    }
}</code></pre>
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
                                <code class="text-lg font-mono">{{ $baseUrl }}/bot-api/fetch-quotation</code>
                            </div>
                            <p class="text-gray-600 mt-2">Get detailed pricing quotation for a specific travel date and room allocation.</p>
                        </div>
                        
                        <div class="endpoint-body">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Request Body</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "package_name": "Langkawi Island Resort",
    "travel_date": "2024-01-15",
    "adults": 4,
    "children": 2,
    "infants": 1,
    "rooms": [
        {
            "room_type_id": 1,
            "adults": 2,
            "children": 1,
            "infants": 0
        },
        {
            "room_type_id": 1,
            "adults": 2,
            "children": 1,
            "infants": 1
        }
    ]
}</code></pre>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 mb-4 mt-6">Response</h3>
                            <div class="code-block">
                                <pre><code class="language-json">{
    "success": true,
    "data": {
        "package_name": "Langkawi Island Resort",
        "travel_date": "2024-01-15",
        "total_pax": {
            "adults": 4,
            "children": 2,
            "infants": 1
        },
        "rooms": [
            {
                "room_type_id": 1,
                "room_type_name": "Deluxe Room",
                "season_type": "Peak Season",
                "date_type": "Weekday",
                "pricing_breakdown": {
                    "base_price": {
                        "adults": 300.00,
                        "children": 75.00,
                        "infants": 0.00,
                        "total": 375.00
                    },
                    "surcharge": {
                        "adults": 50.00,
                        "children": 25.00,
                        "infants": 0.00,
                        "total": 75.00
                    }
                },
                "total_price": 450.00
            }
        ],
        "grand_total": 900.00,
        "currency": "MYR"
    }
}</code></pre>
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
        "package_name": ["The package name field is required."]
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

                <!-- Usage Examples Section -->
                <section id="examples" class="mb-12">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">Usage Examples</h2>
                    
                    <div class="space-y-8">
                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">cURL Examples</h3>
                            
                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <h4 class="font-semibold text-gray-900">Fetch Room Types</h4>
                                </div>
                                <div class="endpoint-body">
                                    <div class="code-block">
                                        <pre><code class="language-bash">curl -X POST {{ $baseUrl }}/bot-api/fetch-room-types \
  -H "Content-Type: application/json" \
  -d '{"package_name": "Langkawi Island Resort"}'</code></pre>
                                    </div>
                                </div>
                            </div>

                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <h4 class="font-semibold text-gray-900">Fetch Quotation</h4>
                                </div>
                                <div class="endpoint-body">
                                    <div class="code-block">
                                        <pre><code class="language-bash">curl -X POST {{ $baseUrl }}/bot-api/fetch-quotation \
  -H "Content-Type: application/json" \
  -d '{
    "package_name": "Langkawi Island Resort",
    "travel_date": "2024-01-15",
    "adults": 2,
    "children": 1,
    "infants": 0,
    "rooms": [
      {
        "room_type_id": 1,
        "adults": 2,
        "children": 1,
        "infants": 0
      }
    ]
  }'</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <h3 class="text-xl font-semibold text-gray-900 mb-4">JavaScript Examples</h3>
                            
                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <h4 class="font-semibold text-gray-900">Fetch Room Types</h4>
                                </div>
                                <div class="endpoint-body">
                                    <div class="code-block">
                                        <pre><code class="language-javascript">const response = await fetch('{{ $baseUrl }}/bot-api/fetch-room-types', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    package_name: 'Langkawi Island Resort'
  })
});

const data = await response.json();
console.log(data);</code></pre>
                                    </div>
                                </div>
                            </div>

                            <div class="endpoint-card">
                                <div class="endpoint-header">
                                    <h4 class="font-semibold text-gray-900">Fetch Quotation</h4>
                                </div>
                                <div class="endpoint-body">
                                    <div class="code-block">
                                        <pre><code class="language-javascript">const response = await fetch('{{ $baseUrl }}/bot-api/fetch-quotation', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
  },
  body: JSON.stringify({
    package_name: 'Langkawi Island Resort',
    travel_date: '2024-01-15',
    adults: 2,
    children: 1,
    infants: 0,
    rooms: [
      {
        room_type_id: 1,
        adults: 2,
        children: 1,
        infants: 0
      }
    ]
  })
});

const data = await response.json();
console.log(data);</code></pre>
                                    </div>
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
        <footer class="mt-16 text-center text-gray-500 border-t border-gray-200 pt-8">
            <p>&copy; 2024 Bot API Documentation. Built with Laravel and Tailwind CSS.</p>
        </footer>
    </div>

    <script>
        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
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