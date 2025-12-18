@extends('layouts.app')

@section('title', 'Dashboard')

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
        height: calc(100vh - 60px) !important;
    }
    #map-card:fullscreen #map {
        height: 100% !important;
        width: 100% !important;
    }
    
    /* Self-marker pulsing animation */
    @keyframes selfPulse {
        0% {
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 4px 15px rgba(0,0,0,0.4);
            transform: scale(1);
        }
        50% {
            box-shadow: 0 0 35px rgba(255, 255, 255, 0.9), 0 4px 20px rgba(0,0,0,0.5);
            transform: scale(1.08);
        }
        100% {
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.6), 0 4px 15px rgba(0,0,0,0.4);
            transform: scale(1);
        }
    }
    
    .self-marker {
        z-index: 1000 !important;
    }
    
    .self-marker-inner {
        animation: selfPulse 2s infinite ease-in-out;
    }
    
    /* Self-marker legend indicator */
    .self-legend {
        position: absolute;
        bottom: 20px;
        left: 20px;
        background: rgba(30, 27, 75, 0.95);
        backdrop-filter: blur(10px);
        padding: 12px 16px;
        border-radius: 12px;
        border: 1px solid rgba(255, 255, 255, 0.2);
        z-index: 1000;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.85rem;
        color: #fff;
    }
    
    .self-legend-dot {
        width: 16px;
        height: 16px;
        border-radius: 50%;
        background: linear-gradient(135deg, #ec4899, #8b5cf6);
        animation: selfPulse 2s infinite ease-in-out;
    }
</style>
@endsection

@section('content')
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon supplier">📦</div>
        <div>
            <div class="stat-value">{{ $suppliers->count() }}</div>
            <div class="stat-label">Suppliers</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon factory">🏭</div>
        <div>
            <div class="stat-value">{{ $factories->count() }}</div>
            <div class="stat-label">Factories</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon distributor">🚚</div>
        <div>
            <div class="stat-value">{{ $distributors->count() }}</div>
            <div class="stat-label">Distributors</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon courier">🛵</div>
        <div>
            <div class="stat-value">{{ $couriers->count() }}</div>
            <div class="stat-label">Couriers</div>
        </div>
    </div>
</div>

<div class="card" id="map-card">
    <div class="card-header">
        <h2 class="card-title">Supply Chain Map</h2>
        <button onclick="toggleMapFullscreen()" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;" id="fullscreen-btn">
            ⛶ Fullscreen
        </button>
    </div>
    <div class="map-container">
        <div id="map"></div>
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">📦 Suppliers & Products</h2>
        </div>
        <div id="suppliers-list">
            @include('partials.suppliers-list', ['suppliers' => $suppliers])
        </div>
        @if($suppliers->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="suppliers-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $suppliers->firstItem() }}-{{ $suppliers->lastItem() }} of {{ $suppliers->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('suppliers', 'prev')" id="suppliers-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $suppliers->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('suppliers', 'next')" id="suppliers-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$suppliers->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🏭 Factories & Products</h2>
        </div>
        <div id="factories-list">
            @include('partials.factories-list', ['factories' => $factories])
        </div>
        @if($factories->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="factories-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $factories->firstItem() }}-{{ $factories->lastItem() }} of {{ $factories->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('factories', 'prev')" id="factories-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $factories->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('factories', 'next')" id="factories-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$factories->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>
</div>

<div class="grid-2">
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🚚 Distributor Stock</h2>
        </div>
        <div id="distributors-list">
            @include('partials.distributors-list', ['distributors' => $distributors])
        </div>
        @if($distributors->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="distributors-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $distributors->firstItem() }}-{{ $distributors->lastItem() }} of {{ $distributors->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('distributors', 'prev')" id="distributors-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $distributors->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('distributors', 'next')" id="distributors-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$distributors->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">🛵 Courier Status</h2>
        </div>
        <div id="couriers-list">
            @include('partials.couriers-list', ['couriers' => $couriers])
        </div>
        @if($couriers->total() > 3)
        <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.75rem; border-top: 1px solid var(--border-glass);">
            <span id="couriers-info" style="color: rgba(255,255,255,0.4); font-size: 0.8rem;">{{ $couriers->firstItem() }}-{{ $couriers->lastItem() }} of {{ $couriers->total() }}</span>
            <div style="display: flex; gap: 0.25rem;">
                <button onclick="loadPage('couriers', 'prev')" id="couriers-prev" class="btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; background: rgba(255,255,255,0.1);" {{ $couriers->onFirstPage() ? 'disabled' : '' }}>←</button>
                <button onclick="loadPage('couriers', 'next')" id="couriers-next" class="btn btn-primary" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;" {{ !$couriers->hasMorePages() ? 'disabled' : '' }}>→</button>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Fullscreen toggle for map
    function toggleMapFullscreen() {
        const mapCard = document.getElementById('map-card');
        const btn = document.getElementById('fullscreen-btn');
        
        if (!document.fullscreenElement) {
            mapCard.requestFullscreen().then(() => {
                btn.innerHTML = '✕ Exit Fullscreen';
                mapCard.style.borderRadius = '0';
                setTimeout(() => map.invalidateSize(), 100);
            }).catch(err => {
                console.error('Fullscreen error:', err);
            });
        } else {
            document.exitFullscreen().then(() => {
                btn.innerHTML = '⛶ Fullscreen';
                mapCard.style.borderRadius = '';
                setTimeout(() => map.invalidateSize(), 100);
            });
        }
    }

    // Listen for fullscreen change (ESC key exit)
    document.addEventListener('fullscreenchange', () => {
        const btn = document.getElementById('fullscreen-btn');
        const mapCard = document.getElementById('map-card');
        if (!document.fullscreenElement && btn) {
            btn.innerHTML = '⛶ Fullscreen';
            mapCard.style.borderRadius = '';
            setTimeout(() => map.invalidateSize(), 100);
        }
    });

    // Initialize map centered on Indonesia
    const map = L.map('map').setView([-2.5, 118], 5);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Self entity data from server
    const selfEntity = @json($selfEntity);

    // Custom icons
    const icons = {
        supplier: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #22c55e; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">📦</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        factory: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #f59e0b; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🏭</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        distributor: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #6366f1; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🚚</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        courierIdle: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #22c55e; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🛵</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        courierBusy: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background: #ef4444; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">🛵</div>',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        }),
        // Special SELF icons - larger with pulsing glow effect
        selfSupplier: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #22c55e, #16a34a); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(34, 197, 94, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">📦</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfFactory: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #f59e0b, #d97706); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(245, 158, 11, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🏭</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfDistributor: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #6366f1, #4f46e5); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(99, 102, 241, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🚚</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        }),
        selfCourier: L.divIcon({
            className: 'custom-marker self-marker',
            html: '<div class="self-marker-inner" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 4px solid #ffffff; box-shadow: 0 0 20px rgba(14, 165, 233, 0.8), 0 4px 15px rgba(0,0,0,0.4); animation: selfPulse 2s infinite;">🛵</div>',
            iconSize: [45, 45],
            iconAnchor: [22, 22]
        })
    };

    // Fetch and display markers
    let selfMarker = null;
    
    fetch('{{ route("api.map-data") }}')
        .then(response => response.json())
        .then(data => {
            data.features.forEach(feature => {
                const coords = feature.geometry.coordinates;
                const props = feature.properties;
                
                // Check if this entity is the self (logged-in user's) entity
                // Using == for ID comparison to handle type coercion (string vs number)
                const isSelf = selfEntity && 
                               selfEntity.type === props.type && 
                               selfEntity.id == props.id;
                
                // Debug logging for self detection
                if (selfEntity && selfEntity.type === props.type) {
                    console.log('Checking entity:', props.type, 'props.id:', props.id, '(type:', typeof props.id, ') selfEntity.id:', selfEntity.id, '(type:', typeof selfEntity.id, ') isSelf:', isSelf);
                }
                
                let icon = icons[props.type] || icons.supplier;

                // Use special self icon if this is the logged-in user's entity
                if (isSelf) {
                    const selfIconKey = 'self' + props.type.charAt(0).toUpperCase() + props.type.slice(1);
                    icon = icons[selfIconKey] || icon;
                } else if (props.type === 'courier') {
                    icon = (props.status === 'idle') ? icons.courierIdle : icons.courierBusy;
                }

                let popupContent = `
                    <div class="popup-title">${props.name}${isSelf ? ' <span style="background: linear-gradient(135deg, #ec4899, #8b5cf6); padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; margin-left: 6px;">YOU</span>' : ''}</div>
                    <span class="popup-type ${props.type}">${props.type}</span>
                `;

                if (props.address) {
                    popupContent += `<div class="popup-info">📍 ${props.address}</div>`;
                }
                if (props.phone) {
                    popupContent += `<div class="popup-info">📞 ${props.phone}</div>`;
                }
                // Show email only for self marker (from user account)
                if (isSelf && selfEntity && selfEntity.email) {
                    console.log('Adding email to popup:', selfEntity.email);
                    popupContent += `<div class="popup-info">✉️ ${selfEntity.email}</div>`;
                }
                if (props.capacity) {
                    popupContent += `<div class="popup-info">📊 Capacity: ${props.capacity}</div>`;
                }
                if (props.status) {
                    popupContent += `<div class="popup-info">Status: ${props.status}</div>`;
                }

                // Products/Stocks
                if (props.products && props.products.length > 0) {
                    popupContent += '<div class="popup-products"><strong>Products:</strong>';
                    props.products.forEach(p => {
                        popupContent += `<div class="popup-product">• ${p.name} - $${p.price}</div>`;
                    });
                    popupContent += '</div>';
                }
                if (props.stocks && props.stocks.length > 0) {
                    popupContent += '<div class="popup-products"><strong>Stock:</strong>';
                    props.stocks.forEach(s => {
                        popupContent += `<div class="popup-product">• ${s.name}: ${s.quantity} pcs</div>`;
                    });
                    popupContent += '</div>';
                }

                const marker = L.marker([coords[1], coords[0]], { icon })
                    .addTo(map)
                    .bindPopup(popupContent);
                
                // Store self marker for auto-zoom
                if (isSelf) {
                    selfMarker = marker;
                }
            });

            // Debug: Log selfEntity data
            console.log('Self Entity Data:', selfEntity);

            // Auto-zoom to self entity if exists with valid coordinates, otherwise fit all bounds
            if (selfEntity && selfEntity.latitude && selfEntity.longitude && 
                selfEntity.latitude !== 0 && selfEntity.longitude !== 0) {
                console.log('Auto-zooming to self location:', selfEntity.latitude, selfEntity.longitude);
                map.setView([selfEntity.latitude, selfEntity.longitude], 14);
                // Open popup for self marker after a short delay
                if (selfMarker) {
                    setTimeout(() => selfMarker.openPopup(), 500);
                }
            } else if (data.features.length > 0) {
                const group = L.featureGroup(
                    data.features.map(f => L.marker([f.geometry.coordinates[1], f.geometry.coordinates[0]]))
                );
                map.fitBounds(group.getBounds().pad(0.1));
            }
        })
        .catch(err => console.error('Error loading map data:', err));

    // Click on map to add new entity
    @if(auth()->user()->role === 'superadmin')
    let clickMarker = null;

    map.on('click', function(e) {
        const lat = e.latlng.lat.toFixed(8);
        const lng = e.latlng.lng.toFixed(8);

        // Remove previous click marker
        if (clickMarker) {
            map.removeLayer(clickMarker);
        }

        // Create popup content with entity options (only fixed location entities)
        const popupContent = `
            <div style="text-align: center; min-width: 200px;">
                <div style="font-weight: 600; margin-bottom: 10px; color: #333;">📍 Add New Entity</div>
                <div style="font-size: 0.85rem; color: #666; margin-bottom: 15px;">
                    Lat: ${lat}<br>Lng: ${lng}
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <a href="{{ route('superadmin.add.supplier') }}?lat=${lat}&lng=${lng}" 
                       style="background: #22c55e; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        📦 Add Supplier
                    </a>
                    <a href="{{ route('superadmin.add.factory') }}?lat=${lat}&lng=${lng}" 
                       style="background: #f59e0b; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        🏭 Add Factory
                    </a>
                    <a href="{{ route('superadmin.add.distributor') }}?lat=${lat}&lng=${lng}" 
                       style="background: #6366f1; color: white; padding: 10px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px;">
                        🚚 Add Distributor
                    </a>
                </div>
            </div>
        `;

        // Add a temporary marker with popup
        clickMarker = L.marker([e.latlng.lat, e.latlng.lng], {
            icon: L.divIcon({
                className: 'click-marker',
                html: '<div style="background: #ec4899; width: 24px; height: 24px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 12px; border: 3px solid white; box-shadow: 0 2px 10px rgba(0,0,0,0.3);">➕</div>',
                iconSize: [24, 24],
                iconAnchor: [12, 12]
            })
        }).addTo(map);

        clickMarker.bindPopup(popupContent, { maxWidth: 300 }).openPopup();
    });
    @endif

    // AJAX Pagination for dashboard panels
    const pageState = {
        suppliers: {{ $suppliers->currentPage() }},
        factories: {{ $factories->currentPage() }},
        distributors: {{ $distributors->currentPage() }},
        couriers: {{ $couriers->currentPage() }}
    };

    const totalPages = {
        suppliers: {{ $suppliers->lastPage() }},
        factories: {{ $factories->lastPage() }},
        distributors: {{ $distributors->lastPage() }},
        couriers: {{ $couriers->lastPage() }}
    };

    function loadPage(type, direction) {
        const currentPage = pageState[type];
        let newPage = direction === 'next' ? currentPage + 1 : currentPage - 1;
        
        if (newPage < 1 || newPage > totalPages[type]) return;

        const urls = {
            suppliers: '{{ route("api.suppliers") }}',
            factories: '{{ route("api.factories") }}',
            distributors: '{{ route("api.distributors") }}',
            couriers: '{{ route("api.couriers") }}'
        };

        fetch(`${urls[type]}?page=${newPage}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById(`${type}-list`).innerHTML = data.html;
                pageState[type] = data.current_page;
                
                // Update info text
                const firstItem = (data.current_page - 1) * 3 + 1;
                const lastItem = Math.min(data.current_page * 3, data.total);
                document.getElementById(`${type}-info`).textContent = `${firstItem}-${lastItem} of ${data.total}`;
                
                // Update button states
                document.getElementById(`${type}-prev`).disabled = data.current_page <= 1;
                document.getElementById(`${type}-next`).disabled = data.current_page >= data.last_page;
            })
            .catch(error => console.error('Error loading page:', error));
    }

    // Store courier markers for updating
    let courierMarkers = {};

    // Function to refresh courier positions every 5 seconds
    function refreshCourierPositions() {
        fetch('{{ route("api.map-data") }}')
            .then(response => response.json())
            .then(data => {
                data.features.forEach(feature => {
                    if (feature.properties.type === 'courier') {
                        const coords = feature.geometry.coordinates;
                        const props = feature.properties;
                        const courierId = props.id;

                        // Create icon based on status
                        const icon = (props.status === 'idle') ? icons.courierIdle : icons.courierBusy;

                        // Build popup
                        let popupContent = `
                            <div class="popup-title">${props.name}</div>
                            <span class="popup-type courier">courier</span>
                            <div class="popup-info">🛵 ${props.vehicle || 'Vehicle N/A'}</div>
                            <div class="popup-info">📞 ${props.phone || 'N/A'}</div>
                            <div class="popup-info">Status: <strong>${props.status}</strong></div>
                        `;

                        if (courierMarkers[courierId]) {
                            // Update existing marker position
                            courierMarkers[courierId].setLatLng([coords[1], coords[0]]);
                            courierMarkers[courierId].setPopupContent(popupContent);
                        } else {
                            // Create new marker
                            courierMarkers[courierId] = L.marker([coords[1], coords[0]], { icon })
                                .addTo(map)
                                .bindPopup(popupContent);
                        }
                    }
                });
            })
            .catch(err => console.error('Error refreshing couriers:', err));
    }

    // Refresh courier positions every 5 seconds
    setInterval(refreshCourierPositions, 5000);
</script>
@endsection

