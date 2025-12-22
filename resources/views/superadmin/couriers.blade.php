@extends('layouts.app')

@section('title', 'Manage Couriers')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🚚 Courier Management</h2>
        <a href="{{ route('superadmin.add.courier') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">+ Add Courier</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @if($couriers->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Vehicle</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($couriers as $courier)
            <tr>
                <td>{{ $courier->name }}</td>
                <td>{{ $courier->user->email ?? 'N/A' }}</td>
                <td>{{ $courier->vehicle_type ?? 'N/A' }} {{ $courier->license_plate ? '• ' . $courier->license_plate : '' }}</td>
                <td>{{ $courier->phone ?? 'N/A' }}</td>
                <td>
                    <span class="badge {{ $courier->status === 'idle' ? 'badge-success' : 'badge-danger' }}">
                        {{ ucfirst($courier->status) }}
                    </span>
                </td>
                <td>
                    <form action="{{ route('superadmin.delete.courier', $courier) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to delete this courier? This will also delete their login account.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Pagination Controls -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-glass);">
        <div style="color: rgba(255,255,255,0.5); font-size: 0.85rem;">
            Showing {{ $couriers->firstItem() ?? 0 }} - {{ $couriers->lastItem() ?? 0 }} of {{ $couriers->total() }} couriers
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($couriers->onFirstPage())
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Previous</span>
            @else
                <a href="{{ $couriers->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Previous</a>
            @endif
            
            @if($couriers->hasMorePages())
                <a href="{{ $couriers->nextPageUrl() }}" class="btn btn-primary">Next →</a>
            @else
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No couriers registered yet. Click "Add Courier" to create a new courier account.</p>
    @endif
</div>
@endsection
