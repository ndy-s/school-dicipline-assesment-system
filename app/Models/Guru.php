<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\MataPelajaran;
use App\Models\Pelanggaran;

class Guru extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function mata_pelajarans() {
        return $this->hasMany(MataPelajaran::class);
    }

    public function pelanggarans() {
        return $this->hasMany(Pelanggaran::class);
    }
}
