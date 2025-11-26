<?php

namespace App\Controllers\Configuration;

use App\Controllers\BaseController;
use App\Models\CategoryModel;

class CategoryController extends BaseController
{
    protected $categoryModel;

    public function __construct()
    {
        $this->categoryModel = new CategoryModel();
        $this->session = session();
    }

    // Vista principal
    public function index()
    {
        $data = [
            'title' => "Rubros",
            'titleMod' => "Administrar Rubros"
        ];
        return view('configuration/category', $data);
    }

    // DataTables AJAX
    public function getData()
    {
        $request = $this->request;
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'];
        $orderColumn = $request->getPost('order')[0]['column'];
        $orderDir = $request->getPost('order')[0]['dir'];

        $data = $this->categoryModel->getDatatables($start, $length, $searchValue, $orderColumn, $orderDir);
        $total = $this->categoryModel->countAllCategories();
        $filtered = $this->categoryModel->countFilteredCategories($searchValue);

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    // Obtener datos de una categoría
    public function edit($id)
    {
        $category = $this->categoryModel->getCategoryById($id);
        return $this->response->setJSON($category);
    }

    /**
     * Método que guarda o actualiza una categoría
     */
    public function save()
    {
        $categoryId   = $this->request->getPost('category_id');
        $clasifier    = trim($this->request->getPost('clasifier'));
        $name         = trim($this->request->getPost('name'));
        $created_user = $this->session->get('user') ?? 'system'; // o el usuario actual

        $data = [
            'clasifier'    => $clasifier,
            'name'         => $name,
            'created_user' => $created_user,
        ];

        // --- VALIDACIÓN: Campos requeridos ---
        if (empty($clasifier) || empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Debe completar todos los campos requeridos.'
            ]);
        }

        // --- VALIDACIÓN: Evitar duplicados ---
        $existing = $this->categoryModel
            ->where('LOWER(clasifier)', strtolower($clasifier))
            ->where('LOWER(name)', strtolower($name));

        // Si es actualización, ignoramos el registro actual
        if (!empty($categoryId)) {
            $existing->where('category_id !=', $categoryId);
        }

        $exists = $existing->first();

        if ($exists) {
            return $this->response->setJSON([
                'status'  => 'warning',
                'message' => 'Ya existe una categoría con ese nombre en este clasificador.'
            ]);
        }

        // --- INSERCIÓN o ACTUALIZACIÓN ---
        if (empty($categoryId)) {
            // Nuevo registro
            $inserted = $this->categoryModel->insert($data);

            if ($inserted) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Categoría registrada correctamente.'
                ]);
            }
        } else {
            // Actualización
            $updated = $this->categoryModel->update($categoryId, $data);

            if ($updated) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Categoría actualizada correctamente.'
                ]);
            }
        }

        // Si algo falla
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'No se pudo guardar la categoría. Intente nuevamente.'
        ]);
    }



    /**
     * Método para cambiar el estado de una categoría (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $categoryModel = new CategoryModel();

        // 📥 Leer JSON del body
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['status'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos inválidos recibidos.'
            ]);
        }

        $newStatus = $data['status']; // true o false

        // ✅ Actualizar el registro
        $categoryModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newStatus ? 'Categoría activada correctamente.' : 'Categoría dada de baja correctamente.'
        ]);
    }
}
