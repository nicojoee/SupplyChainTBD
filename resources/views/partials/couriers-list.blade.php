@foreach($couriers as $courier)
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 0.75rem;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $courier->name }}</div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">
            {{ $courier->vehicle_type ?? 'Vehicle N/A' }} • {{ $courier->license_plate ?? 'No plate' }}
        </div>
        <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : 'badge-danger' }}">
            {{ ucfirst($courier->status) }}
        </span>
    </div>
@endforeach
@if($couriers->count() == 0)
    <p style="color: rgba(255,255,255,0.5);">No couriers registered yet.</p>
@endif
