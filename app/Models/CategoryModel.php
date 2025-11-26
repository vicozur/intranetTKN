<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'directory.category';
    protected $primaryKey = 'category_id';

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    protected $allowedFields = [
        'clasifier',
        'name',
        'created_user',
        'status'
    ];

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    // Devuelve todas las categorías activas
    public function getActiveCategories()
    {
        return $this->where('status', true)
                    ->orderBy('name', 'ASC')
                    ->findAll();
    }

    // Devuelve una categoría por ID
    public function getCategoryById($id)
    {
        return $this->where('category_id', $id)
                    ->first();
    }

    public function getCategoryByClasifier($clasifier)
    {
        return $this->where('clasifier', $clasifier)
            ->where('status', true)
            ->findAll(); // ✅ Devuelve un array
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
        $builder->select('category_id, clasifier, name, created_user, created_at, status');

        // Filtro de búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('clasifier', $searchValue)
                ->orLike('name', $searchValue)
                ->orLike('created_user', $searchValue)
            ->groupEnd();
        }

        // Orden
        if (!empty($orderColumn) && !empty($orderDir)) {
            // Mapear índices de DataTables a columnas
            $columns = ['category_id', 'clasifier', 'name', 'created_user', 'created_at', 'status'];
            if (isset($columns[$orderColumn])) {
                $builder->orderBy($columns[$orderColumn], $orderDir);
            }
        } else {
            $builder->orderBy('category_id', 'DESC');
        }

        // Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    // Contar todas las filas
    public function countAllCategories()
    {
        return $this->countAll();
    }

    // Contar filas filtradas
    public function countFilteredCategories($searchValue)
    {
        $builder = $this->builder();
        $builder->select('category_id');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('clasifier', $searchValue)
                ->orLike('name', $searchValue)
                ->orLike('created_user', $searchValue)
            ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * Obtener el ID del país según su nombre
     */
    public function getCategoryIdByName($countryName)
    {
        return $this->db->table('city')
                        ->select('city_id')
                        ->where('LOWER(name)', strtolower($countryName))
                        ->get()
                        ->getRow('city_id');
    }

}
