@extends('layouts.app')

@section('title', 'Setup Supplier Profile')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Setup Your Supplier Profile</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">Complete your supplier profile to start listing products.</p>

    <form action="{{ route('supplier.setup') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Company Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter company name">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of your company"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Address *</label>
            <input type="text" name="address" class="form-control" required placeholder="Full address">
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+62...">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Supplier Profile</button>
    </form>
</div>
@endsection
