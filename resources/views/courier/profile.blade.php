@extends('layouts.app')

@section('title', 'Vehicle Profile')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚚 My Vehicle Profile</h2>
    </div>
    
    <form action="{{ route('courier.profile.update') }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" required 
                       value="{{ old('name', $courier->name) }}" placeholder="Your name">
            </div>
            <div class="form-group">
                <label class="form-label">Truck Type (by Capacity) *</label>
                <select name="vehicle_type" class="form-control" required>
                    <option value="">Select truck type...</option>
                    <option value="small_15" {{ ($courier->vehicle_capacity == 15) ? 'selected' : '' }}>🚛 Small Truck - 15 Ton</option>
                    <option value="medium_20" {{ ($courier->vehicle_capacity == 20) ? 'selected' : '' }}>🚛 Medium Truck - 20 Ton</option>
                    <option value="large_30" {{ ($courier->vehicle_capacity == 30) ? 'selected' : '' }}>🚛 Large Truck - 30 Ton</option>
                </select>
                <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.5rem;">
                    Larger trucks can carry more per trip. Orders exceeding capacity will be split among multiple couriers.
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">License Plate</label>
                <input type="text" name="license_plate" class="form-control" 
                       value="{{ old('license_plate', $courier->license_plate) }}" placeholder="B 1234 XYZ">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number *</label>
                <input type="text" name="phone" class="form-control" required
                       value="{{ old('phone', $courier->phone) }}" placeholder="+62...">
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">
            💾 Save Changes
        </button>
    </form>
</div>

<!-- Current Vehicle Info Card -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📋 Current Vehicle Information</h2>
    </div>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; padding: 0.5rem 0;">
        <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">Courier Name</div>
            <div style="font-size: 1.1rem; font-weight: 600;">{{ $courier->name }}</div>
        </div>
        <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">Truck Type</div>
            <div style="font-size: 1.1rem; font-weight: 600;">{{ $courier->vehicle_type ?? 'Not Set' }}</div>
        </div>
        <div style="padding: 1rem; background: rgba(34, 197, 94, 0.1); border-radius: 12px; border: 1px solid rgba(34, 197, 94, 0.3);">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">Capacity</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #22c55e;">{{ $courier->vehicle_capacity ?? 'N/A' }} Ton / Trip</div>
        </div>
        <div style="padding: 1rem; background: rgba(99, 102, 241, 0.1); border-radius: 12px; border: 1px solid rgba(99, 102, 241, 0.3);">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">License Plate</div>
            <div style="font-size: 1.25rem; font-weight: 700; color: #818cf8;">{{ $courier->license_plate ?? 'Not Set' }}</div>
        </div>
        <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">Phone Number</div>
            <div style="font-size: 1.1rem; font-weight: 600;">{{ $courier->phone ?? 'Not Set' }}</div>
        </div>
        <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 12px;">
            <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; margin-bottom: 0.5rem;">Current Status</div>
            <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : ($courier->status === 'busy' ? 'badge-warning' : 'badge-danger') }}">
                {{ ucfirst($courier->status) }}
            </span>
        </div>
    </div>
</div>

<!-- All Registered Vehicles -->
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚛 All Registered Vehicles</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Courier</th>
                <th>Truck Type</th>
                <th>Capacity</th>
                <th>License Plate</th>
                <th>Phone</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($allCouriers as $c)
            <tr style="{{ $c->id === $courier->id ? 'background: rgba(99, 102, 241, 0.1);' : '' }}">
                <td>
                    <strong>{{ $c->name }}</strong>
                    @if($c->id === $courier->id)
                        <span class="badge badge-info" style="margin-left: 0.5rem;">You</span>
                    @endif
                </td>
                <td>{{ $c->vehicle_type ?? 'Not Set' }}</td>
                <td><span style="color: #22c55e; font-weight: 600;">{{ $c->vehicle_capacity ?? 'N/A' }} Ton</span></td>
                <td style="font-weight: 600; color: #818cf8;">{{ $c->license_plate ?? '-' }}</td>
                <td>{{ $c->phone ?? '-' }}</td>
                <td>
                    <span class="badge {{ $c->status === 'idle' ? 'badge-success' : ($c->status === 'busy' ? 'badge-warning' : 'badge-danger') }}">
                        {{ ucfirst($c->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align: center; color: rgba(255,255,255,0.5);">No couriers registered yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
