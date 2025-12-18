@extends('layouts.app')

@section('title', 'Manage Suppliers')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📦 Supplier Management</h2>
        <a href="{{ route('superadmin.add.supplier') }}" class="btn btn-success" style="padding: 0.5rem 1rem; font-size: 0.85rem;">+ Add Supplier</a>
    </div>

    @if(session('success'))
        <div style="background: rgba(34, 197, 94, 0.2); border: 1px solid #22c55e; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; color: #22c55e;">
            {{ session('success') }}
        </div>
    @endif

    @if($suppliers->count() > 0)
    <table class="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Products</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($suppliers as $supplier)
            <tr>
                <td>
                    <div style="font-weight: 600;">{{ $supplier->name }}</div>
                    <div style="font-size: 0.8rem; color: rgba(255,255,255,0.5);">{{ $supplier->user->email ?? 'No account' }}</div>
                </td>
                <td style="font-size: 0.9rem;">{{ Str::limit($supplier->address, 40) }}</td>
                <td style="font-size: 0.9rem;">
                    {{ $supplier->phone ?? 'N/A' }}<br>
                    <span style="color: rgba(255,255,255,0.5);">{{ $supplier->email ?? '' }}</span>
                </td>
                <td>
                    @if($supplier->products->count() > 0)
                        @foreach($supplier->products->take(3) as $sp)
                            <span class="badge badge-success">{{ $sp->product->name ?? 'N/A' }}</span>
                        @endforeach
                        @if($supplier->products->count() > 3)
                            <span class="badge badge-info">+{{ $supplier->products->count() - 3 }} more</span>
                        @endif
                    @else
                        <span style="color: rgba(255,255,255,0.4); font-size: 0.85rem;">No products</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('superadmin.delete.supplier', $supplier) }}" method="POST" 
                          onsubmit="return confirm('Delete this supplier and their account?');">
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
            Showing {{ $suppliers->firstItem() ?? 0 }} - {{ $suppliers->lastItem() ?? 0 }} of {{ $suppliers->total() }} suppliers
        </div>
        <div style="display: flex; gap: 0.5rem;">
            @if($suppliers->onFirstPage())
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">← Previous</span>
            @else
                <a href="{{ $suppliers->previousPageUrl() }}" class="btn" style="background: rgba(255,255,255,0.1);">← Previous</a>
            @endif
            
            @if($suppliers->hasMorePages())
                <a href="{{ $suppliers->nextPageUrl() }}" class="btn btn-primary">Next →</a>
            @else
                <span class="btn" style="background: rgba(255,255,255,0.05); opacity: 0.5; cursor: not-allowed;">Next →</span>
            @endif
        </div>
    </div>
    @else
    <p style="color: rgba(255,255,255,0.5);">No suppliers registered. <a href="{{ route('superadmin.add.supplier') }}" style="color: var(--success);">Add a supplier</a> or click on the map to add one.</p>
    @endif
</div>
@endsection
