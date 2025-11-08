@extends('layout')
@section('content')
    <h3>Users</h3>

    <form method="get" style="margin:.5rem 0 1rem">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name/email">
        <button type="submit">Search</button>
        <a href="{{ route('users.create') }}">+ New</a>
    </form>

    <table>
        <thead><tr>
            <th>Name</th><th>Email</th><th>Phone</th><th>Keluarga</th><th>Action</th>
        </tr></thead>
        <tbody>
        @forelse(($data ?? []) as $u)
            <tr>
                <td>{{ $u['name'] }}</td>
                <td>{{ $u['email'] }}</td>
                <td>{{ $u['phone'] ?? '-' }}</td>
                <td>{{ $u['family_count'] ?? ($u['family'] ? count($u['family']) : 0) }}</td>
                <td>
                    <a href="{{ route('users.edit',$u['id']) }}">Edit</a>
                    <form action="{{ route('users.destroy',$u['id']) }}" method="post" style="display:inline">
                        @csrf @method('DELETE')
                        <button type="submit" onclick="return confirm('Delete?')">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="5">No data</td></tr>
        @endforelse
        </tbody>
    </table>
@endsection
