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
                <th>Joined</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
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
                <td>{{ $user->created_at->format('M d, Y') }}</td>
                <td>
                    <form action="{{ route('superadmin.users.role', $user) }}" method="POST" style="display: inline-flex; gap: 0.5rem;">
                        @csrf
                        @method('PATCH')
                        <select name="role" class="form-control" style="width: auto; padding: 0.4rem 0.75rem; font-size: 0.85rem;">
                            <option value="superadmin" {{ $user->role === 'superadmin' ? 'selected' : '' }}>Superadmin</option>
                            <option value="supplier" {{ $user->role === 'supplier' ? 'selected' : '' }}>Supplier</option>
                            <option value="factory" {{ $user->role === 'factory' ? 'selected' : '' }}>Factory</option>
                            <option value="distributor" {{ $user->role === 'distributor' ? 'selected' : '' }}>Distributor</option>
                            <option value="courier" {{ $user->role === 'courier' ? 'selected' : '' }}>Courier</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding: 0.4rem 0.75rem; font-size: 0.85rem;">Update</button>
                    </form>
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
