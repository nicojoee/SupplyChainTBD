@extends('layouts.app')

@section('title', 'Setup Courier Profile')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Setup Your Courier Profile</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">Complete your courier profile to start deliveries.</p>

    <form action="{{ route('courier.setup') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Your Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter your name">
        </div>
        <div class="form-group">
            <label class="form-label">Vehicle Type</label>
            <select name="vehicle_type" class="form-control">
                <option value="">Select vehicle type...</option>
                <option value="Motorcycle">🏍️ Motorcycle</option>
                <option value="Car">🚗 Car</option>
                <option value="Van">🚐 Van</option>
                <option value="Truck">🚚 Truck</option>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">License Plate</label>
            <input type="text" name="license_plate" class="form-control" placeholder="B 1234 XYZ">
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+62...">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Courier Profile</button>
    </form>
</div>
@endsection
