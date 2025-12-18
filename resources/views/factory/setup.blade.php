@extends('layouts.app')

@section('title', 'Setup Factory Profile')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">Setup Your Factory Profile</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">Complete your factory profile to start production.</p>

    <form action="{{ route('factory.setup') }}" method="POST">
        @csrf
        <div class="form-group">
            <label class="form-label">Factory Name *</label>
            <input type="text" name="name" class="form-control" required placeholder="Enter factory name">
        </div>
        <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" placeholder="Brief description of your factory"></textarea>
        </div>
        <div class="form-group">
            <label class="form-label">Address *</label>
            <input type="text" name="address" class="form-control" required placeholder="Full address">
        </div>
        <div class="form-group">
            <label class="form-label">Production Capacity (units) *</label>
            <input type="number" name="production_capacity" class="form-control" min="0" required placeholder="10000">
        </div>
        <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" placeholder="+62...">
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%;">Create Factory Profile</button>
    </form>
</div>
@endsection
