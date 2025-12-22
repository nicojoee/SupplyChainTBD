@extends('layouts.app')

@section('title', 'My Deliveries')

@section('content')
<!-- GPS Permission Modal -->
<div id="gps-permission-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.85); z-index: 10000; align-items: center; justify-content: center;">
    <div style="background: linear-gradient(145deg, #1e1b4b, #312e81); border-radius: 20px; padding: 2rem; max-width: 450px; width: 90%; text-align: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: 0 25px 50px rgba(0,0,0,0.5);">
        <div style="font-size: 4rem; margin-bottom: 1rem;">📍</div>
        <h3 style="margin: 0 0 1rem 0; color: #fff; font-size: 1.5rem;">Izinkan Akses Lokasi</h3>
        <p style="color: rgba(255,255,255,0.7); margin-bottom: 1.5rem; line-height: 1.6;">
            Untuk melacak posisi Anda secara real-time, aplikasi memerlukan izin akses lokasi GPS.
        </p>
        <div style="background: rgba(255,255,255,0.05); padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; text-align: left;">
            <div style="font-weight: 600; color: #fff; margin-bottom: 0.5rem;">📱 Cara Mengaktifkan:</div>
            <ol style="color: rgba(255,255,255,0.6); margin: 0; padding-left: 1.25rem; font-size: 0.9rem;">
                <li>Klik tombol "Izinkan GPS" di bawah</li>
                <li>Pilih "Allow" atau "Izinkan" pada popup browser</li>
                <li>Pastikan GPS/Location aktif di perangkat Anda</li>
            </ol>
        </div>
        <div style="display: flex; gap: 1rem;">
            <button onclick="closeGpsPermissionModal()" style="flex: 1; padding: 0.75rem 1rem; background: rgba(255,255,255,0.1); color: #fff; border: none; border-radius: 10px; cursor: pointer; font-size: 0.9rem;">
                Nanti Saja
            </button>
            <button onclick="requestGpsPermission()" style="flex: 2; padding: 0.75rem 1rem; background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff; border: none; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 0.9rem;">
                📍 Izinkan GPS
            </button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">🛵 My Courier Profile</h2>
        <a href="{{ route('dashboard') }}" class="btn btn-info" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
            🗺️ Open Map
        </a>
    </div>
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Name</div>
                <div style="font-weight: 600;">{{ $courier->name }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Vehicle</div>
                <div>{{ $courier->vehicle_type ?? 'N/A' }} • {{ $courier->license_plate ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Phone</div>
                <div>{{ $courier->phone ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.25rem;">Current Status</div>
                <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : 'badge-danger' }}">
                    {{ ucfirst($courier->status) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- GPS Tracking Section -->
<div class="card" style="margin-top: 1rem; border: 2px solid rgba(14, 165, 233, 0.3);">
    <div class="card-header" style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.1), rgba(99, 102, 241, 0.1));">
        <h2 class="card-title">📍 GPS Tracking</h2>
        <div style="display: flex; gap: 0.5rem; align-items: center;">
            <span id="gps-status-badge" class="badge badge-warning">⏳ Menunggu...</span>
            <button id="gps-toggle-btn" onclick="toggleGpsTracking()" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.9rem;">
                ▶️ Mulai GPS
            </button>
        </div>
    </div>
    <div style="padding: 1rem;">
        <!-- GPS Info Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 10px; text-align: center;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem; margin-bottom: 0.25rem;">LATITUDE</div>
                <div id="gps-latitude" style="font-family: monospace; font-size: 1.1rem; font-weight: 600;">-</div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 10px; text-align: center;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem; margin-bottom: 0.25rem;">LONGITUDE</div>
                <div id="gps-longitude" style="font-family: monospace; font-size: 1.1rem; font-weight: 600;">-</div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 10px; text-align: center;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem; margin-bottom: 0.25rem;">AKURASI</div>
                <div id="gps-accuracy" style="font-size: 1.1rem; font-weight: 600;">-</div>
                <div id="gps-accuracy-label" style="font-size: 0.7rem; margin-top: 0.25rem;"></div>
            </div>
            <div style="background: rgba(255,255,255,0.03); padding: 1rem; border-radius: 10px; text-align: center;">
                <div style="color: rgba(255,255,255,0.5); font-size: 0.75rem; margin-bottom: 0.25rem;">UPDATE TERAKHIR</div>
                <div id="gps-last-update" style="font-size: 1rem;">-</div>
            </div>
        </div>
        
        <!-- Accuracy Legend -->
        <div style="display: flex; gap: 1rem; flex-wrap: wrap; justify-content: center; padding: 0.75rem; background: rgba(255,255,255,0.02); border-radius: 8px; font-size: 0.75rem;">
            <span><span style="display: inline-block; width: 10px; height: 10px; background: #22c55e; border-radius: 50%; margin-right: 4px;"></span> &lt;10m Excellent</span>
            <span><span style="display: inline-block; width: 10px; height: 10px; background: #84cc16; border-radius: 50%; margin-right: 4px;"></span> 10-30m Good</span>
            <span><span style="display: inline-block; width: 10px; height: 10px; background: #f59e0b; border-radius: 50%; margin-right: 4px;"></span> 30-100m Fair</span>
            <span><span style="display: inline-block; width: 10px; height: 10px; background: #ef4444; border-radius: 50%; margin-right: 4px;"></span> &gt;100m Poor</span>
        </div>
    </div>
</div>

<!-- Available Deliveries to Accept -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📋 Available Deliveries</h2>
    </div>
    @php
        $availableOrders = \App\Models\Order::whereNull('courier_id')
            ->whereIn('status', ['pickup', 'confirmed'])
            ->with(['items.product', 'sellerSupplier', 'sellerFactory'])
            ->take(10)
            ->get();
    @endphp
    @if($availableOrders->count() > 0)
        <div style="padding: 1rem; display: grid; gap: 0.75rem;">
            @foreach($availableOrders as $order)
            <div style="padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 10px; border: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <div style="font-weight: 600;">{{ $order->order_number }}</div>
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                            @foreach($order->items as $item)
                                {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }}){{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </div>
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.5); margin-top: 0.25rem;">
                            From: {{ $order->sellerSupplier->name ?? $order->sellerFactory->name ?? 'Unknown' }}
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: 600; color: #22c55e;">${{ number_format($order->total_amount, 2) }}</div>
                        <form action="{{ route('courier.accept', $order) }}" method="POST" style="margin-top: 0.5rem;">
                            @csrf
                            <button type="submit" class="btn btn-success" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                ✓ Accept Delivery
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No available deliveries at the moment.</p>
    @endif
</div>

<!-- Assigned Deliveries -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📦 Assigned Deliveries</h2>
    </div>
    @if($assignedOrders->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assignedOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>
                        <span class="badge badge-info">{{ ucfirst($order->status) }}</span>
                    </td>
                    <td>
                        <form action="{{ route('courier.orders.status', $order) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                            @csrf
                            @method('PATCH')
                            <select name="status" class="form-control" style="width: auto; padding: 0.4rem; font-size: 0.85rem;">
                                <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                                <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Update</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No assigned deliveries.</p>
    @endif
</div>

<!-- Completed Deliveries -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">✅ Completed Deliveries</h2>
    </div>
    @if($completedOrders->count() > 0)
        <table class="table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Items</th>
                    <th>Amount</th>
                    <th>Completed</th>
                </tr>
            </thead>
            <tbody>
                @foreach($completedOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>
                        @foreach($order->items as $item)
                            {{ $item->product->name ?? 'N/A' }} (x{{ $item->quantity }})<br>
                        @endforeach
                    </td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->updated_at->format('M d, Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="padding: 1rem; color: rgba(255,255,255,0.5);">No completed deliveries yet.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // ==========================================
    // IMPROVED GPS TRACKING - Courier Page
    // Using watchPosition for real-time accuracy
    // ==========================================
    
    let gpsWatchId = null;
    let isGpsTracking = false;
    let lastSentLocation = null;
    let sendLocationThrottle = null;
    
    // DOM Elements
    const statusBadge = document.getElementById('gps-status-badge');
    const toggleBtn = document.getElementById('gps-toggle-btn');
    const latDisplay = document.getElementById('gps-latitude');
    const lngDisplay = document.getElementById('gps-longitude');
    const accuracyDisplay = document.getElementById('gps-accuracy');
    const accuracyLabel = document.getElementById('gps-accuracy-label');
    const lastUpdateDisplay = document.getElementById('gps-last-update');
    const permissionModal = document.getElementById('gps-permission-modal');
    
    // Check GPS support on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🛵 Courier GPS - Initializing...');
        
        if (!navigator.geolocation) {
            updateStatus('❌ GPS Tidak Didukung', 'badge-danger');
            toggleBtn.disabled = true;
            toggleBtn.innerHTML = '❌ Tidak Didukung';
            return;
        }
        
        // Check current permission state
        if (navigator.permissions) {
            navigator.permissions.query({ name: 'geolocation' }).then(function(result) {
                console.log('GPS Permission status:', result.state);
                
                if (result.state === 'granted') {
                    // Permission already granted, start automatically
                    updateStatus('✅ GPS Siap', 'badge-success');
                    startGpsTracking();
                } else if (result.state === 'denied') {
                    // Permission denied
                    updateStatus('🚫 GPS Ditolak', 'badge-danger');
                    showPermissionDeniedHelp();
                } else {
                    // Prompt - show permission modal
                    updateStatus('⏳ Menunggu Izin...', 'badge-warning');
                    showGpsPermissionModal();
                }
                
                // Listen for permission changes
                result.onchange = function() {
                    console.log('GPS Permission changed to:', this.state);
                    if (this.state === 'granted' && !isGpsTracking) {
                        startGpsTracking();
                    }
                };
            });
        } else {
            // Fallback for browsers without Permissions API
            updateStatus('📍 Klik Mulai GPS', 'badge-info');
        }
    });
    
    // Toggle GPS tracking
    function toggleGpsTracking() {
        if (isGpsTracking) {
            stopGpsTracking();
        } else {
            startGpsTracking();
        }
    }
    
    // Start GPS tracking with watchPosition
    function startGpsTracking() {
        if (gpsWatchId !== null) {
            // Already tracking
            return;
        }
        
        updateStatus('🔄 Mendeteksi...', 'badge-warning');
        toggleBtn.innerHTML = '⏳ Memulai...';
        toggleBtn.disabled = true;
        
        const options = {
            enableHighAccuracy: true,  // Use GPS hardware if available
            timeout: 15000,            // 15 second timeout
            maximumAge: 0              // Don't use cached position
        };
        
        // Use watchPosition for continuous real-time updates
        gpsWatchId = navigator.geolocation.watchPosition(
            onGpsSuccess,
            onGpsError,
            options
        );
        
        isGpsTracking = true;
        toggleBtn.innerHTML = '⏹️ Stop GPS';
        toggleBtn.classList.remove('btn-success');
        toggleBtn.classList.add('btn-danger');
        toggleBtn.disabled = false;
        
        console.log('📍 GPS watchPosition started, ID:', gpsWatchId);
    }
    
    // Stop GPS tracking
    function stopGpsTracking() {
        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            gpsWatchId = null;
            console.log('📍 GPS watchPosition stopped');
        }
        
        isGpsTracking = false;
        toggleBtn.innerHTML = '▶️ Mulai GPS';
        toggleBtn.classList.remove('btn-danger');
        toggleBtn.classList.add('btn-success');
        updateStatus('⏸️ GPS Dihentikan', 'badge-warning');
        
        // Send inactive status to server
        sendLocationToServer(lastSentLocation?.lat, lastSentLocation?.lng, false);
    }
    
    // GPS success callback
    function onGpsSuccess(position) {
        const lat = position.coords.latitude;
        const lng = position.coords.longitude;
        const accuracy = position.coords.accuracy; // in meters
        const timestamp = new Date(position.timestamp);
        
        console.log(`📍 GPS Update: ${lat.toFixed(6)}, ${lng.toFixed(6)} (±${accuracy.toFixed(0)}m)`);
        
        // Update display
        latDisplay.textContent = lat.toFixed(6);
        lngDisplay.textContent = lng.toFixed(6);
        accuracyDisplay.textContent = `±${Math.round(accuracy)}m`;
        lastUpdateDisplay.textContent = timestamp.toLocaleTimeString('id-ID');
        
        // Update accuracy indicator with color
        updateAccuracyIndicator(accuracy);
        
        // Update status
        updateStatus('🟢 GPS Aktif', 'badge-success');
        
        // Send to server (throttled to every 3 seconds max)
        throttledSendLocation(lat, lng, accuracy);
    }
    
    // GPS error callback
    function onGpsError(error) {
        console.error('GPS Error:', error.code, error.message);
        
        let errorMsg = '';
        let statusClass = 'badge-danger';
        
        switch (error.code) {
            case error.PERMISSION_DENIED:
                errorMsg = '🚫 Izin Ditolak';
                showPermissionDeniedHelp();
                break;
            case error.POSITION_UNAVAILABLE:
                errorMsg = '📡 Sinyal Tidak Tersedia';
                statusClass = 'badge-warning';
                break;
            case error.TIMEOUT:
                errorMsg = '⏱️ Timeout - Mencoba lagi...';
                statusClass = 'badge-warning';
                break;
            default:
                errorMsg = '❌ Error GPS';
                break;
        }
        
        updateStatus(errorMsg, statusClass);
    }
    
    // Update accuracy indicator color
    function updateAccuracyIndicator(accuracy) {
        let color, label;
        
        if (accuracy < 10) {
            color = '#22c55e'; // green - excellent
            label = '🎯 Excellent';
        } else if (accuracy < 30) {
            color = '#84cc16'; // lime - good
            label = '✅ Good';
        } else if (accuracy < 100) {
            color = '#f59e0b'; // amber - fair
            label = '⚠️ Fair';
        } else {
            color = '#ef4444'; // red - poor
            label = '❌ Poor';
        }
        
        accuracyDisplay.style.color = color;
        accuracyLabel.innerHTML = `<span style="color: ${color}">${label}</span>`;
    }
    
    // Update status badge
    function updateStatus(text, badgeClass) {
        statusBadge.innerHTML = text;
        statusBadge.className = 'badge ' + badgeClass;
    }
    
    // Throttled send location to server (max every 3 seconds)
    function throttledSendLocation(lat, lng, accuracy) {
        // Clear previous timeout
        if (sendLocationThrottle) {
            clearTimeout(sendLocationThrottle);
        }
        
        // Store for immediate use if needed
        const locationData = { lat, lng, accuracy };
        
        // Check if we should send immediately (first time or significant change)
        const shouldSendNow = !lastSentLocation || 
            calculateDistance(lastSentLocation.lat, lastSentLocation.lng, lat, lng) > 10; // 10m threshold
        
        if (shouldSendNow) {
            sendLocationToServer(lat, lng, true, accuracy);
            lastSentLocation = locationData;
        } else {
            // Throttle subsequent updates
            sendLocationThrottle = setTimeout(() => {
                sendLocationToServer(lat, lng, true, accuracy);
                lastSentLocation = locationData;
            }, 3000);
        }
    }
    
    // Send location to server
    function sendLocationToServer(lat, lng, isActive, accuracy = null) {
        if (!lat || !lng) return;
        
        fetch('{{ route("courier.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                latitude: lat,
                longitude: lng,
                is_gps_active: isActive,
                accuracy: accuracy
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('📤 Location sent to server');
            }
        })
        .catch(err => {
            console.error('❌ Server error:', err);
        });
    }
    
    // Calculate distance between two points (meters)
    function calculateDistance(lat1, lng1, lat2, lng2) {
        const R = 6371000; // Earth radius in meters
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
    
    // Show GPS permission modal
    function showGpsPermissionModal() {
        permissionModal.style.display = 'flex';
    }
    
    // Close GPS permission modal
    function closeGpsPermissionModal() {
        permissionModal.style.display = 'none';
    }
    
    // Request GPS permission
    function requestGpsPermission() {
        closeGpsPermissionModal();
        startGpsTracking();
    }
    
    // Show help when permission denied
    function showPermissionDeniedHelp() {
        if (toggleBtn) {
            toggleBtn.innerHTML = '🔓 Izinkan GPS';
            toggleBtn.classList.remove('btn-success', 'btn-danger');
            toggleBtn.classList.add('btn-warning');
            toggleBtn.onclick = function() {
                alert('GPS Permission Ditolak!\n\nUntuk mengaktifkan GPS:\n\n1. Klik ikon gembok/info di address bar browser\n2. Pilih "Site settings" atau "Pengaturan situs"\n3. Ubah "Location" menjadi "Allow"\n4. Refresh halaman ini');
            };
        }
    }
    
    // Cleanup when page unloads
    window.addEventListener('beforeunload', function() {
        if (gpsWatchId !== null) {
            navigator.geolocation.clearWatch(gpsWatchId);
            // Send final inactive status
            if (lastSentLocation) {
                sendLocationToServer(lastSentLocation.lat, lastSentLocation.lng, false);
            }
        }
    });
</script>
@endsection

