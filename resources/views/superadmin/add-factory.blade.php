@extends('layouts.app')

@section('title', 'Add Factory')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">🏭 Add Factory</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">
        Create a new factory with location.
    </p>

    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; color: #ef4444;">
            <ul style="margin: 0; padding-left: 1.5rem;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('superadmin.store.factory') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Factory Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter factory name" value="{{ old('name') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Google Account Email *</label>
            <input type="email" name="email" class="form-control" required placeholder="factory@example.com" value="{{ old('email') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Address *</label>
            <input type="text" name="address" class="form-control" required placeholder="Full address" value="{{ old('address') }}">
        </div>
        
        <div style="display: flex; gap: 1rem;">
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Latitude *</label>
                <input type="number" step="any" name="latitude" class="form-control" required placeholder="-6.2088" value="{{ old('latitude', request('lat', '')) }}">
            </div>
            <div class="form-group" style="flex: 1;">
                <label class="form-label">Longitude *</label>
                <input type="number" step="any" name="longitude" class="form-control" required placeholder="106.8456" value="{{ old('longitude', request('lng', '')) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Production Capacity (units) *</label>
            <input type="number" name="production_capacity" class="form-control" min="0" required placeholder="10000" value="{{ old('production_capacity') }}">
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+62..." value="{{ old('phone') }}">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <a href="{{ route('dashboard') }}" class="btn btn-danger" style="flex: 1;">Cancel</a>
            <button type="submit" class="btn btn-warning" style="flex: 1;">Create Factory</button>
        </div>
    </form>
</div>
@endsection
