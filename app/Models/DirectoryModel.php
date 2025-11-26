<?php

namespace App\Models;

use CodeIgniter\Model;

class DirectoryModel extends Model
{
    // ⚙️ Propiedades Principales de la Tabla
    protected $table = 'directory';
    protected $primaryKey = 'directory_id';

    protected $allowedFields = [
        'city_id',
        'country_id',
        'category_id',
        'company_name',
        'client_name',
        'client_post',
        'email',
        'created_user',
        'status',
        'created_at' // Aunque se actualiza automáticamente, se incluye si es necesario insertarla manualmente
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No usamos updated_at en tu esquema

// ----------------------------------------------------------------------
// MÉTODOS DE DATATABLES (Server-Side)
// ----------------------------------------------------------------------

    /**
     * @param array $request Los datos POST de DataTables
     * @return object Retorna el objeto Builder con JOINS y filtros de búsqueda aplicados.
     * Es la base para getDataTable y countFilteredData.
     */
    protected function _getDatatableQuery($request)
    {
        $builder = $this->db->table('directory d');

        // --- 1. JOINS ---
        $builder->join('city c', 'c.city_id = d.city_id', 'left');
        $builder->join('country co', 'co.country_id = d.country_id', 'left');
        $builder->join('category cat', 'cat.category_id = d.category_id', 'left');
        // JOINS 1-a-N que requieren agregación (STRING_AGG)
        $builder->join('phone p', 'p.directory_id = d.directory_id', 'left');
        $builder->join('address a', 'a.directory_id = d.directory_id', 'left');
/*
        // --- 2. Lógica de Búsqueda global (Server-Side) ---
        if (!empty($request['search']['value'])) {
            $search = $request['search']['value'];
            $builder->groupStart()
                ->like('d.company_name', $search)
                ->orLike('d.client_name', $search)
                ->orLike('d.client_post', $search)
                ->orLike('d.email', $search)
                ->orLike('c.name', $search)
                ->orLike('co.name', $search)
                ->orLike('cat.name', $search)
                // Usamos ::text para PostgreSQL si 'p.number' no es VARCHAR
                ->orLike('p.number::text', $search)
                ->orLike('a.name', $search)
                ->groupEnd();
        }
*/
        if (!empty($request['search']['value'])) {
            $search = $this->db->escapeLikeString($request['search']['value']); // Escapamos la búsqueda
            $builder->groupStart()
                ->like('d.company_name', $search)
                ->orLike('d.client_name', $search)
                ->orLike('d.client_post', $search)
                ->orLike('d.email', $search)
                ->orLike('c.name', $search)
                ->orLike('co.name', $search)
                ->orLike('cat.name', $search)

                // ✅ CORRECCIÓN: Usar orWhere (o where) con el casting y LIKE en el SQL crudo (3er param false)
                // Esto le dice a CI4: No cites el nombre de la columna, usa esta expresión tal cual.
                ->orWhere("p.number::text ILIKE '%{$search}%'", null, false)
                // Nota: ILIKE es la forma sensible a mayúsculas/minúsculas de LIKE en PostgreSQL, 
                // y usamos el valor escapado directamente.

                ->orLike('a.name', $search)
                ->groupEnd();
        }

        return $builder;
    }


    /**
     * Devuelve los datos paginados, ordenados y con las agregaciones para DataTables.
     */
    public function getDataTable($request)
    {
        $builder = $this->_getDatatableQuery($request);

        // --- 3. SELECT con Agregación (STRING_AGG) ---
        $builder->select("
            d.directory_id, d.company_name, d.client_name, d.client_post, d.email, 
            c.name AS city_name, co.name AS country_name, cat.name AS category_name, 
            STRING_AGG(DISTINCT p.number::text, ', ') AS phones,
            STRING_AGG(DISTINCT a.name, ', ') AS addresses,
            d.created_at, d.created_user, d.status
        ", false);

        // --- 4. Ordenamiento (Asegúrate que el índice coincida con el array JS) ---
        if (isset($request['order'])) {
            $colIndex = $request['order'][0]['column'];
            $dir      = $request['order'][0]['dir'];

            // Mapeo de columnas (se usa el índice del DataTables - 1 si el índice 0 en JS es 'data: null')
            $orderColumns = [
                'd.directory_id',
                'd.company_name',
                'd.client_name',
                'd.client_post',
                'd.email',
                'city_name',
                'country_name',
                'category_name',
                'phones',
                'addresses',
                'd.created_user',
                'd.created_at',
                'd.status'
            ];

            // El índice 0 en JS es el contador (data: null). El índice 1 en JS es d.company_name.
            // Si $colIndex > 0 (no es el contador/acciones), ordenamos usando el índice ajustado.
            if ($colIndex > 0 && isset($orderColumns[$colIndex - 1])) {
                $builder->orderBy($orderColumns[$colIndex - 1], $dir);
            }
        } else {
            $builder->orderBy('d.directory_id', 'DESC');
        }

        // --- 5. Agrupamiento Obligatorio (PostgreSQL) ---
        // Se agrupa por todas las columnas que no son agregadas.
        $builder->groupBy('
            d.directory_id, d.company_name, d.client_name, d.client_post, d.email, 
            c.name, co.name, cat.name, d.created_at, d.created_user, d.status
        ');

        // --- 6. Paginación ---
        if ($request['length'] != -1) {
            $builder->limit($request['length'], $request['start']);
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Total de registros sin filtrar (usa la función nativa countAll).
     */
    public function countAllData()
    {
        return $this->countAll();
    }

    /**
     * Total filtrado según búsqueda. (OPTIMIZADO para evitar GROUP BY costoso en el conteo)
     */
    public function countFilteredData($request)
    {
        // ❌ NO llama a getDataTable().
        // ✅ Usa la consulta base con los filtros y cuenta los IDs distintos.
        $builder = $this->_getDatatableQuery($request);

        // Seleccionar ID distinto antes de contar es la forma más segura con joins 1-a-N.
        return $builder->select('d.directory_id', false)->distinct()->countAllResults();
    }

// ----------------------------------------------------------------------
// MÉTODOS DE SOPORTE (Edición, etc.)
// ----------------------------------------------------------------------

    /**
     * Obtener un registro completo por ID, incluyendo datos relacionados.
     */
    public function getClientFullData($id)
    {
        $client = $this
            // Usamos $this->table para evitar ambigüedad sin alias 'd'
            ->select($this->table . '.*, c.name AS city_name, co.name AS country_name, cat.name AS category_name', false)
            ->join('city c', 'c.city_id = ' . $this->table . '.city_id')
            ->join('country co', 'co.country_id = ' . $this->table . '.country_id')
            ->join('category cat', 'cat.category_id = ' . $this->table . '.category_id')
            ->where($this->table . '.directory_id', $id)
            ->first();

        if ($client) {
            // Asumo que tienes un PhoneModel y un AddressModel
            $client['phones'] = model('App\Models\PhoneModel')->where('directory_id', $id)->findAll();
            $client['addresses'] = model('App\Models\AddressModel')->where('directory_id', $id)->findAll();
        }

        return $client;
    }
}
