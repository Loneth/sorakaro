<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = ['level_id', 'title', 'order', 'pass_rate', 'is_assessment', 'assessment_type'];

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function questions()
    {
        return $this->hasMany(\App\Models\Question::class);
    }
}

