<?php

namespace App\Controllers\Administration;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\FileModel;
use App\Models\LibraryModel;

class LibraryController extends BaseController
{
    protected $libraryModel, $fileModel, $categoryModel;
    protected $session;

    public function __construct()
    {
        $this->libraryModel = new LibraryModel();
        $this->fileModel = new FileModel();
        $this->categoryModel = new CategoryModel();
        $this->session = session();
    }

    public function index()
    {
        //print_r($this->categoryModel->getCategoryByClasifier("Proyectos") ); exit();
        $data = [
            'title' => "Proyectos",
            'titleMod' => "Administrar Info. Proyectos",
            'categoryList' => $this->categoryModel->getCategoryByClasifier("Proyectos")
        ];

        return view('administration/library', $data);
    }

    /**
     * 📊 Cargar datos para DataTables (server-side)
     */
    public function getData()
    {
        $request = service('request');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';
        $orderColumn = $request->getPost('order')[0]['column'] ?? 0;
        $orderDir = $request->getPost('order')[0]['dir'] ?? 'asc';

        // 🔹 Obtener registros con paginación y búsqueda
        $data = $this->libraryModel->getDatatables($start, $length, $searchValue, $orderColumn, $orderDir);

        // 🔹 Agregar lista de archivos asociados
        $db = \Config\Database::connect();
        foreach ($data as &$row) {
            $files = $db->table('directory.file')
                ->select('file_id, name, url, extencion')
                ->where('library_id', $row['library_id'])
                ->where('status', true)
                ->get()
                ->getResultArray();

            $row['files'] = $files; // 👈 Array listo para usar en el front
        }

        // 🔹 Total general y filtrado
        $totalRecords = $this->libraryModel->countAllLibraries();
        $filteredRecords = $this->libraryModel->countFilteredLibraries($searchValue);

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }



    /**
     * 💾 Guardar o actualizar proyecto (library)
     */
    public function save()
    {
        $id = $this->request->getPost('library_id');
        $identifier = $this->request->getPost('identifier');
        $nameP = $this->request->getPost('name');
        $data = [
            'category_id'  => $this->request->getPost('categoryId'),
            'identifier'   => $identifier,
            'name'         => strtoupper($nameP),
            'created_user' => session('username') ?? 'system',
            'status'       => true
        ];

        // --- VALIDACIÓN: Campos requeridos ---
        if (empty($identifier) || empty($nameP)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Debe completar todos los campos requeridos.'
            ]);
        }

        // --- VALIDACIÓN: Evitar duplicados ---
        $existing = $this->libraryModel
            ->groupStart()
            ->where('LOWER(identifier)', strtolower($identifier))
            ->orWhere('LOWER(name)', strtolower($nameP))
            ->groupEnd()
            ->first();


        // Si es actualización, ignoramos el registro actual
        $existing = $this->libraryModel
            ->groupStart()
            ->where('LOWER(identifier)', strtolower($identifier))
            ->orWhere('LOWER(name)', strtolower($nameP))
            ->groupEnd()
            ->first();


        // Si es actualización, ignoramos el registro actual
        if ($existing && (empty($id) || $existing['library_id'] != $id)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Ya existe un registro con ese identificador o nombre.'
            ]);
        }

        if (empty($id)) {
            unset($data['library_id']); // 🔹 No enviar el ID al insertar
            $inserted = $this->libraryModel->insert($data);
            if ($inserted) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Informacion de proyecto registrada correctamente.'
                ]);
            }
        } else {
            $updated = $this->libraryModel->update($id, $data);
            if ($updated) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Informacion de proyecto actualizada correctamente.'
                ]);
            }
        }
    }

    /**
     * 📁 Subida de archivos asociados a una librería
     */
    public function uploadFile()
    {
        helper(['form', 'filesystem']);

        $libraryId   = $this->request->getPost('upload_library_id');
        $tipoCarga   = $this->request->getPost('tipo_carga'); // 0 = Solo archivo, 1 = En carpeta
        $carpetaName = trim($this->request->getPost('carpetaName') ?? '');
        $files       = $this->request->getFiles();

        if (!$libraryId || !isset($files['files'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Faltan datos']);
        }

        // --- USO DE tipo_carga PARA ARMAR EL PATH ---
        // Si tipo_carga es "1" Y el nombre no está vacío, usamos la subcarpeta.
        // De lo contrario, forzamos la ruta a la raíz del proyecto.
        $subPath = ($tipoCarga === "1" && $carpetaName !== "") ? $carpetaName . '/' : "";

        $fullRelativePath = 'uploads/library/' . $libraryId . '/' . $subPath;
        $uploadPath       = FCPATH . $fullRelativePath;

        // --- VERIFICACIÓN Y CREACIÓN ---
        if (!is_dir($uploadPath)) {
            // El tercer parámetro 'true' permite crear libraryId y subPath al mismo tiempo
            if (!mkdir($uploadPath, 0777, true)) {
                return $this->response->setJSON(['success' => false, 'message' => 'Error al crear directorio']);
            }
        }

        $fileModel  = new \App\Models\FileModel();
        $savedFiles = [];

        foreach ($files['files'] as $file) {
            if ($file->isValid() && !$file->hasMoved()) {
                $newName = $file->getRandomName();
                $file->move($uploadPath, $newName);

                $fileData = [
                    'library_id'   => $libraryId,
                    'name'         => $file->getClientName(),
                    'extencion'    => $file->getClientExtension(),
                    'url'          => $fullRelativePath . $newName, // URL dinámica
                    'created_user' => session('username') ?? 'system',
                    'status'       => true,
                ];

                $fileModel->insert($fileData);
                $savedFiles[] = $fileData;
            }
        }

        return $this->response->setJSON(['success' => true, 'files' => $savedFiles]);
    }


    /**
     * 🔄 Activar / Desactivar registro
     */
    public function toggleStatus($id)
    {
        $record = $this->libraryModel->find($id);

        if (!$record) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Registro no encontrado']);
        }

        $newStatus = !$record['status'];
        $this->libraryModel->toggleStatus($id, $newStatus);

        $msg = $newStatus ? 'Registro activado' : 'Registro desactivado';
        return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
    }
}
