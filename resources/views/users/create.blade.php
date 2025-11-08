<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet"
          href="https://unpkg.com/@picocss/pico@1.5.10/css/pico.min.css" />
    <title>User CRUD</title>

    <link rel="stylesheet" href="{{ asset('assets/css/app.css') }}">

</head>
<body>
<main class="container">
    <h4 class="section-title">USER</h4>

    @if(session('ok'))
        <article role="alert">{{ session('ok') }}</article>
    @endif

    @php
        use Illuminate\Support\MessageBag;
        if (!isset($errors)) {
          $errors = session('errors', new MessageBag());
        }
    @endphp

    @if($errors->any())
        <article role="alert">
            <strong>Error:</strong>
            <ul style="margin:.5rem 0 0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </article>
    @endif


    <form method="post" action="{{ route('users.store') }}">
        @csrf

        <label>
            Nama (cst_name)
            <input name="cst_name" placeholder="Masukan nama anda" value="{{ old('cst_name') }}" required />
        </label>

        <div class="grid-2">
            <label>
                Tanggal Lahir (cst_dob)
                <input type="date" name="cst_dob" value="{{ old('cst_dob') }}" required />
            </label>

            <label>
                Nationality (nationality_id)
                <select name="nationality_id" required>
                    <option value="">Pilih kewarganegaraan</option>
                    @foreach(($nationalities ?? []) as $n)
                        <option value="{{ $n['ID'] }}" @selected(old('nationality_id') == ($n['ID'] ?? ''))>
                            {{ $n['Name'] ?? 'Unknown' }} ({{ $n['Code'] ?? '-' }})
                        </option>
                    @endforeach
                </select>
            </label>
        </div>

        <div class="grid-2">
            <label>
                Phone (cst_phoneNum)
                <input name="cst_phoneNum" placeholder="08xxxxxxxxxx" value="{{ old('cst_phoneNum') }}" required />
            </label>

            <label>
                Email (cst_email)
                <input type="email" name="cst_email" placeholder="nama@domain.com" value="{{ old('cst_email') }}" required />
            </label>
        </div>

        <div class="hr"></div>

        <div class="grid-2" style="grid-template-columns:1fr auto">
            <div class="section-title" style="margin:0">Keluarga (family_list)</div>
            <div class="add-link" id="addRow">+ Tambah Keluarga</div>
        </div>

        <div id="familyWrap">
            @php $rows = collect(old('family', [])); @endphp
            @foreach($rows as $i => $fm)
                @php
                    $rel = $fm['fl_relation'] ?? '';
                    $isOther = $rel === 'Other';
                @endphp
                <div class="family-row">
                    <select name="family[{{ $i }}][fl_relation]" class="rel">
                        <option value="">Pilih relasi</option>
                        @foreach(['Spouse','Child','Father','Mother','Sibling','Guardian','Other'] as $o)
                            <option value="{{ $o }}" @selected($rel === $o)>{{ $o }}</option>
                        @endforeach
                    </select>

                    <input
                        name="family[{{ $i }}][fl_relation_other]"
                        class="rel-other"
                        placeholder="Isi relasi"
                        value="{{ $fm['fl_relation_other'] ?? '' }}"
                        style="display: {{ $isOther ? 'block' : 'none' }};"
                    />

                    <input
                        name="family[{{ $i }}][fl_name]"
                        placeholder="Nama"
                        value="{{ $fm['fl_name'] ?? '' }}"
                    />

                    <input
                        type="date"
                        name="family[{{ $i }}][fl_dob]"
                        value="{{ $fm['fl_dob'] ?? '' }}"
                    />

                    <button type="button" class="btn-danger rm">Hapus</button>
                </div>
            @endforeach
        </div>

        <div class="hr"></div>

        <button type="submit">Simpan</button>
    </form>

    <div class="hr"></div>
    <h4 class="section-title">DAFTAR USER</h4>
    <div class="table-wrap">
        <table role="grid">
            <colgroup>
                <col><col><col><col><col>
            </colgroup>
            <thead>
            <tr>
                <th>ID</th><th>Nama</th><th>Email</th><th>Phone</th><th>Aksi</th>
            </tr>
            </thead>
            <tbody>
            @forelse(($users ?? []) as $u)
                @php $id = $u['cst_id'] ?? $u['ID'] ?? null; @endphp
                <tr>
                    <td class="num">{{ $id }}</td>
                    <td class="truncate" title="{{ $u['cst_name'] ?? '-' }}">{{ $u['cst_name'] ?? '-' }}</td>
                    <td class="truncate" title="{{ $u['cst_email'] ?? '-' }}">{{ $u['cst_email'] ?? '-' }}</td>
                    <td class="truncate" title="{{ $u['cst_phoneNum'] ?? '-' }}">{{ $u['cst_phoneNum'] ?? '-' }}</td>
                    <td class="actions">
                        <div class="action-bar">
                            @if($id)
                                <a role="button" class="btn-sm" href="{{ route('users.show', $id) }}">Show</a>
                                <form action="{{ route('users.destroy', $id) }}" method="post"
                                      onsubmit="return confirm('Hapus user ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-danger btn-sm">Delete</button>
                                </form>
                            @endif
                        </div>
                    </td>

                </tr>
            @empty
                <tr><td colspan="5">Belum ada data</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

</main>

<script>
    (function () {
        const wrap = document.getElementById('familyWrap');
        const add  = document.getElementById('addRow');
        let idx = {{ max(($rows?->keys()?->max() ?? -1), -1) + 1 }};

        function wireRow(div) {
            const sel = div.querySelector('.rel');
            const oth = div.querySelector('.rel-other');
            if (sel && oth) {
                sel.onchange = () => {
                    const show = sel.value === 'Other';
                    oth.style.display = show ? 'block' : 'none';
                    if (!show) oth.value = '';
                };
            }
            const rm = div.querySelector('.rm');
            if (rm) rm.onclick = () => div.remove();
        }

        function addRow(d = {}) {
            const div = document.createElement('div');
            div.className = 'family-row';
            div.innerHTML = `
        <select name="family[${idx}][fl_relation]" class="rel">
          <option value="">Pilih relasi</option>
          <option>Spouse</option><option>Child</option>
          <option>Father</option><option>Mother</option>
          <option>Sibling</option><option>Guardian</option>
          <option>Other</option>
        </select>
        <input name="family[${idx}][fl_relation_other]" class="rel-other" placeholder="Isi relasi" style="display:none" />
        <input name="family[${idx}][fl_name]" placeholder="Nama" value="${d.fl_name || ''}" />
        <input type="date" name="family[${idx}][fl_dob]" value="${d.fl_dob || ''}" />
        <button type="button" class="btn-danger rm">Hapus</button>
      `;
            wrap.appendChild(div);
            if (d.fl_relation) div.querySelector('.rel').value = d.fl_relation;
            if (d.fl_relation === 'Other') {
                const other = div.querySelector('.rel-other');
                other.style.display = 'block';
                other.value = d.fl_relation_other || '';
            }
            wireRow(div);
            idx++;
        }

        add.onclick = () => addRow();
        if (!wrap.children.length) addRow();
        else Array.from(wrap.children).forEach(wireRow);
    })();
</script>
</body>
</html>
