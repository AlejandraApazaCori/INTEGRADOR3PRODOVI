<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleDriveReport extends Model
{
    protected $fillable = [
        'report_key',
        'file_id',
        'folder_id',
        'file_name',
        'web_view_link',
    ];
}
