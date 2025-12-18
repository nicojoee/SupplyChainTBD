@extends('layouts.app')

@section('title', 'Logout Confirmation')

@section('content')
<div style="max-width: 500px; margin: 4rem auto; text-align: center;">
    <div class="card">
        <div style="padding: 3rem 2rem;">
            <div style="font-size: 4rem; margin-bottom: 1.5rem;">👋</div>
            <h2 style="margin-bottom: 1rem; font-size: 1.5rem;">Ready to leave?</h2>
            <p style="color: rgba(255,255,255,0.6); margin-bottom: 2rem;">
                Are you sure you want to log out of your account?
            </p>
            
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <a href="{{ url()->previous() }}" class="btn" style="background: #ef4444; color: #fff; padding: 0.75rem 2rem; font-weight: 600; text-decoration: none;">
                    ← Cancel
                </a>
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-danger" style="padding: 0.75rem 2rem;">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <p style="margin-top: 1.5rem; color: rgba(255,255,255,0.4); font-size: 0.85rem;">
        Logged in as <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
    </p>
</div>
@endsection
