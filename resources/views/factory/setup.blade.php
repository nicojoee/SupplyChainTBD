@extends('layouts.app')

@section('title', 'Factory Profile Pending')

@section('content')
<div class="card" style="max-width: 600px; margin: 0 auto; text-align: center;">
    <div style="font-size: 4rem; margin-bottom: 1rem;">🏭</div>
    <h2 style="color: #fff; margin-bottom: 1rem;">Factory Profile Not Found</h2>
    <p style="color: rgba(255,255,255,0.7); margin-bottom: 1.5rem;">
        Your factory profile has not been set up yet. Please contact the <strong style="color: #f59e0b;">Superadmin</strong> to create your factory account with the correct location on the map.
    </p>
    <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); padding: 1rem; border-radius: 10px;">
        <div style="color: #f59e0b; font-weight: 600;">ℹ️ What happens next?</div>
        <p style="color: rgba(255,255,255,0.6); font-size: 0.9rem; margin: 0.5rem 0 0 0;">
            The Superadmin will click on the map to set your location and create your factory profile. Once done, you can login again to access your dashboard.
        </p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-primary" style="margin-top: 1.5rem;">Go to Dashboard</a>
</div>
@endsection
