@extends('layouts.app')

@section('title', 'Manage Factories')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">🏭 Factory Management</h2>
        <a href="{{ route('superadmin.add.factory') }}" class="btn btn-warning" style="padding: 0.5rem 1rem; font-size: 0.85rem;">+ Add Factory</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @if($factories->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Capacity</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($factories as $factory)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $factory->name }}</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">{{ $factory->user->email ?? 'No account' }}</div>
                </td>
                <td style="font-size: 0.9rem;">{{ Str::limit($factory->address, 40) }}</td>
                <td>
                    <span class="badge badge-warning">{{ number_format($factory->production_capacity) }} units</span>
                </td>
                <td>
                    @if($factory->products->count() > 0)
                        @foreach($factory->products->take(3) as $fp)
                            <span class="badge badge-warning">{{ $fp->product->name ?? 'N/A' }}</span>
                        @endforeach
                        @if($factory->products->count() > 3)
                            <span class="badge badge-info">+{{ $factory->products->count() - 3 }} more</span>
                        @endif
                    @else
                        <span style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">No products</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('superadmin.delete.factory', $factory) }}" method="POST" 
                          onsubmit="return confirm('Delete this factory and their account?');">
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
            Showing {{ $factories->firstItem() ?? 0 }} - {{ $factories->lastItem() ?? 0 }} of {{ $factories->total() }} factories
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($factories->onFirstPage())
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Previous</span>
            @else
                <a href="{{ $factories->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Previous</a>
            @endif
            
            @if($factories->hasMorePages())
                <a href="{{ $factories->nextPageUrl() }}" class="btn btn-primary">Next →</a>
            @else
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No factories registered. <a href="{{ route('superadmin.add.factory') }}" style="color: var(--warning);">Add a factory</a> or click on the map to add one.</p>
    @endif
</div>
@endsection
