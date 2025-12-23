@extends('layouts.app')

@section('title', 'Manage Users')

@section('content')
<div class="card">
    <div class="card-header">
        <h2 class="card-title">All Users</h2>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Avatar</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Entity Status</th>
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            @php
                // Check if user has entity profile based on their role
                $hasEntityProfile = false;
                $entityMissing = false;
                $entityLocation = null;
                
                if ($user->role === 'supplier') {
                    $hasEntityProfile = $user->supplier !== null;
                    $entityMissing = !$hasEntityProfile;
                    if ($hasEntityProfile) {
                        $entityLocation = ($user->supplier->latitude != 0 || $user->supplier->longitude != 0);
                    }
                } elseif ($user->role === 'factory') {
                    $hasEntityProfile = $user->factory !== null;
                    $entityMissing = !$hasEntityProfile;
                    if ($hasEntityProfile) {
                        $entityLocation = ($user->factory->latitude != 0 || $user->factory->longitude != 0);
                    }
                } elseif ($user->role === 'distributor') {
                    $hasEntityProfile = $user->distributor !== null;
                    $entityMissing = !$hasEntityProfile;
                    if ($hasEntityProfile) {
                        $entityLocation = ($user->distributor->latitude != 0 || $user->distributor->longitude != 0);
                    }
                } elseif ($user->role === 'courier') {
                    $hasEntityProfile = $user->courier !== null;
                    $entityMissing = !$hasEntityProfile;
                    $entityLocation = true; // Couriers don't need fixed location
                } else {
                    // superadmin doesn't need entity profile
                    $hasEntityProfile = true;
                    $entityLocation = true;
                }
            @endphp
            <tr>
                <td>
                    <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}" 
                         alt="Avatar" 
                         style="width: 36px; height: 36px; border-radius: 50%;">
                </td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    <span class="badge badge-info">{{ ucfirst($user->role) }}</span>
                </td>
                <td>
                    @if($user->role === 'superadmin')
                        <span class="badge" style="background: #6b7280;">N/A</span>
                    @elseif($entityMissing)
                        <span class="badge" style="background: #ef4444;">⚠️ No Profile</span>
                    @elseif(!$entityLocation)
                        <span class="badge" style="background: #f59e0b;">📍 No Location</span>
                    @else
                        <span class="badge" style="background: #22c55e;">✓ Complete</span>
                    @endif
                </td>
                <td>{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        @if($user->role === 'superadmin')
                        <span class="badge" style="background: #6b7280; padding: 0.5rem 1rem;">🔒 Protected</span>
                        @else
                        <form action="{{ route('superadmin.users.role', $user) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                            @csrf
                            @method('PATCH')
                            <select name="role" class="form-control" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                                <option value="supplier" {{ $user->role === 'supplier' ? 'selected' : '' }}>Supplier</option>
                                <option value="factory" {{ $user->role === 'factory' ? 'selected' : '' }}>Factory</option>
                                <option value="distributor" {{ $user->role === 'distributor' ? 'selected' : '' }}>Distributor</option>
                                <option value="courier" {{ $user->role === 'courier' ? 'selected' : '' }}>Courier</option>
                            </select>
                            <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Update</button>
                        </form>
                        @endif
                        
                        @if($entityMissing && in_array($user->role, ['supplier', 'factory', 'distributor', 'courier']))
                        <form action="{{ route('superadmin.users.fix-profile', $user) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; background: #f59e0b;" title="Create missing entity profile">
                                🔧 Fix
                            </button>
                        </form>
                        @endif
                        
                        @if($user->id !== auth()->id())
                        <form action="{{ route('superadmin.users.delete', $user) }}" method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this account?\\n\\nUser: {{ $user->name }}\\nEmail: {{ $user->email }}\\n\\nThis action cannot be undone!');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn" style="padding: 0.4rem 0.75rem; font-size: 0.85rem; background: #ef4444;" title="Delete this account">
                                🗑️ Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 1rem;">
        {{ $users->links() }}
    </div>
</div>
@endsection
