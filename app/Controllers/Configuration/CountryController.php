<?php

namespace App\Controllers\Configuration;

use App\Controllers\BaseController;
use App\Models\CountryModel;

class CountryController extends BaseController
{
    protected $countryModel;

    public function __construct()
    {
        $this->countryModel = new CountryModel();
        $this->session = session();
    }

    // Vista principal
    public function index()
    {
        $data = [
            'title' => "Pais/Estado",
            'titleMod' => "Administrar Pais/Estado"
        ];
        return view('configuration/country', $data);
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

        $data = $this->countryModel->getDatatables($start, $length, $searchValue, $orderColumn, $orderDir);
        $total = $this->countryModel->countAllCountries();
        $filtered = $this->countryModel->countFilteredCountries($searchValue);

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
        $country = $this->countryModel->getCountryById($id);
        return $this->response->setJSON($country);
    }

    /**
     * Método que guarda o actualiza una categoría
     */
    public function save()
    {
        $countryId   = $this->request->getPost('country_id');
        $countryCode    = trim($this->request->getPost('country_code'));
        $name         = trim($this->request->getPost('name'));
        $created_user = $this->session->get('user') ?? 'system'; // o el usuario actual

        $data = [
            'country_code'    => $countryCode,
            'name'         => $name,
            'created_user' => $created_user,
            'status' => true
        ];

        // --- VALIDACIÓN: Campos requeridos ---
        if (empty($countryCode) || empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Debe completar todos los campos requeridos.'
            ]);
        }

        // --- VALIDACIÓN: Evitar duplicados ---
        $existing = $this->countryModel
            ->where('LOWER(country_code)', strtolower($countryCode))
            ->where('LOWER(name)', strtolower($name));

        // Si es actualización, ignoramos el registro actual
        if (!empty($countryId)) {
            $existing->where('country_id !=', $countryId);
        }

        $exists = $existing->first();

        if ($exists) {
            return $this->response->setJSON([
                'status'  => 'warning',
                'message' => 'Ya existe un Pais/Estado con ese codigo Pais.'
            ]);
        }

        // --- INSERCIÓN o ACTUALIZACIÓN ---
        if (empty($countryId)) {
            // Nuevo registro
            unset($data['country_id']); // 🔹 No enviar el ID al insertar
            $inserted = $this->countryModel->insert($data);

            if ($inserted) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Pais/Estado registrado correctamente.'
                ]);
            }
        } else {
            // Actualización
            $updated = $this->countryModel->update($countryId, $data);

            if ($updated) {
                return $this->response->setJSON([
                    'status'  => 'success',
                    'message' => 'Pais/Estado actualizado correctamente.'
                ]);
            }
        }

        // Si algo falla
        return $this->response->setJSON([
            'status'  => 'error',
            'message' => 'No se pudo guardar el Pais/Estado. Intente nuevamente.'
        ]);
    }



    /**
     * Método para cambiar el estado de una categoría (activar/desactivar)
     */
    public function toggleStatus($id)
    {
        $countryModel = new CountryModel();

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
        $countryModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newStatus ? 'Pais/Estado activado correctamente.' : 'Pais/Estado dado de baja correctamente.'
        ]);
    }
}
