<?php
namespace App\Models;

use CodeIgniter\Model;

class PhoneModel extends Model
{
    protected $table      = 'phone';
    protected $primaryKey = 'phone_id';
    
    protected $allowedFields = [
        'directory_id',
        'country_code',
        'region_code',
        'number',
        'internal_code',
        'created_user',
        'created_at',
        'status'
    ];

    protected $useTimestamps = true; // opcional, si quieres manejar created_at automáticamente
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // no se usa updated_at

    public function getPhoneByDirectoryId($id)
    {
        return $this->select('p.*')
                    ->from('directory.phone p')
                    ->where('p.directory_id', $id)
                    ->findAll();
    }

}
