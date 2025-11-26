<?php
namespace App\Models;

use CodeIgniter\Model;

class AddressModel extends Model
{
    protected $table      = 'address';
    protected $primaryKey = 'address_id';
    
    protected $allowedFields = [
        'directory_id',
        'name',
        'created_user',
        'created_at',
        'status'
    ];

    protected $useTimestamps = true; // opcional
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    
}
