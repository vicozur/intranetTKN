<?php

namespace App\Models;

use CodeIgniter\Model;

class CityModel extends Model
{
    protected $table      = 'directory.city';
    protected $primaryKey = 'city_id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'country_id',
        'city_code',
        'name',
        'created_user',
        'status'
    ];

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    /**
     * 🔹 Obtener todas las ciudades activas (para selects o listas)
     */
    public function getActiveCities()
    {
        return $this->select('c.city_id, c.name, co.name AS country_name')
                    ->from('directory.city c')
                    ->join('directory.country co', 'co.country_id = c.country_id', 'left')
                    ->where('c.status', true)
                    ->orderBy('c.name', 'ASC')
                    ->findAll();
    }

    /**
     * 🔹 Obtener ciudad por ID
     */
    public function getCityById($id)
    {
        return $this->select('c.*, co.name AS country_name, co.country_code')
                    ->from('directory.city c')
                    ->join('directory.country co', 'co.country_id = c.country_id', 'left')
                    ->where('c.city_id', $id)
                    ->first();
    }

    /**
     * 🔹 Obtener ciudad por ID
     */
    public function getCityByCountryId($id)
    {
        return $this->distinct()
            ->select('city_id, name')
            ->where('country_id', $id)
            ->findAll();
    }


    /**
     * 🔹 DataTables Server-side
     */
    public function getDatatables($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $builder = $this->db->table('directory.city c');
        $builder->select('
            c.city_id,
            c.city_code,
            c.name,
            c.created_user,
            c.created_at,
            c.status,
            c.country_id,
            co.name AS country_name
        ');
        $builder->join('directory.country co', 'co.country_id = c.country_id', 'left');

        // 🔍 Filtro de búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('c.city_code', $searchValue)
                ->orLike('c.name', $searchValue)
                ->orLike('co.name', $searchValue)
                ->orLike('c.created_user', $searchValue)
            ->groupEnd();
        }

        // 🔃 Ordenamiento
        $columns = ['c.city_id', 'c.city_code', 'c.name', 'co.name', 'c.created_user', 'c.created_at', 'c.status'];
        if (isset($columns[$orderColumn])) {
            $builder->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $builder->orderBy('c.city_id', 'DESC');
        }

        // 📄 Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * 🔹 Total de registros
     */
    public function countAllCities()
    {
        return $this->countAll();
    }

    /**
     * 🔹 Total filtrado (coincidente con búsqueda)
     */
    public function countFilteredCities($searchValue)
    {
        $builder = $this->db->table('directory.city c');
        $builder->join('directory.country co', 'co.country_id = c.country_id', 'left');
        $builder->select('c.city_id');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('c.city_code', $searchValue)
                ->orLike('c.name', $searchValue)
                ->orLike('co.name', $searchValue)
                ->orLike('c.created_user', $searchValue)
            ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * 🔹 Insertar o actualizar ciudad
     */
    public function saveCity($data, $id = null)
    {
        if ($id === null) {
            return $this->insert($data);
        }
        return $this->update($id, $data);
    }

    /**
     * 🔹 Cambiar estado (activar/desactivar)
     */
    public function toggleStatus($id, $newStatus)
    {
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * Obtener el ID del país según su nombre
     */
    public function getCityIdByName($countryName)
    {
        return $this->db->table('city')
                        ->select('city_id')
                        ->where('LOWER(name)', strtolower($countryName))
                        ->get()
                        ->getRow('city_id');
    }
}
