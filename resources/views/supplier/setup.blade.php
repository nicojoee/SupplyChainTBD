@extends('layouts.app')

@section('title', 'Supplier Profile Pending')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 1rem;">📦</div>
    <h2 style="color: #fff; margin-bottom: 1rem;">Supplier Profile Not Found</h2>
    <p style="color: rgba(255,255,255,0.7); margin-bottom: 1.5rem;">
        Your supplier profile has not been set up yet. Please contact the <strong style="color: #22c55e;">Superadmin</strong> to create your supplier account with the correct location on the map.
    </p>
    <div style="background: rgba(34, 197, 94, 0.1); border: 1px solid rgba(34, 197, 94, 0.3); padding: 1rem; border-radius: 10px;">
        <div style="color: #22c55e; font-weight: 600;">ℹ️ What happens next?</div>
        <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin: 0.5rem 0 0 0;">
            The Superadmin will click on the map to set your location and create your supplier profile. Once done, you can login again to access your dashboard.
        </p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top: 1.5rem;">Go to Dashboard</a>
</div>
@endsection
