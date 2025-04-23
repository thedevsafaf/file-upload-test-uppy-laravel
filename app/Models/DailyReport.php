<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    protected $fillable = [
        'report_date',
        'report_images',
        'report_docs',
    ];
    protected $casts = [
        'report_images' => 'array',
        'report_docs' => 'array',
    ];
}
