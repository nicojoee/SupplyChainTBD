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

    <!-- Nearby Pickup Locations -->
    @if(isset($nearbyLocations) && count($nearbyLocations) > 0)
    <div style="padding: 1rem; border-top: 1px solid var(--border-glass);">
        <h4 style="margin: 0 0 0.75rem 0; font-size: 1rem;">📍 Nearest Pickup Locations</h4>
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach($nearbyLocations as $index => $location)
            <div class="pickup-card" style="padding: 1rem; background: rgba(255,255,255,0.05); border-radius: 12px; border: 1px solid rgba(255,255,255,0.1);">
                <div style="display: flex; align-items: flex-start; gap: 0.75rem;">
                    <div style="width: 40px; height: 40px; border-radius: 50%; background: {{ $location['type'] == 'Supplier' ? '#22c55e' : '#f59e0b' }}; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.1rem; flex-shrink: 0;">
                        {{ $index + 1 }}
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                            <div>
                                <div style="font-weight: 600; font-size: 1rem;">{{ $location['name'] }}</div>
                                <div style="font-size: 0.8rem; color: {{ $location['type'] == 'Supplier' ? '#22c55e' : '#f59e0b' }};">
                                    {{ $location['type'] }}
                                </div>
                            </div>
                            <div style="text-align: right;">
                                <div style="font-size: 1.25rem; font-weight: 700; color: {{ $location['distance'] < 10 ? '#22c55e' : ($location['distance'] < 50 ? '#f59e0b' : '#ef4444') }};">
                                    {{ number_format($location['distance'], 1) }} km
                                </div>
                                @if($location['pending_orders'] > 0)
                                <div style="font-size: 0.75rem; background: #ef4444; color: white; padding: 2px 8px; border-radius: 10px; display: inline-block;">
                                    {{ $location['pending_orders'] }} pending
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div style="margin-top: 0.5rem; font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                            📍 {{ $location['address'] }}
                        </div>
                        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">
                            📞 {{ $location['phone'] }}
                        </div>
                        
                        <div style="display: flex; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
                            <button onclick="focusLocation({{ $location['latitude'] }}, {{ $location['longitude'] }})" 
                                    class="btn" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #3b82f6;">
                                🔍 Locate
                            </button>
                            <button onclick="showRoute({{ $location['latitude'] }}, {{ $location['longitude'] }}, '{{ addslashes($location['name']) }}')" 
                                    class="btn" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #8b5cf6;">
                                🗺️ Route
                            </button>
                            <a href="https://www.google.com/maps/dir/?api=1&origin={{ $courier->current_latitude }},{{ $courier->current_longitude }}&destination={{ $location['latitude'] }},{{ $location['longitude'] }}" 
                               target="_blank" class="btn" style="padding: 0.4rem 0.75rem; font-size: 0.8rem; background: #22c55e; text-decoration: none;">
                                🚗 Navigate
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div style="padding: 1rem; border-top: 1px solid var(--border-glass); text-align: center; color: rgba(255,255,255,0.5);">
        📍 Set your location to see nearby pickup points
    </div>
    @endif
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
    // Default center (Surabaya) if no location saved
    const DEFAULT_LAT = -7.2575;
    const DEFAULT_LNG = 112.7521;

    // Check if we have saved location
    let savedLat = {{ $courier->current_latitude ?? 'null' }};
    let savedLng = {{ $courier->current_longitude ?? 'null' }};
    
    // Track GPS status
    let isGpsAvailable = false;
    let hasLastKnownPosition = (savedLat && savedLng);
    
    let initialLat, initialLng;
    if (savedLat && savedLng) {
        // Use last known location from database
        initialLat = savedLat;
        initialLng = savedLng;
        console.log('📍 Using last known location:', initialLat, initialLng);
    } else {
        // No location yet - use Surabaya center as default view
        initialLat = DEFAULT_LAT;
        initialLng = DEFAULT_LNG;
        console.warn('⚠️ No location saved - GPS required to set position');
    }

    // Initialize map with aggressive performance optimizations
    const courierMap = L.map('courier-map', {
        center: [initialLat, initialLng],
        zoom: 15,
        preferCanvas: true,
        zoomControl: true,
        scrollWheelZoom: true,
        fadeAnimation: false,
        zoomAnimation: true,
        markerZoomAnimation: false,
        inertia: true,
        inertiaDeceleration: 2000,
        worldCopyJump: false
    });
    
    // Street - OpenStreetMap Standard (clearer fonts and road lines)
    const streetLayer = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 4
    });

    // Satellite - ESRI World Imagery + Roads with Labels
    const satelliteImagery = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 4
    });

    // Roads + Labels overlay (ESRI Transportation - includes road names)
    const roadsWithLabels = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Transportation/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 19,
        updateWhenIdle: true,
        updateWhenZooming: false,
        keepBuffer: 2,
        pane: 'overlayPane'
    });

    const satelliteLayer = L.layerGroup([satelliteImagery, roadsWithLabels]);

    // Add default layer
    streetLayer.addTo(courierMap);

    // Overlay - Administrative boundaries (sharp labels)
    const adminBoundaries = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager_only_labels/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; CARTO',
        subdomains: 'abcd',
        maxZoom: 20,
        updateWhenIdle: true
    });

    // Layer control - Simple and clean
    const baseLayers = {
        '🗺️ Street': streetLayer,
        '🛰️ Satellite': satelliteLayer
    };

    const overlayLayers = {
        '📍 Batas Wilayah': adminBoundaries
    };

    L.control.layers(baseLayers, overlayLayers, { position: 'topright' }).addTo(courierMap);

    // Courier marker
    const courierIcon = L.divIcon({
        className: 'custom-marker',
        html: '<div style="background: #22c55e; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid white; box-shadow: 0 4px 15px rgba(0,0,0,0.4);">🛵</div>',
        iconSize: [40, 40],
        iconAnchor: [20, 20]
    });

    let courierMarker = L.marker([initialLat, initialLng], { icon: courierIcon }).addTo(courierMap);
    courierMarker.bindPopup('<strong>📍 My Location</strong>').openPopup();

    // Add markers for nearby pickup locations
    @if(isset($nearbyLocations) && count($nearbyLocations) > 0)
    const nearbyLocations = @json($nearbyLocations);
    nearbyLocations.forEach((location, index) => {
        const markerColor = location.type === 'Supplier' ? '#22c55e' : '#f59e0b';
        const markerIcon = L.divIcon({
            className: 'custom-marker',
            html: `<div style="background: ${markerColor}; width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: bold; color: white; border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3);">${index + 1}</div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        const marker = L.marker([location.latitude, location.longitude], { icon: markerIcon }).addTo(courierMap);
        marker.bindPopup(`
            <div style="min-width: 150px;">
                <strong>${location.name}</strong><br>
                <span style="color: #888;">${location.type}</span><br>
                <span style="color: ${markerColor}; font-weight: 600;">${location.distance.toFixed(1)} km away</span>
            </div>
        `);
    });

    // Fit bounds to show all markers if we have locations
    if (nearbyLocations.length > 0) {
        const allPoints = [[initialLat, initialLng], ...nearbyLocations.map(l => [l.latitude, l.longitude])];
        const bounds = L.latLngBounds(allPoints);
        courierMap.fitBounds(bounds, { padding: [50, 50] });
    }
    @endif

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
            console.error('❌ GPS ERROR: Geolocation is not supported by this browser.');
            alert('Geolocation is not supported by this browser.');
            setGpsUnavailableStatus('Browser tidak support GPS');
            return;
        }

        isTracking = true;
        document.getElementById('toggle-tracking').innerHTML = '⏹️ Stop Tracking';
        document.getElementById('toggle-tracking').classList.remove('btn-success');
        document.getElementById('toggle-tracking').classList.add('btn-danger');
        document.getElementById('tracking-status').innerHTML = '🔄 Detecting GPS...';
        document.getElementById('tracking-status').classList.remove('badge-warning', 'badge-danger');
        document.getElementById('tracking-status').classList.add('badge-info');

        console.log('🔍 Attempting to detect GPS...');

        // Update immediately
        updateGPSLocation();

        // Update every 3 seconds
        trackingInterval = setInterval(updateGPSLocation, 3000);
    }

    // Function to set GPS unavailable status
    function setGpsUnavailableStatus(reason) {
        isGpsAvailable = false;
        isUsingRandomLocation = true;
        console.warn('⚠️ GPS NOT AVAILABLE:', reason);
        document.getElementById('tracking-status').innerHTML = '⚠️ GPS Tidak Aktif';
        document.getElementById('tracking-status').classList.remove('badge-success', 'badge-info');
        document.getElementById('tracking-status').classList.add('badge-warning');
    }

    // Function to set GPS active status
    function setGpsActiveStatus() {
        isGpsAvailable = true;
        isUsingRandomLocation = false;
        console.log('✅ GPS ACTIVE - Real location detected');
        document.getElementById('tracking-status').innerHTML = '🟢 GPS Aktif';
        document.getElementById('tracking-status').classList.remove('badge-warning', 'badge-info');
        document.getElementById('tracking-status').classList.add('badge-success');
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

                // GPS is working!
                setGpsActiveStatus();
                console.log('📍 GPS Location received:', lat.toFixed(6), lng.toFixed(6));

                // Update map
                courierMarker.setLatLng([lat, lng]);
                courierMap.panTo([lat, lng]);

                // Update display
                document.getElementById('current-coords').textContent = lat.toFixed(6) + ', ' + lng.toFixed(6);

                // Send to server with GPS active = true
                sendLocationToServer(lat, lng, true);
            },
            function(error) {
                // GPS failed - log the specific error
                console.error('❌ GPS ERROR:', error.code, '-', error.message);
                
                let errorReason = 'Unknown error';
                switch(error.code) {
                    case error.PERMISSION_DENIED:
                        errorReason = 'User denied GPS permission';
                        console.warn('⚠️ GPS PERMISSION DENIED by user');
                        break;
                    case error.POSITION_UNAVAILABLE:
                        errorReason = 'Location information unavailable';
                        console.warn('⚠️ GPS POSITION UNAVAILABLE');
                        break;
                    case error.TIMEOUT:
                        errorReason = 'GPS request timeout';
                        console.warn('⚠️ GPS TIMEOUT');
                        break;
                }
                
                setGpsUnavailableStatus(errorReason);
                
                // If we already have a saved location, keep using it (don't generate new random)
                if (savedLat && savedLng) {
                    console.log('📍 GPS failed but keeping saved location');
                    document.getElementById('current-coords').textContent = savedLat.toFixed(6) + ', ' + savedLng.toFixed(6) + ' (Tersimpan)';
                    // Don't send new location, keep using the saved one
                } else {
                    // No saved location exists, this shouldn't happen normally 
                    // because we save on first login, but handle just in case
                    console.log('🎲 Fallback: Using current map position');
                    const currentPos = courierMarker.getLatLng();
                    document.getElementById('current-coords').textContent = currentPos.lat.toFixed(6) + ', ' + currentPos.lng.toFixed(6) + ' (Default)';
                }
            },
            {
                enableHighAccuracy: true,
                timeout: 5000,
                maximumAge: 0
            }
        );
    }

    function sendLocationToServer(lat, lng, isRealGps = false) {
        console.log('📤 Sending location to server:', lat.toFixed(6), lng.toFixed(6), '| GPS Active:', isRealGps);
        
        fetch('{{ route("courier.location") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ 
                latitude: lat, 
                longitude: lng,
                is_gps_active: isRealGps
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (isRealGps) {
                    document.getElementById('tracking-status').innerHTML = '🟢 GPS Aktif';
                    document.getElementById('tracking-status').classList.remove('badge-warning');
                    document.getElementById('tracking-status').classList.add('badge-success');
                }
                // Update saved coordinates
                savedLat = lat;
                savedLng = lng;
                hasLastKnownPosition = true;
            }
        })
        .catch(err => {
            console.error('Server Error:', err);
        });
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
        console.log('========================================');
        console.log('🛵 COURIER GPS STATUS CHECK');
        console.log('========================================');
        
        // Check current status
        if (hasLastKnownPosition) {
            console.log('📍 Last known position available:', savedLat, savedLng);
            document.getElementById('tracking-status').innerHTML = '📍 Posisi Terakhir';
            document.getElementById('tracking-status').classList.add('badge-info');
        } else {
            console.warn('⚠️ No position saved - GPS required');
            document.getElementById('tracking-status').innerHTML = '⚠️ Butuh GPS';
            document.getElementById('tracking-status').classList.add('badge-warning');
        }
        
        // Request permission and start tracking automatically
        if (navigator.geolocation) {
            console.log('🔍 Checking GPS availability...');
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    // GPS permission granted and working
                    console.log('✅ GPS PERMISSION GRANTED');
                    console.log('📍 GPS position:', position.coords.latitude.toFixed(6), position.coords.longitude.toFixed(6));
                    startTracking();
                },
                function(error) {
                    // GPS failed on page load
                    console.error('❌ GPS FAILED ON PAGE LOAD');
                    console.error('   Error Code:', error.code);
                    console.error('   Error Message:', error.message);
                    
                    let errorReason = '';
                    switch(error.code) {
                        case error.PERMISSION_DENIED:
                            errorReason = 'Permission Denied';
                            console.warn('⚠️ User DENIED GPS permission');
                            break;
                        case error.POSITION_UNAVAILABLE:
                            errorReason = 'Position Unavailable';
                            console.warn('⚠️ GPS position not available');
                            break;
                        case error.TIMEOUT:
                            errorReason = 'Timeout';
                            console.warn('⚠️ GPS request timed out');
                            break;
                        default:
                            errorReason = 'Unknown Error';
                    }
                    
                    setGpsUnavailableStatus(errorReason);
                    
                    // Show last known position if available
                    if (hasLastKnownPosition) {
                        console.log('📍 Showing last known position');
                        document.getElementById('current-coords').textContent = savedLat.toFixed(6) + ', ' + savedLng.toFixed(6) + ' (Terakhir)';
                    } else {
                        document.getElementById('current-coords').textContent = 'Tidak ada posisi - Aktifkan GPS';
                    }
                    
                    console.log('========================================');
                }
            );
        } else {
            console.error('❌ BROWSER DOES NOT SUPPORT GEOLOCATION');
            document.getElementById('tracking-status').innerHTML = '❌ GPS Tidak Didukung';
            document.getElementById('tracking-status').classList.add('badge-danger');
        }
    });

    // Focus on a specific location
    function focusLocation(lat, lng) {
        if (courierMap) {
            courierMap.setView([lat, lng], 15);
            // Add a temporary highlight marker
            L.circle([lat, lng], {
                color: '#6366f1',
                fillColor: '#6366f1',
                fillOpacity: 0.3,
                radius: 500
            }).addTo(courierMap).on('click', function() {
                this.remove();
            });
        }
    }

    // Current route line
    let routeLine = null;
    let routeDestMarker = null;

    // Show route from courier to destination
    function showRoute(destLat, destLng, destName) {
        if (!courierMap) return;

        // Remove existing route
        if (routeLine) {
            courierMap.removeLayer(routeLine);
        }
        if (routeDestMarker) {
            courierMap.removeLayer(routeDestMarker);
        }

        // Get courier current position
        const courierLat = {{ $courier->current_latitude ?? '-6.2088' }};
        const courierLng = {{ $courier->current_longitude ?? '106.8456' }};

        // Draw route line
        routeLine = L.polyline([
            [courierLat, courierLng],
            [destLat, destLng]
        ], {
            color: '#6366f1',
            weight: 4,
            opacity: 0.8,
            dashArray: '10, 10'
        }).addTo(courierMap);

        // Add destination marker with popup
        const destIcon = L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #ef4444; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 18px; border: 3px solid white; box-shadow: 0 3px 10px rgba(0,0,0,0.3);">📍</div>',
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });

        routeDestMarker = L.marker([destLat, destLng], { icon: destIcon }).addTo(courierMap);
        routeDestMarker.bindPopup(`
            <div style="min-width: 150px;">
                <strong>Destination:</strong><br>
                ${destName}<br>
                <button onclick="clearRoute()" style="margin-top: 8px; padding: 4px 10px; background: #ef4444; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Clear Route
                </button>
            </div>
        `).openPopup();

        // Fit bounds to show route
        courierMap.fitBounds([[courierLat, courierLng], [destLat, destLng]], { padding: [50, 50] });

        // Calculate distance
        const distance = calculateHaversineDistance(courierLat, courierLng, destLat, destLng);
        alert(`Route to ${destName}\nDistance: ${distance.toFixed(1)} km\n\nClick "Navigate" to open Google Maps for turn-by-turn directions.`);
    }

    // Clear route
    function clearRoute() {
        if (routeLine) {
            courierMap.removeLayer(routeLine);
            routeLine = null;
        }
        if (routeDestMarker) {
            courierMap.removeLayer(routeDestMarker);
            routeDestMarker = null;
        }
    }

    // Calculate distance (Haversine)
    function calculateHaversineDistance(lat1, lng1, lat2, lng2) {
        const R = 6371; // km
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLng = (lng2 - lng1) * Math.PI / 180;
        const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                  Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLng/2) * Math.sin(dLng/2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        return R * c;
    }
</script>
@endsection
