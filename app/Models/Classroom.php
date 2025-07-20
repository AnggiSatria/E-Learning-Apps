<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classes';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['id', 'name'];

    protected static function booted()
    {
        static::deleting(function ($classroom) {
            // Hapus relasi cascade
            $classroom->classMembers()->delete(); // optional karena sudah cascade
            $classroom->tasks()->delete();        // optional karena sudah cascade

            // Set null pada student & teacher yang pakai class_id
            $classroom->students()->update(['class_id' => null]);
            $classroom->teachers()->update(['class_id' => null]);
        });
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function teachers()
    {
        return $this->hasMany(Teacher::class, 'class_id');
    }

    public function classMembers()
    {
        return $this->hasMany(ClassMember::class, 'class_id');
    }

    public function tasks()
    {
        return $this->hasMany(Task::class, 'class_id');
    }
}
