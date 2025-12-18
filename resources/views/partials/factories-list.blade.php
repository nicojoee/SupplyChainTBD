@foreach($factories as $factory)
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 0.75rem;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $factory->name }}</div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">{{ $factory->address }}</div>
        <div style="font-size: 0.8rem; color: var(--warning); margin-bottom: 0.5rem;">Capacity: {{ number_format($factory->production_capacity) }} units</div>
        @if($factory->products->count() > 0)
            <div style="font-size: 0.8rem; color: var(--secondary);">
                Products: 
                @foreach($factory->products as $fp)
                    <span class="badge badge-warning">{{ $fp->product->name ?? 'N/A' }} (${{ number_format($fp->price, 2) }})</span>
                @endforeach
            </div>
        @else
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4);">No products listed</div>
        @endif
    </div>
@endforeach
@if($factories->count() == 0)
    <p style="color: rgba(255,255,255,0.5);">No factories registered yet.</p>
@endif
