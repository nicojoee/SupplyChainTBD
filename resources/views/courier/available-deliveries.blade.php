@extends('layouts.app')

@section('title', 'Available Deliveries')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">📦 Available Deliveries</h2>
        <span style="color: rgba(255,255,255,0.6);">{{ $total }} deliveries available</span>
    </div>

    @if(session('error'))
        <div style="padding: 1rem; background: rgba(239, 68, 68, 0.2); border-radius: 8px; margin-bottom: 1rem; color: #fca5a5;">
            {{ session('error') }}
        </div>
    @endif

    @if($orders->count() > 0)
        <div style="margin-bottom: 1rem; padding: 0.75rem; background: rgba(99, 102, 241, 0.1); border-radius: 8px; font-size: 0.9rem;">
            📍 Sorted by distance from your current location
            @if(!$courier->current_latitude || !$courier->current_longitude)
                <br><span style="color: #f59e0b;">⚠️ Update your location for accurate distance sorting</span>
            @endif
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Order ID</th>
                        <th>Products</th>
                        <th>Total</th>
                        <th>Distance</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $index => $order)
                    <tr>
                        <td>{{ ($currentPage - 1) * 10 + $index + 1 }}</td>
                        <td>#{{ $order->id }}</td>
                        <td>
                            @foreach($order->items as $item)
                                <div style="font-size: 0.85rem;">
                                    {{ $item->product->name ?? 'Unknown' }} x{{ $item->quantity }}
                                </div>
                            @endforeach
                        </td>
                        <td>{{ formatRupiah($order->total_amount) }}</td>
                        <td>
                            @if($order->distance && $order->distance < 999999)
                                <span style="color: {{ $order->distance < 10 ? '#22c55e' : ($order->distance < 50 ? '#f59e0b' : '#ef4444') }};">
                                    {{ number_format($order->distance, 1) }} km
                                </span>
                            @else
                                <span style="color: rgba(255,255,255,0.5);">Unknown</span>
                            @endif
                        </td>
                        <td>{{ $order->created_at->format('M d, Y') }}</td>
                        <td>
                            <form action="{{ route('courier.accept', $order) }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem;" 
                                        onclick="return confirm('Accept this delivery?')">
                                    ✓ Accept
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($totalPages > 1)
        <div style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(255,255,255,0.1);">
            @if($currentPage > 1)
                <a href="{{ route('courier.available-deliveries', ['page' => $currentPage - 1]) }}" class="btn" style="padding: 0.5rem 1rem;">
                    ← Previous
                </a>
            @else
                <button class="btn" style="padding: 0.5rem 1rem; opacity: 0.5;" disabled>
                    ← Previous
                </button>
            @endif

            <span style="color: rgba(255,255,255,0.7);">
                Page {{ $currentPage }} of {{ $totalPages }}
            </span>

            @if($currentPage < $totalPages)
                <a href="{{ route('courier.available-deliveries', ['page' => $currentPage + 1]) }}" class="btn" style="padding: 0.5rem 1rem;">
                    Next →
                </a>
            @else
                <button class="btn" style="padding: 0.5rem 1rem; opacity: 0.5;" disabled>
                    Next →
                </button>
            @endif
        </div>
        @endif
    @else
        <div style="text-align: center; padding: 3rem; color: rgba(255,255,255,0.5);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">📭</div>
            <h3>No deliveries available</h3>
            <p>Check back later for new delivery requests.</p>
        </div>
    @endif
</div>
@endsection
