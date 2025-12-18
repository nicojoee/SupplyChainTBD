@extends('layouts.app')

@section('title', 'Courier Dashboard')

@section('styles')
<style>
    #map-card:fullscreen {
        width: 100vw !important;
        height: 100vh !important;
        max-width: 100vw !important;
        max-height: 100vh !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
        display: flex;
        flex-direction: column;
    }
    #map-card:fullscreen .card-header {
        flex-shrink: 0;
        border-radius: 0;
    }
    #map-card:fullscreen .map-container {
        flex: 1;
        height: calc(100vh - 120px) !important;
    }
    #map-card:fullscreen #courier-map {
        height: 100% !important;
        width: 100% !important;
        border-radius: 0 !important;
    }
</style>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">My Courier Profile</h2>
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

<div class="card" id="map-card">
    <div class="card-header">
        <h2 class="card-title">📍 My Live Location</h2>
        <div style="display: flex; align-items: center; gap: 1rem;">
            <span id="tracking-status" class="badge badge-success">🔴 Starting...</span>
            <button onclick="toggleMapFullscreen()" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;" id="fullscreen-btn">⛶ Fullscreen</button>
        </div>
    </div>
    <div class="map-container" style="height: 350px;">
        <div id="courier-map" style="height: 100%; width: 100%; border-radius: 0 0 12px 12px;"></div>
    </div>
    <div style="padding: 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
        <div>
            <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">Current Position:</span>
            <span id="current-coords" style="font-family: monospace; margin-left: 0.5rem;">
                {{ $courier->current_latitude ?? 'Not set' }}, {{ $courier->current_longitude ?? 'Not set' }}
            </span>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <button type="button" class="btn btn-success" id="toggle-tracking" onclick="toggleTracking()">
                ▶️ Start GPS
            </button>
        </div>
    </div>
    <!-- Manual location input (fallback for HTTP) -->
    <div style="padding: 1rem; border-top: 1px solid var(--border-glass);">
        <div style="color: rgba(255,255,255,0.6); font-size: 0.85rem; margin-bottom: 0.5rem;">
            ⚠️ GPS requires HTTPS. For local testing, enter coordinates manually:
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <input type="number" id="manual-lat" class="form-control" step="0.000001" placeholder="Latitude" style="flex: 1; min-width: 120px;" value="{{ $courier->current_latitude ?? '-6.2088' }}">
            <input type="number" id="manual-lng" class="form-control" step="0.000001" placeholder="Longitude" style="flex: 1; min-width: 120px;" value="{{ $courier->current_longitude ?? '106.8456' }}">
            <button type="button" class="btn btn-primary" onclick="setManualLocation()">📍 Set Location</button>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Assigned Deliveries</h2>
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
        <p style="color: rgba(255,255,255,0.5);">No assigned deliveries.</p>
    @endif
</div>

<div class="card">
    <div class="card-header">
        <h2 class="card-title">Completed Deliveries</h2>
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
        <p style="color: rgba(255,255,255,0.5);">No completed deliveries yet.</p>
    @endif
</div>
@endsection

@section('scripts')
<script>
    // Initialize map
    const initialLat = {{ $courier->current_latitude ?? -2.5 }};
    const initialLng = {{ $courier->current_longitude ?? 118 }};
    const courierMap = L.map('courier-map').setView([initialLat, initialLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(courierMap);

    // Courier marker
    const courierIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #22c55e; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">🛵</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    let courierMarker = L.marker([initialLat, initialLng], { icon: courierIcon }).addTo(courierMap);
    courierMarker.bindPopup('<strong>📍 My Location</strong>').openPopup();

    // Tracking state
    let trackingInterval = null;
    let isTracking = false;

    function toggleTracking() {
        if (isTracking) {
            stopTracking();
        } else {
            startTracking();
        }
    }

    function startTracking() {
        if (!navigator.geolocation) {
            alert('Geolocation is not supported by this browser.');
            return;
        }

        isTracking = true;
        document.getElementById('toggle-tracking').innerHTML = '⏹️ Stop Tracking';
        document.getElementById('toggle-tracking').classList.remove('btn-success');
        document.getElementById('toggle-tracking').classList.add('btn-danger');
        document.getElementById('tracking-status').innerHTML = '🟢 Live Tracking';
        document.getElementById('tracking-status').classList.remove('badge-warning');
        document.getElementById('tracking-status').classList.add('badge-success');

        // Update immediately
        updateGPSLocation();

        // Update every 3 seconds
        trackingInterval = setInterval(updateGPSLocation, 3000);
    }

    function stopTracking() {
        isTracking = false;
        if (trackingInterval) {
            clearInterval(trackingInterval);
            trackingInterval = null;
        }
        document.getElementById('toggle-tracking').innerHTML = '▶️ Start Tracking';
        document.getElementById('toggle-tracking').classList.remove('btn-danger');
        document.getElementById('toggle-tracking').classList.add('btn-success');
        document.getElementById('tracking-status').innerHTML = '⏸️ Paused';
        document.getElementById('tracking-status').classList.remove('badge-success');
        document.getElementById('tracking-status').classList.add('badge-warning');
    }

    function updateGPSLocation() {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Update map
                courierMarker.setLatLng([lat, lng]);
                courierMap.panTo([lat, lng]);

                // Update display
                document.getElementById('current-coords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

                // Send to server
                sendLocationToServer(lat, lng);
            },
            function(error) {
                console.error('GPS Error:', error.message);
                document.getElementById('tracking-status').innerHTML = '⚠️ GPS Error';
                document.getElementById('tracking-status').classList.remove('badge-success');
                document.getElementById('tracking-status').classList.add('badge-warning');
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }

    function sendLocationToServer(lat, lng) {
        fetch('{{ route("courier.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ latitude: lat, longitude: lng })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('tracking-status').innerHTML = '🟢 Live • Updated';
            }
        })
        .catch(err => {
            console.error('Server Error:', err);
        });
    }

    // Manual location setter (for HTTP where GPS doesn't work)
    function setManualLocation() {
        const lat = parseFloat(document.getElementById('manual-lat').value);
        const lng = parseFloat(document.getElementById('manual-lng').value);
        
        if (isNaN(lat) || isNaN(lng)) {
            alert('Please enter valid latitude and longitude values');
            return;
        }

        // Update map
        courierMarker.setLatLng([lat, lng]);
        courierMap.panTo([lat, lng]);
        
        // Update display
        document.getElementById('current-coords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);
        
        // Send to server
        sendLocationToServer(lat, lng);
        
        document.getElementById('tracking-status').innerHTML = '📍 Location Set';
        document.getElementById('tracking-status').classList.remove('badge-warning');
        document.getElementById('tracking-status').classList.add('badge-success');
    }

    // Fullscreen toggle
    function toggleMapFullscreen() {
        const mapCard = document.getElementById('map-card');
        const btn = document.getElementById('fullscreen-btn');
        
        if (!document.fullscreenElement) {
            mapCard.requestFullscreen().then(() => {
                btn.innerHTML = '✕ Exit';
                mapCard.style.borderRadius = '0';
                setTimeout(() => courierMap.invalidateSize(), 100);
            });
        } else {
            document.exitFullscreen().then(() => {
                btn.innerHTML = '⛶ Fullscreen';
                mapCard.style.borderRadius = '';
                setTimeout(() => courierMap.invalidateSize(), 100);
            });
        }
    }

    document.addEventListener('fullscreenchange', () => {
        const btn = document.getElementById('fullscreen-btn');
        const mapCard = document.getElementById('map-card');
        if (!document.fullscreenElement && btn) {
            btn.innerHTML = '⛶ Fullscreen';
            mapCard.style.borderRadius = '';
            setTimeout(() => courierMap.invalidateSize(), 100);
        }
    });

    // Auto-start tracking when page loads
    document.addEventListener('DOMContentLoaded', function() {
        // Request permission and start tracking automatically
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // Got permission, start tracking
                    startTracking();
                },
                function(error) {
                    document.getElementById('tracking-status').innerHTML = '⚠️ Enable GPS';
                    document.getElementById('tracking-status').classList.add('badge-warning');
                }
            );
        }
    });
</script>
@endsection
