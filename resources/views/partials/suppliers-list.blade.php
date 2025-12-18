@foreach($suppliers as $supplier)
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 0.75rem;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $supplier->name }}</div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">{{ $supplier->address }}</div>
        @if($supplier->products->count() > 0)
            <div style="font-size: 0.8rem; color: var(--secondary);">
                Products: 
                @foreach($supplier->products as $sp)
                    <span class="badge badge-success">{{ $sp->product->name ?? 'N/A' }} (${{ number_format($sp->price, 2) }})</span>
                @endforeach
            </div>
        @else
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4);">No products listed</div>
        @endif
    </div>
@endforeach
@if($suppliers->count() == 0)
    <p style="color: rgba(255,255,255,0.5);">No suppliers registered yet.</p>
@endif
