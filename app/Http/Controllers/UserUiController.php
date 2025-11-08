<?php
namespace App\Http\Controllers;

use App\Services\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class UserUiController extends Controller
{
    public function __construct(private ApiClient $api) {}

    public function create(\Illuminate\Http\Request $r, \App\Services\ApiClient $api){
        $nationalities = $api->nationalities()->json() ?? [];
        $resp  = $api->listUsers([
            'page'   => (int)$r->input('page',1),
            'size'   => (int)$r->input('size',10),
            'search' => (string)$r->input('search',''),
        ]);
        $users = data_get($resp->json(), 'data', []);
        $total = (int) data_get($resp->json(), 'total', 0);
        return view('users.create', compact('nationalities','users','total'));
    }

    public function store(Request $r) {
        $r->validate([
            'cst_name'                  => 'required|string',
            'cst_dob'                   => 'required|date',
            'nationality_id'            => 'required|integer',
            'cst_phoneNum'              => 'required|string',
            'cst_email'                 => 'required|email',
            'family'                    => 'array',
            'family.*.fl_name'          => 'nullable|string',
            'family.*.fl_relation'      => 'nullable|string',
            'family.*.fl_relation_other'=> 'nullable|string',
            'family.*.fl_dob'           => 'nullable|date',
        ]);

        $family = collect($r->input('family', []))
            ->map(function ($f) {
                return [
                    'fl_name'           => $f['fl_name'] ?? null,
                    'fl_relation'       => $f['fl_relation'] ?? null,
                    'fl_relation_other' => $f['fl_relation_other'] ?? null,
                    'fl_dob'            => isset($f['fl_dob']) ? Carbon::parse($f['fl_dob'])->format('Y-m-d') : null,
                ];
            })
            ->filter(fn($f) => $f['fl_name'] || $f['fl_relation'] || $f['fl_dob'])
            ->values()->all();

        $payload = [
            'cst_name'       => (string)$r->input('cst_name'),
            'cst_dob'        => Carbon::parse($r->input('cst_dob'))->format('Y-m-d'),
            'nationality_id' => (int)$r->input('nationality_id'),
            'cst_phoneNum'   => (string)$r->input('cst_phoneNum'),
            'cst_email'      => (string)$r->input('cst_email'),
            'family'         => $family,
        ];

        $res = $this->api->createUser($payload);

        if ($res->status() === 201) {
            return redirect()->route('users.create')->with('ok', 'Created');
        }
        if ($res->status() === 409) {
            return back()->withErrors(['cst_email' => 'Email exists'])->withInput();
        }
        if ($res->status() === 422) {
            return back()->withErrors($res->json()['fields'] ?? ['payload' => 'Validation error'])->withInput();
        }
        return back()->withErrors(['api' => 'Unexpected error'])->withInput();
    }

    public function show(int $id) {
        $res = $this->api->getUser($id);
        if ($res->status() === 404) abort(404);
        abort_unless($res->successful(), 502);
        return view('users.show', ['u' => $res->json()]);
    }

    public function update(Request $r, int $id) {
        // validasi sama seperti store()
        $r->validate([
            'cst_name'       => 'required|string',
            'cst_dob'        => 'required|date',
            'nationality_id' => 'required|integer',
            'cst_phoneNum'   => 'required|string',
            'cst_email'      => 'required|email',
            'family'                    => 'array',
            'family.*.fl_name'          => 'nullable|string',
            'family.*.fl_relation'      => 'nullable|string',
            'family.*.fl_relation_other'=> 'nullable|string',
            'family.*.fl_dob'           => 'nullable|date',
        ]);

        $family = collect($r->input('family', []))->map(function ($f) {
            return [
                'fl_name'           => $f['fl_name'] ?? null,
                'fl_relation'       => $f['fl_relation'] ?? null,
                'fl_relation_other' => $f['fl_relation_other'] ?? null,
                'fl_dob'            => isset($f['fl_dob']) ? Carbon::parse($f['fl_dob'])->format('Y-m-d') : null,
            ];
        })->filter(fn($f) => $f['fl_name'] || $f['fl_relation'] || $f['fl_dob'])->values()->all();

        $payload = [
            'cst_name'       => (string)$r->input('cst_name'),
            'cst_dob'        => Carbon::parse($r->input('cst_dob'))->format('Y-m-d'),
            'nationality_id' => (int)$r->input('nationality_id'),
            'cst_phoneNum'   => (string)$r->input('cst_phoneNum'),
            'cst_email'      => (string)$r->input('cst_email'),
            'family'         => $family,
        ];

        $res = $this->api->updateUser($id, $payload);

        if ($res->successful()) return redirect()->route('users.show', $id)->with('ok', 'Updated');
        if ($res->status() === 422) return back()->withErrors($res->json()['fields'] ?? ['payload' => 'Validation error'])->withInput();
        if ($res->status() === 409) return back()->withErrors(['cst_email' => 'Email exists'])->withInput();
        return back()->withErrors(['api' => 'Unexpected error'])->withInput();
    }

    public function destroy(int $id) {
        $res = $this->api->deleteUser($id);
        if ($res->successful()) return redirect()->route('users.create')->with('ok', 'Deleted');
        return back()->withErrors(['api' => 'Delete failed']);
    }
}
