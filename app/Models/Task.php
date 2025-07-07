<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Classroom;

class Task extends Model
{
    use HasFactory;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'description',
        'class_id',
        'due_date',
        'media_path'
    ];

    // Auto-append media_url saat model dijadikan JSON
    protected $appends = ['media_url'];

    // Accessor media_url
    public function getMediaUrlAttribute()
    {
        return $this->media_path 
            ? asset('storage/' . $this->media_path)
            : null;
    }

    public function class()
    {
        return $this->belongsTo(Classroom::class);
    }
}
