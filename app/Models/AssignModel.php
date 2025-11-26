<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignModel extends Model
{
    protected $table      = 'directory.assign';
    protected $primaryKey = 'assign_id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'user_user_id',
        'profile_profile_id',
        'created_user',
        'status'
    ];

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

}
