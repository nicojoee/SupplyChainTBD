@extends('layouts.app')

@section('title', 'Setup Distributor Profile')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Setup Your Distributor Profile</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">Complete your distributor profile to start managing stock.</p>

    <form action="{{ route('distributor.setup') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Company Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter company name">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Address *</label>
            <input type="text" name="address" class="form-control" required placeholder="Full address">
        </div>
        <div class="form-group">
            <label class="form-label">Warehouse Capacity (units) *</label>
            <input type="number" name="warehouse_capacity" class="form-control" min="0" required placeholder="50000">
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+62...">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Distributor Profile</button>
    </form>
</div>
@endsection
