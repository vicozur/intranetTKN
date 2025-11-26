<?php

namespace App\Models;

use CodeIgniter\Model;

class LibraryModel extends Model
{
    protected $table            = 'directory.library';
    protected $primaryKey       = 'library_id';
    protected $allowedFields    = [
        'category_id',
        'identifier',
        'name',
        'created_user',
        'status'
    ];
    protected $useTimestamps    = true;
    protected $createdField     = 'created_at';
    protected $updatedField     = '';

    /**
     * 🔹 Obtener todas las librerías activas con su categoría
     */
    public function getActiveLibraries()
    {
        return $this->db->table('directory.library l')
            ->select('l.library_id, l.identifier, l.name, l.created_user, l.created_at, l.status, ca.name AS category_name')
            ->join('directory.category ca', 'ca.category_id = l.category_id', 'left')
            ->where('l.status', true)
            ->orderBy('l.name', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * 🔹 Obtener una librería con sus archivos adjuntos
     */
    public function getLibraryById($id)
    {
        $builder = $this->db->table('directory.library l');
        $builder->select('l.*, ca.name AS category_name');
        $builder->join('directory.category ca', 'ca.category_id = l.category_id', 'left');
        $builder->where('l.library_id', $id);
        $library = $builder->get()->getRowArray();

        if ($library) {
            $library['files'] = $this->db->table('directory.file f')
                ->select('f.file_id, f.name, f.extencion, f.url, f.status, f.created_user, f.created_at')
                ->where('f.library_id', $id)
                ->where('f.status', true)
                ->get()
                ->getResultArray();
        }

        return $library;
    }

    /**
     * 🔹 DataTables Server-side
     */

    /*
    public function getDatatables($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $builder = $this->db->table('directory.library l');
        $builder->select('
            l.library_id,
            l.identifier,
            l.name,
            l.created_user,
            l.created_at,
            l.status,
            l.category_id,
            ca.name AS category_name
        ');
        $builder->join('directory.category ca', 'ca.category_id = l.category_id', 'left');

        // 🔍 Filtro de búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('l.identifier', $searchValue)
                ->orLike('l.name', $searchValue)
                ->orLike('ca.name', $searchValue)
                ->orLike('l.created_user', $searchValue)
            ->groupEnd();
        }

        // 🔃 Ordenamiento
        $columns = ['l.library_id', 'l.identifier', 'l.name', 'ca.name', 'l.created_user', 'l.created_at', 'l.status', 'l.category_id'];
        if (isset($columns[$orderColumn])) {
            $builder->orderBy($columns[$orderColumn], $orderDir);
        } else {
            $builder->orderBy('l.library_id', 'DESC');
        }

        // 📄 Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    */

    public function getDatatables($start, $length, $searchValue, $orderColumn, $orderDir)
    {
        $builder = $this->db->table('directory.library l');
        $builder->select('
        l.library_id,
        l.identifier,
        l.name,
        l.created_user,
        l.created_at,
        l.status,
        ca.name AS category_name,
        (
            SELECT json_agg(json_build_object(
                \'file_id\', f.file_id,
                \'name\', f.name,
                \'url\', f.url,
                \'extencion\', f.extencion
            ))
            FROM directory.file f
            WHERE f.library_id = l.library_id 
            AND f.status = true
        ) AS files
    ');
        $builder->join('directory.category ca', 'ca.category_id = l.category_id', 'left');

        // 🔍 búsqueda
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('l.identifier', $searchValue)
                ->orLike('l.name', $searchValue)
                ->orLike('ca.name', $searchValue)
                ->orLike('l.created_user', $searchValue)
                ->groupEnd();
        }

        // 🔃 orden
        $columns = ['l.library_id', 'l.identifier', 'l.name', 'ca.name', 'l.created_user', 'l.created_at', 'l.status'];
        if (isset($columns[$orderColumn])) {
            $builder->orderBy($columns[$orderColumn], $orderDir);
        }

        // 📄 paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }


    /**
     * 🔹 Total de registros
     */
    public function countAllLibraries()
    {
        return $this->countAll();
    }

    /**
     * 🔹 Total filtrado (coincidente con búsqueda)
     */
    public function countFilteredLibraries($searchValue)
    {
        $builder = $this->db->table('directory.library l');
        $builder->join('directory.category ca', 'ca.category_id = l.category_id', 'left');

        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('l.identifier', $searchValue)
                ->orLike('l.name', $searchValue)
                ->orLike('ca.name', $searchValue)
                ->orLike('l.created_user', $searchValue)
            ->groupEnd();
        }

        return $builder->countAllResults();
    }

    /**
     * 🔹 Insertar o actualizar librería
     */
    public function saveLibrary($data, $id = null)
    {
        if ($id === null) {
            return $this->insert($data);
        }
        return $this->update($id, $data);
    }

    /**
     * 🔹 Subir archivo vinculado a una librería
     */
    public function saveFile($data)
    {
        return $this->db->table('directory.file')->insert($data);
    }

    /**
     * 🔹 Obtener archivos por librería
     */
    public function getFilesByLibrary($libraryId)
    {
        return $this->db->table('directory.file f')
            ->select('f.file_id, f.name, f.extencion, f.url, f.status, f.created_user, f.created_at')
            ->where('f.library_id', $libraryId)
            ->get()
            ->getResultArray();
    }

    /**
     * 🔹 Cambiar estado (activar/desactivar librería)
     */
    public function toggleStatus($id, $newStatus)
    {
        return $this->update($id, ['status' => $newStatus]);
    }

    /**
     * 🔹 Cambiar estado de un archivo
     */
    public function toggleFileStatus($fileId, $newStatus)
    {
        return $this->db->table('directory.file')
            ->where('file_id', $fileId)
            ->update(['status' => $newStatus]);
    }
    
}
