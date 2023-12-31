<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\User;
use App\Models\History;
use App\Models\MataPelajaran;
use App\Models\Pelanggaran;
use App\Models\Siswa;
use App\Models\SOP;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        request()->validate([
            'direction' => ['in:asc,desc'],
            'field' => ['in:nama,token,peran']
        ]);

        $userQuery = User::query();

        if (request('search')) {
            $userQuery
                ->where('nama', 'LIKE', '%'.request('search').'%')
                ->orWhere('token', 'LIKE', '%'.request('search').'%')
                ->orWhere('peran', 'LIKE', '%'.request('search').'%');
        }

        if (request()->has(['field', 'direction'])) {
            $userQuery->orderBy(request('field'), request('direction'));
        }

        return Inertia::render('User', [
            'userQuery' => $userQuery->get(),
            'userPaginate' => $userQuery->orderBy('created_at', 'desc')->paginate('10')->withQueryString(),
            'filters' => request()->all(['search', 'field', 'direction']),
            'historyQuery' => History::query()->where('nama_tabel', 'data user')->with('user')->orderBy('created_at', 'desc')->get(),
            'can' => [
                'viewUser' => Auth::user()->can('viewAny', User::class),
                'viewSiswa' => Auth::user()->can('viewAny', Siswa:: class),
                'viewGuru' => Auth::user()->can('viewAny', Guru:: class),
                'viewMataPelajaran' => Auth::user()->can('viewAny', MataPelajaran:: class),
                'viewSOP' => Auth::user()->can('viewAny', SOP:: class),
                'viewPelanggaran' => Auth::user()->can('viewAny', Pelanggaran::class),
            ]
        ]);    
    }


    public function dataProcess($request) {
        try {
            $attributes = $request->validate([
                'token' => 'required',
                'peran' => 'required',
                'nama' => 'required',
            ]);

            $nullableFields = [
                'password',
                'id',
            ];

            foreach($nullableFields as $field) {
                if (!is_null($request->input($field))) {
                    $attributes = array_merge($attributes, [$field => $request->input($field)]);
                } else if ($field === 'password' && is_null($request->input('id'))) {
                    $attributes['password'] = $attributes['password'] ?? $attributes['token'];
                }
            }

            $attributes['token'] = strtoupper($attributes['token']);

            if ($request->file('image_path')) {
                $request->validate([
                    'image_path' => 'image|max:2048',
                ]);

                $image = $request->file('image_path');
                $extension = strtolower($image->getClientOriginalExtension());
                $image_name = md5(uniqid($image->getClientOriginalName(), true) . time()) . '.' . $extension;
                $attributes['image_path'] = $image_name;
                $image->move('./img/', $image_name);
                return $attributes;
            } else if ($request->image_path) {
                return array_merge($attributes, ['image_path' => $request->input('image_path')]);
            }
            return $attributes;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function create(Request $request) {
        $attributes = $this->dataProcess($request);

        $user = User::create($attributes);

        $history = History::create([
            'user_id' => Auth::id(),
            'nama_tabel' => 'data user',
            'jenis' => 'tambah',
            'nama_data' => $attributes['nama'],
            'token_data' => $attributes['token'],
        ]);

        return back()->withInput();
    }

    public function update(Request $request) {
        $attributes = $this->dataProcess($request);

        $user = User::findOrFail($attributes['id']);
        $image_path = public_path("img/{$user->image_path}");
        
        if (!str_contains($image_path ,'default.png') && $attributes["image_path"] != $user->image_path) {
            unlink($image_path);
        }
        $user->update($attributes);

        $history = History::create([
            'user_id' => Auth::id(),
            'nama_tabel' => 'data user',
            'jenis' => 'ubah',
            'nama_data' => $attributes['nama'],
            'token_data' => $attributes['token'],
        ]);

        return back()->withInput();
    }

    public function destroy(Request $request) {
        try {
            $user = User::findOrFail($request->id);

            $image_path = public_path("img/{$user->image_path}");
            if (!str_contains($image_path ,'default.png')) {
                unlink($image_path);
            }
    
            User::destroy($user->id);

            $history = History::create([
                'user_id' => Auth::id(),
                'nama_tabel' => 'data user',
                'jenis' => 'hapus',
                'nama_data' => $user->nama,
                'token_data' => $user->token,
            ]);

            return back();
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
