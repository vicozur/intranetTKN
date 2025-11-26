<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table = 'directory.user';   // ahora apunta a schema + tabla
    protected $primaryKey = 'user_id';       // en PostgreSQL todo quedó en minúscula
    protected $useAutoIncrement = true;
    protected $returnType = 'array'; // también podrías usar 'object'
    protected $useSoftDeletes = false;

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    
    // Campos permitidos en inserts/updates
    protected $allowedFields = [
        'username',
        'password',
        'name',
        'lastname',
        'email',
        'phone',
        'created_user',
        'status'
    ];

    protected bool $allowEmptyInserts = false;
    protected bool $updateOnlyChanged = true;
    
    // Si quieres timestamps automáticos descomenta y ajusta
    /*
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'createdate';
    protected $updatedField  = 'createdate';
    */

    /**
     * Obtener un usuario por su username
     */
    public function getUserByUserName($user)
    {
        return $this->where('username', $user)
                    ->where('status', true)   // boolean en PostgreSQL
                    ->first();
    }

    /**
     * Obtener lista completa de usuarios con sus perfiles
     */
    public function getFullListUsers()
    {
        $builder = $this->db->table('directory.user u');
        $builder->select('
            u.user_id, 
            u.username, 
            u.name, 
            u.lastname, 
            u.email, 
            u.phone, 
            u.created_at, 
            u.created_user, 
            u.status,
            a.assign_id, 
            p.profile_id,
            p.name AS nameprofile,
            a.assign_id
        ');
        $builder->join('directory.assign a', 'a.user_user_id = u.user_id', 'left');
        $builder->join('directory.profile p', 'p.profile_id = a.profile_profile_id', 'left');
        $builder->orderBy('u.username', 'ASC');

        return $builder->get()->getResultArray();
    }

    /**
     * 🔹 Obtener lista de usuarios con su perfil asignado (JOIN con assign y profile)
     */
    public function getDatatables($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $columns = [
            'u.user_id', 'u.username', 'u.name', 'u.lastname', 'u.email', 'u.phone',
            'p.name', 'u.status', 'p.profile_id', 'assign_id'
        ];

        $builder = $this->db->table('directory."user" u')
            ->select('u.user_id, u.username, u.name, u.lastname, u.email, u.phone, p.name as profile, u.status, p.profile_id, a.assign_id')
            ->join('directory.assign a', 'a.user_user_id = u.user_id', 'left')
            ->join('directory.profile p', 'p.profile_id = a.profile_profile_id', 'left');

        // Filtro de búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('u.username', $searchValue)
                ->orLike('u.name', $searchValue)
                ->orLike('u.lastName', $searchValue)
                ->orLike('p.name', $searchValue)
            ->groupEnd();
        }

        // Ordenamiento
        if (isset($columns[$orderColumn])) {
            $builder->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $builder->orderBy('u.user_id', 'DESC');
        }

        // Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countAllUsers()
    {
        return $this->countAll();
    }

    public function countFilteredUsers($searchValue)
    {
        $builder = $this->db->table('directory."user" u')
            ->select('u.user_id')
            ->join('directory.assign a', 'a.user_user_id = u.user_id', 'left')
            ->join('directory.profile p', 'p.profile_id = a.profile_profile_id', 'left');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('u.username', $searchValue)
                ->orLike('u.name', $searchValue)
                ->orLike('p.name', $searchValue)
            ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * 🔹 Obtener perfiles disponibles
     */
    public function getProfiles()
    {
        return $this->db->table('directory.profile')
            ->select('profile_id, name')
            ->where('status', true)
            ->orderBy('name', 'ASC')
            ->get()->getResultArray();
    }
}
