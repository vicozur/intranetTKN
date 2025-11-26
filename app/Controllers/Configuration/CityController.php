<?php

namespace App\Controllers\Configuration;

use App\Controllers\BaseController;
use App\Models\CityModel;
use App\Models\CountryModel;

class CityController extends BaseController
{
    protected $cityModel, $countryModel;

    public function __construct()
    {
        $this->cityModel = new CityModel();
        $this->countryModel = new CountryModel();
        $this->session = session();
    }

    public function index()
    {
        $data = [
            'title' => "Ciudades",
            'titleMod' => "Administrar ciudades",
            'countries' => $this->countryModel->getActiveCountries()
        ];
        return view('configuration/city', $data);
    }

    // 🔹 DataTables server-side
    public function getData()
    {
        $request = $this->request;
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'];
        $orderColumn = $request->getPost('order')[0]['column'];
        $orderDir = $request->getPost('order')[0]['dir'];

        $data = $this->cityModel->getDatatables($start, $length, $searchValue, $orderColumn, $orderDir);
        $total = $this->cityModel->countAllCities();
        $filtered = $this->cityModel->countFilteredCities($searchValue);

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    // 🔹 Guardar o actualizar ciudad
    public function save()
    {
        $cityId = $this->request->getPost('city_id');
        $countryId = $this->request->getPost('country_id');
        $cityCode = trim($this->request->getPost('city_code'));
        $name = trim($this->request->getPost('name'));
        $created_user = $this->session->get('user') ?? 'system';

        if (empty($countryId) || empty($name)) {
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => 'Debe completar todos los campos requeridos.'
            ]);
        }

        $data = [
            'country_id' => $countryId,
            'city_code' => $cityCode,
            'name' => $name,
            'created_user' => $created_user,
            'status' => true
        ];

        if (empty($cityId)) {
            unset($data['city_id']); // ✅ dejar que PostgreSQL genere el ID
            $inserted = $this->cityModel->insert($data);
            if ($inserted) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Ciudad registrada correctamente.'
                ]);
            }
        } else {
            $updated = $this->cityModel->update($cityId, $data);
            if ($updated) {
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Ciudad actualizada correctamente.'
                ]);
            }
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'No se pudo guardar la ciudad.'
        ]);
    }

    // 🔹 Cambiar estado (activar/desactivar)
    public function toggleStatus($id)
    {
        $data = $this->request->getJSON(true);
        if (!$data || !isset($data['status'])) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Datos inválidos.']);
        }

        $newStatus = $data['status'];
        $this->cityModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newStatus ? 'Ciudad activada correctamente.' : 'Ciudad desactivada correctamente.'
        ]);
    }
}
