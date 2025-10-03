<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Charging Station - EV Charging Platform</title>
                        <button type="button" @click="console.log('Current booking object:', booking)"
                                class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded mr-2">Debug Log</button>
                        <button type="button" @click="syncDateTime()"
                                class="mt-2 px-3 py-1 bg-green-500 text-white text-xs rounded">Force Sync Time</button>  <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gradient-to-br from-slate-900 via-blue-900 to-slate-900 min-h-screen">

    <!-- Navigation Header -->
    <nav class="bg-slate-800/80 backdrop-blur-sm border-b border-slate-700 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-xl font-bold text-white">EV Charging</span>
                    </a>
                </div>
                <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition duration-300">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="min-h-screen py-12 px-4" x-data="bookingForm()">
        <div class="max-w-4xl mx-auto">

            <!-- Header Section -->
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-white mb-4">Book Charging Station</h1>
                <p class="text-xl text-gray-300">Reserve your charging slot and get powered up!</p>

                <!-- Payment cancelled alert -->
                @if(request()->get('payment_cancelled'))
                <div class="mt-6 bg-yellow-900/50 border border-yellow-500/50 rounded-lg p-4 max-w-md mx-auto">
                    <div class="flex items-center space-x-3">
                        <svg class="w-6 h-6 text-yellow-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L5.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                        <div>
                            <h4 class="text-yellow-300 font-medium">Payment Cancelled</h4>
                            <p class="text-yellow-200 text-sm">Your payment was cancelled. You can try booking again.</p>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Station Information -->
            <div x-show="stationInfo" class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-8 mb-8">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-16 h-16 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-white" x-text="stationInfo?.name || 'Loading...'"></h2>
                        <p class="text-gray-400" x-text="stationInfo?.address || 'Loading address...'"></p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-blue-400" x-text="stationInfo?.power || 'N/A'"></div>
                        <div class="text-sm text-gray-400">Power Output</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-green-400" x-text="stationInfo?.pricing_per_hour ? '$' + stationInfo.pricing_per_hour + '/hr' : 'N/A'"></div>
                        <div class="text-sm text-gray-400">Price per Hour</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold" :class="(stationInfo?.available_slots || 0) > 0 ? 'text-green-400' : 'text-red-400'" x-text="(stationInfo?.available_slots || 0) + '/' + (stationInfo?.total_slots || 0) + ' available'"></div>
                        <div class="text-sm text-gray-400">Available Slots</div>
                        <div x-show="(stationInfo?.available_slots || 0) === 0" class="text-xs text-red-400 mt-1">Fully Reserved</div>
                    </div>
                </div>
            </div>

            <!-- Booking Form -->
            <div class="bg-slate-800/50 backdrop-blur-sm border border-slate-700 rounded-2xl p-8">
                <h3 class="text-2xl font-bold text-white mb-8">Reservation Details</h3>

                <form @submit.prevent="submitBooking" class="space-y-6">
                    <!-- Date and Time Selection -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-gray-300 text-lg font-medium mb-3">Start Date & Time</label>
                            <input type="datetime-local"
                                   x-ref="startTimeInput"
                                   @input="booking.start_time = $event.target.value; console.log('Start time manually set to:', booking.start_time); calculateEndTime()"
                                   @change="booking.start_time = $event.target.value; console.log('Start time change event:', booking.start_time)"
                                   required
                                   :min="minDateTime"
                                   class="w-full px-4 py-3 bg-slate-900 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-gray-300 text-lg font-medium mb-3">Duration (Hours)</label>
                            <select x-model="booking.duration_hours"
                                    @change="console.log('Duration changed to:', booking.duration_hours); calculateEndTime(); calculateEstimatedCost()"
                                    required
                                    class="w-full px-4 py-3 bg-slate-900 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Select Duration</option>
                                <option value="0.5">30 minutes</option>
                                <option value="1">1 hour</option>
                                <option value="1.5">1.5 hours</option>
                                <option value="2">2 hours</option>
                                <option value="3">3 hours</option>
                                <option value="4">4 hours</option>
                                <option value="6">6 hours</option>
                                <option value="8">8 hours</option>
                                <option value="12">12 hours</option>
                            </select>
                        </div>
                    </div>

                    <!-- End Time Display -->
                    <div x-show="booking.end_time">
                        <label class="block text-gray-300 text-lg font-medium mb-3">End Time</label>
                        <div class="w-full px-4 py-3 bg-slate-900/50 border border-slate-600 rounded-xl text-gray-300" x-text="formatDateTime(booking.end_time)"></div>
                    </div>

                    <!-- Energy Needed -->
                    <div>
                        <label class="block text-gray-300 text-lg font-medium mb-3">Estimated Energy Needed (kWh)</label>
                        <input type="number" x-model="booking.estimated_energy_needed" step="0.1" min="1" max="100"
                               @input="calculateEstimatedCost()"
                               placeholder="e.g., 25 kWh"
                               class="w-full px-4 py-3 bg-slate-900 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <p class="text-sm text-gray-400 mt-2">Average car battery: 40-100 kWh. Most charging sessions: 10-50 kWh</p>
                    </div>

                    <!-- Vehicle Information -->
                    <div>
                        <label class="block text-gray-300 text-lg font-medium mb-3">Vehicle Information (Optional)</label>
                        <input type="text" x-model="booking.vehicle_info"
                               placeholder="e.g., Tesla Model 3, Nissan Leaf, etc."
                               class="w-full px-4 py-3 bg-slate-900 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <!-- Special Notes -->
                    <div>
                        <label class="block text-gray-300 text-lg font-medium mb-3">Special Notes (Optional)</label>
                        <textarea x-model="booking.notes" rows="3"
                                  placeholder="Any special requirements or notes for this charging session..."
                                  class="w-full px-4 py-3 bg-slate-900 border border-slate-600 rounded-xl text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 resize-none"></textarea>
                    </div>

                    <!-- Cost Summary -->
                    <div x-show="estimatedCost > 0" class="bg-slate-900/50 rounded-xl p-6 border border-slate-600">
                        <h4 class="text-lg font-semibold text-white mb-4">Cost Estimate</h4>
                        <div class="space-y-2">
                            <div class="flex justify-between">
                                <span class="text-gray-400">Duration:</span>
                                <span class="text-white" x-text="booking.duration_hours + ' hours'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Rate per hour:</span>
                                <span class="text-white" x-text="'$' + (stationInfo?.pricing_per_hour || 0)"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-400">Energy estimate:</span>
                                <span class="text-white" x-text="(booking.estimated_energy_needed || 0) + ' kWh'"></span>
                            </div>
                            <div class="border-t border-slate-600 pt-2 mt-2">
                                <div class="flex justify-between text-lg font-semibold">
                                    <span class="text-white">Estimated Total:</span>
                                    <span class="text-blue-400" x-text="'$' + estimatedCost.toFixed(2)"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Information -->
                    <div class="bg-blue-900/30 border border-blue-500/30 rounded-lg p-4 mb-6">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-blue-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <div>
                                <h4 class="text-sm font-medium text-blue-300 mb-1">Secure Payment</h4>
                                <p class="text-sm text-blue-200">You'll be redirected to Stripe's secure payment page to complete your booking payment safely.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-6">
                        <button type="submit" :disabled="submitting || !booking.start_time || !booking.duration_hours"
                                @click="console.log('Button clicked - Start Time:', booking.start_time, 'Duration:', booking.duration_hours)"
                                class="w-full py-4 px-6 bg-gradient-to-r from-green-600 to-blue-600 text-white font-bold text-lg rounded-xl hover:from-green-700 hover:to-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition duration-300 shadow-lg">
                            <span x-show="!submitting" class="flex items-center justify-center gap-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3 3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                Proceed to Payment
                            </span>
                            <span x-show="submitting" class="flex items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 078-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Preparing Payment...
                            </span>
                        </button>                    <!-- Back to Map -->
                    <div class="text-center">
                        <a href="{{ route('dashboard') }}" class="text-gray-400 hover:text-white transition duration-300">
                            ← Back to Map
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function bookingForm() {
            return {
                stationId: new URLSearchParams(window.location.search).get('station'),
                stationInfo: null,
                booking: {
                    start_time: '',
                    duration_hours: '',
                    end_time: '',
                    estimated_energy_needed: 25,
                    vehicle_info: '',
                    notes: ''
                },
                estimatedCost: 0,
                submitting: false,
                minDateTime: new Date(Date.now() + 30 * 60000).toISOString().slice(0, 16), // 30 minutes from now

                async init() {
                    if (this.stationId) {
                        await this.loadStationInfo();
                    } else {
                        // Redirect to dashboard if no station selected
                        window.location.href = '{{ route("dashboard") }}';
                    }
                },

                async loadStationInfo() {
                    try {
                        const response = await fetch(`/api/stations/${this.stationId}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            console.log('Station data loaded:', data);
                            // Handle the correct API response format
                            this.stationInfo = data.data || data.station || data;
                        } else {
                            console.error('Failed to load station info');
                            alert('Failed to load station information. Redirecting to map...');
                            window.location.href = '{{ route("dashboard") }}';
                        }
                    } catch (error) {
                        console.error('Error loading station info:', error);
                        alert('Error loading station information. Redirecting to map...');
                        window.location.href = '{{ route("dashboard") }}';
                    }
                },

                // Force sync datetime input value
                syncDateTime() {
                    const input = this.$refs.startTimeInput;
                    if (input && input.value) {
                        this.booking.start_time = input.value;
                        console.log('Synced datetime value:', this.booking.start_time);
                    }
                },

                calculateEndTime() {
                    if (this.booking.start_time && this.booking.duration_hours) {
                        const startTime = new Date(this.booking.start_time);
                        const endTime = new Date(startTime.getTime() + (parseFloat(this.booking.duration_hours) * 60 * 60 * 1000));
                        this.booking.end_time = endTime.toISOString();
                    }
                },

                calculateEstimatedCost() {
                    if (this.booking.duration_hours && this.stationInfo?.pricing_per_hour) {
                        this.estimatedCost = parseFloat(this.booking.duration_hours) * parseFloat(this.stationInfo.pricing_per_hour);
                    }
                },

                formatDateTime(dateTime) {
                    if (!dateTime) return '';
                    return new Date(dateTime).toLocaleString();
                },

                async submitBooking() {
                    if (this.submitting) return;

                    this.submitting = true;

                    try {
                        const bookingData = {
                            station_id: this.stationId,
                            start_time: this.booking.start_time,
                            duration_hours: parseFloat(this.booking.duration_hours),
                            estimated_energy_needed: parseFloat(this.booking.estimated_energy_needed) || null,
                            notes: this.booking.notes || null
                        };

                        const response = await fetch('/api/bookings', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify(bookingData)
                        });

                        const result = await response.json();
                        console.log('Booking result:', result);

                        if (response.ok && result.success) {
                            // Check if payment is required (new Stripe flow)
                            if (result.payment_required && result.stripe_checkout_url) {
                                // Show booking details confirmation before redirecting to payment
                                const confirmPayment = confirm(
                                    '💰 Payment Required\n\n' +
                                    '📍 Station: ' + result.booking_details.station.name + '\n' +
                                    '📅 Date & Time: ' + result.booking_details.start_time + '\n' +
                                    '⏱️ Duration: ' + result.booking_details.duration_hours + ' hours\n' +
                                    '💵 Total Cost: ' + result.booking_details.total_amount + '\n\n' +
                                    '🔒 You will be redirected to secure Stripe payment page.\n\n' +
                                    'Continue to payment?'
                                );

                                if (confirmPayment) {
                                    // Redirect to Stripe Checkout
                                    window.location.href = result.stripe_checkout_url;
                                } else {
                                    this.submitting = false;
                                }
                            } else {
                                // Old flow (if no payment required)
                                alert('🎉 Reservation created successfully!\n\n📍 Station: ' + (result.booking?.station?.name || 'Selected station') + '\n📅 Date: ' + (result.booking?.date || 'Selected date') + '\n⏰ Time: ' + (result.booking?.time || 'Selected time') + '\n💰 Cost: ' + (result.booking?.total_amount || 'Calculated cost'));
                                window.location.href = '{{ route("dashboard") }}#reservations';
                            }
                        } else {
                            const errorMessage = result.error || result.message || 'Unknown error occurred';

                            // Special handling for fully reserved error
                            if (errorMessage.includes('fully reserved')) {
                                alert('🚫 Station Fully Reserved\n\n' + errorMessage + '\n\n💡 Try selecting a different time slot or choose another charging station.');
                            } else if (errorMessage.includes('booking limit')) {
                                alert('📊 Booking Limit Reached\n\n' + errorMessage + '\n\n⭐ Consider upgrading to Premium for unlimited bookings!');
                            } else {
                                alert('❌ Failed to create reservation:\n\n' + errorMessage + '\n\nPlease try again or contact support if the problem persists.');
                            }
                        }
                    } catch (error) {
                        console.error('Booking error:', error);
                        alert('❌ Network error occurred while creating reservation.\n\nPlease check your connection and try again.');
                    } finally {
                        this.submitting = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
