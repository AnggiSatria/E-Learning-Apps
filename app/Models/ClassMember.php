<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassMember extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'class_id', 'user_id', 'role'];

    public function class() {
        return $this->belongsTo(Classroom::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }
}
