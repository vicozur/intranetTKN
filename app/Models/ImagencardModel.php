<?php

namespace App\Models;

use CodeIgniter\Model;

class ImagencardModel extends Model
{
    protected $table            = 'directory.imagencard';
    protected $primaryKey       = 'imagencard_id';
    protected $allowedFields    = [
        'directory_id',
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
