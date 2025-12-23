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
            <label class="form-label">Truck Type (by Capacity) *</label>
            <select name="vehicle_type" class="form-control" required>
                <option value="">Select truck type...</option>
                <option value="small_15">🚛 Small Truck - 15 Ton</option>
                <option value="medium_20">🚛 Medium Truck - 20 Ton</option>
                <option value="large_30">🚛 Large Truck - 30 Ton</option>
            </select>
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.5rem;">
                Larger trucks can carry more per trip. Orders exceeding capacity will be split among multiple couriers.
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">License Plate</label>
            <input type="text" name="license_plate" class="form-control" placeholder="B 1234 XYZ">
        </div>
        <div class="form-group">
            <label class="form-label">Phone Number * (Indonesia)</label>
            <div style="display: flex; align-items: center; gap: 0;">
                <span style="background: rgba(99, 102, 241, 0.3); padding: 0.75rem 1rem; border-radius: 10px 0 0 10px; border: 1px solid rgba(255,255,255,0.1); border-right: none; font-weight: 600;">+62</span>
                <input type="text" name="phone" class="form-control" required 
                       pattern="[0-9]{10,13}" minlength="10" maxlength="13"
                       placeholder="8123456789" 
                       style="border-radius: 0 10px 10px 0;"
                       title="Masukkan 10-13 digit nomor telepon (tanpa 0 di depan)">
            </div>
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5); margin-top: 0.5rem;">
                Contoh: 8123456789 (tanpa 0 di depan, minimal 10 digit)
            </div>
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Courier Profile</button>
    </form>
</div>
@endsection
