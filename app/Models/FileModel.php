<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table            = 'directory.file';
    protected $primaryKey       = 'file_id';
    protected $allowedFields    = [
        'library_id',
        'name',
        'extencion',
        'url',
        'created_user',
        'status'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';
}
