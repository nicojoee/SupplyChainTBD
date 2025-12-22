@extends('layouts.app')

@section('title', 'Manage Distributors')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🏪 Distributor Management</h2>
        <a href="{{ route('superadmin.add.distributor') }}" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;">+ Add Distributor</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @if($distributors->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Warehouse</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($distributors as $distributor)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $distributor->name }}</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">{{ $distributor->user->email ?? 'No account' }}</div>
                </td>
                <td style="font-size: 0.9rem;">{{ Str::limit($distributor->address, 40) }}</td>
                <td>
                    <span class="badge badge-info">{{ number_format($distributor->warehouse_capacity) }} units</span>
                </td>
                <td>
                    @if($distributor->stocks->count() > 0)
                        @foreach($distributor->stocks->take(3) as $ds)
                            <span class="badge badge-info">{{ $ds->product->name ?? 'N/A' }} ({{ $ds->quantity }})</span>
                        @endforeach
                        @if($distributor->stocks->count() > 3)
                            <span class="badge badge-success">+{{ $distributor->stocks->count() - 3 }} more</span>
                        @endif
                    @else
                        <span style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">No stock</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('superadmin.delete.distributor', $distributor) }}" method="POST" 
                          onsubmit="return confirm('Delete this distributor and their account?');">
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
            Showing {{ $distributors->firstItem() ?? 0 }} - {{ $distributors->lastItem() ?? 0 }} of {{ $distributors->total() }} distributors
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($distributors->onFirstPage())
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Previous</span>
            @else
                <a href="{{ $distributors->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Previous</a>
            @endif
            
            @if($distributors->hasMorePages())
                <a href="{{ $distributors->nextPageUrl() }}" class="btn btn-primary">Next →</a>
            @else
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No distributors registered. <a href="{{ route('superadmin.add.distributor') }}" style="color: var(--primary);">Add a distributor</a> or click on the map to add one.</p>
    @endif
</div>
@endsection
