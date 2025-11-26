<?php

namespace App\Models;

use CodeIgniter\Model;

class CountryModel extends Model
{
    protected $table      = 'directory.country';
    protected $primaryKey = 'country_id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'country_code',
        'name',
        'created_user',
        'status'
    ];

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    /**
     * Devuelve todos los países activos
     */
    public function getActiveCountries()
    {
        return $this->where('status', true)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    /**
     * Devuelve un país por su ID
     */
    public function getCountryById($id)
    {
        return $this->where('country_id', $id)
                    ->first();
    }

    /**
     * Devuelve datos para DataTables (server-side)
     * $start, $length -> paginación
     * $searchValue -> búsqueda global
     * $orderColumn, $orderDir -> ordenamiento
     */
    public function getDatatables($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $builder = $this->builder();

        // Seleccionamos columnas
        $builder->select('country_id, country_code, name, created_user, created_at, status');

        // Filtro de búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('country_code', $searchValue)
                ->orLike('name', $searchValue)
                ->orLike('created_user', $searchValue)
            ->groupEnd();
        }

        // Orden
        if (!empty($orderColumn) && !empty($orderDir)) {
            // Mapear índices de DataTables a columnas
            $columns = ['country_id', 'country_code', 'name', 'created_user', 'created_at', 'status'];
            if (isset($columns[$orderColumn])) {
                $builder->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $builder->orderBy('country_id', 'DESC');
        }

        // Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Contar todas las filas de la tabla
     */
    public function countAllCountries()
    {
        return $this->countAll();
    }

    /**
     * Contar filas filtradas (búsqueda)
     */
    public function countFilteredCountries($searchValue)
    {
        $builder = $this->builder();
        $builder->select('country_id');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('country_code', $searchValue)
                ->orLike('name', $searchValue)
                ->orLike('created_user', $searchValue)
            ->groupEnd();
        }

        return $builder->countAllResults();
    }

    public function getCountryByStatus()
    {
        return $this->select('country_id, name')
            ->where('status', true)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    /**
     * Obtener el ID del país según su nombre
     */
    public function getCountryIdByName($countryName)
    {
        return $this->db->table('country')
                        ->select('country_id')
                        ->where('LOWER(name)', strtolower($countryName))
                        ->get()
                        ->getRow('country_id');
    }


}
