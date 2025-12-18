@foreach($distributors as $distributor)
    <div style="padding: 1rem; background: rgba(255,255,255,0.03); border-radius: 10px; margin-bottom: 0.75rem;">
        <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $distributor->name }}</div>
        <div style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-bottom: 0.5rem;">{{ $distributor->address }}</div>
        <div style="font-size: 0.8rem; color: var(--primary); margin-bottom: 0.5rem;">Warehouse: {{ number_format($distributor->warehouse_capacity) }} units</div>
        @if($distributor->stocks->count() > 0)
            <div style="font-size: 0.8rem;">
                Stock: 
                @foreach($distributor->stocks as $ds)
                    <span class="badge badge-info">{{ $ds->product->name ?? 'N/A' }} ({{ $ds->quantity }} pcs)</span>
                @endforeach
            </div>
        @else
            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.4);">No stock</div>
        @endif
    </div>
@endforeach
@if($distributors->count() == 0)
    <p style="color: rgba(255,255,255,0.5);">No distributors registered yet.</p>
@endif
