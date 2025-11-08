<!doctype html>
<html>
<head><meta charset="utf-8"><title>User Detail</title></head>
<body>
<p><a href="{{ route('users.create') }}">← Back</a></p>

@php $u = $u ?? []; @endphp
<h1>User #{{ $u['cst_id'] ?? $u['ID'] ?? '-' }}</h1>

<ul>
    <li>Name: {{ $u['Name'] ?? '-' }}</li>
    <li>Email: {{ $u['Email'] ?? '-' }}</li>
    <li>Phone: {{ $u['PhoneNum'] ?? '-' }}</li>
    <li>DOB: {{ $u['Dob'] ?? '-' }}</li>
    <li>Nationality ID: {{ $u['NationalityID'] ?? '-' }}</li>
</ul>

@if(!empty($u['Family']))
    <h3>Family</h3>
    <ul>
        @foreach($u['Family'] as $f)
            <li>{{ $f['Name'] ?? '-' }} — {{ $f['Relation'] ?? '-' }} — {{ $f['Dob'] ?? '-' }}</li>
        @endforeach
    </ul>
@endif
</body>
</html>
