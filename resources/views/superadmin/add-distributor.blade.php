@extends('layouts.app')

@section('title', 'Add Distributor Account')

@section('content')
<div class="card" style="max-width: 500px; margin: 0 auto;">
    <div class="card-header">
        <h2 class="card-title">🚚 Add Distributor Account</h2>
    </div>
    <p style="color: rgba(255,255,255,0.6); margin-bottom: 1.5rem;">
        Create a distributor account. The user will fill in their warehouse details when they first login.
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

    <form action="{{ route('superadmin.store.distributor') }}" method="POST">
        @csrf
        <div style="background: rgba(99, 102, 241, 0.1); border: 1px solid rgba(99, 102, 241, 0.3); border-radius: 10px; padding: 1rem; margin-bottom: 1.5rem;">
            <div style="font-weight: 600; color: #6366f1; margin-bottom: 0.5rem;">📧 Google Account Email</div>
            <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6);">This email will be used to login via Google OAuth.</div>
        </div>

        <div class="form-group">
            <label class="form-label">Email Address *</label>
            <input type="email" name="email" class="form-control" required placeholder="distributor@example.com" value="{{ old('email') }}">
        </div>

        <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
            <a href="{{ route('superadmin.distributors') }}" class="btn btn-danger" style="flex: 1;">Cancel</a>
            <button type="submit" class="btn btn-primary" style="flex: 1;">Create Account</button>
        </div>
    </form>
</div>
@endsection
