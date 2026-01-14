<?php

namespace App\Controllers\Administration;

use App\Controllers\BaseController;
use App\Models\AddressModel;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\DirectoryModel;
use App\Models\PhoneModel;

class DirectoryFormController extends BaseController
{
    protected $directoryModel, $categoryModel, $countryModel, $cityModel, $phoneModel, $addressModel;
    protected $session;

    public function __construct()
    {
        $this->directoryModel = new DirectoryModel();
        $this->categoryModel = new CategoryModel();
        $this->countryModel = new CountryModel();
        $this->cityModel = new CityModel();
        $this->phoneModel = new PhoneModel();
        $this->addressModel = new AddressModel();
        $this->session = session();
    }

    public function index($id=null)
    {
        
        $data = [
            'title' => "Cliente Form",
            'titleMod' => "Formulario de Cliente",
        ];
        //print_r($this->getCityList(1));
        $data['categoryList'] = $this->categoryModel->getCategoryByClasifier("Directorio") ?? [];
        $data['countryList'] = $this->countryModel->getCountryByStatus() ?? [];
        
        if ($id) {
            // Editar cliente
            $directory = $this->directoryModel->getClientFullData($id);
            //print_r($directory); //exit();
            $data['directory'] = $directory;
            $data['phones'] = $directory['phones'] ?? [];
            $data['addresses'] = $directory['addresses'] ?? [];

            // Traer ciudades según el país del cliente
            $data['cityList'] = $this->cityModel->getCityByCountryId($directory['country_id']);
        } else {
            // Nuevo cliente
            $data['directory'] = [];
            $data['phones'] = [];
            $data['addresses'] = [];
            $data['categoryList'] = $this->categoryModel->getCategoryByClasifier("Directorio");

            $countries = $this->countryModel->getCountryByStatus();
            $data['countryList'] = $countries;
            //print_r($data['countryList']); exit();
            // Traer ciudades del primer país activo
            $data['cityList'] = !empty($countries)
                ? $this->cityModel->getCityByCountryId($countries[0]['country_id'])
                : [];
        }

        return view('administration/directoryForm', $data);
    }

    // Controller
    public function getCityList($countryId = null)
    {
        //echo "getCityList($countryId = null)";
        if (empty($countryId)) {
            return $this->response->setJSON([]);
        }

        $cities = $this->cityModel->getCityByCountryId($countryId);
        return $this->response->setJSON($cities);
    }

    public function create()
    {
        $exists = $this->directoryModel
            ->where('company_name', $this->request->getPost('company_name'))
            ->where('client_name', $this->request->getPost('client_name'))
            ->first();

        if ($exists) {
            //return redirect()->back()->withInput()->with('error', 'Ya existe un estado físico con esta descripción.');
            return $this->response->setJSON([
                'status' => 'error', 
                'message' => 'Error, Ya existe el cliente: ' . $this->request->getPost('client_name')
            ]);
        }

        $data = [
            'country_id' => $this->request->getPost('country'),
            'city_id' => $this->request->getPost('city'),
            'category_id' => $this->request->getPost('category'),
            'company_name' => strtoupper($this->request->getPost('company_name')),
            'client_name' => strtoupper($this->request->getPost('client_name')),
            'client_post' => strtoupper($this->request->getPost('client_post')),
            'email' => $this->request->getPost('email'),
            'created_user' => session()->get('user'),
            'status' => true
        ];

        if ($this->directoryModel->insert($data)) {
             // Obtener el ID recién insertado
            $directoryId = $this->directoryModel->getInsertID();

            // Obtener códigos de city y country
            $city = $this->cityModel->getCityById($this->request->getPost('city'));
            $country = $this->countryModel->getCountryById($this->request->getPost('country'));

            $cityCode = $city['city_code'] ?? null;
            $countryCode = $country['country_code'] ?? null;

            // --- Guardar teléfonos ---
            $phones = $this->request->getPost('phonelist');          // array de números
            $internals = $this->request->getPost('internal_code'); // array de internos
            $countries = $this->request->getPost('countrycode'); // array de internos
            $regionals = $this->request->getPost('regioncode'); // array de internos

            if ($phones && is_array($phones)) {
                $phoneModel = new PhoneModel();
                foreach ($phones as $idx => $number) {
                    if (!empty($number)) { // evita insertar vacíos
                        $phoneModel->insert([
                            'directory_id'  => $directoryId,
                            'country_code'  => !empty($countries[$idx]) ? $countries[$idx] : null,
                            'region_code'   => !empty($regionals[$idx]) ? $regionals[$idx] : null,
                            'number'        => $number,
                            'internal_code' => !empty($internals[$idx]) ? $internals[$idx] : null,
                            'created_user'  => session()->get('userId'),
                            'status'        => true
                        ]);
                    }
                }
            }

            // --- Guardar direcciones ---
            $addresses = $this->request->getPost('address'); // array
            if ($addresses && is_array($addresses)) {
                $addressModel = new AddressModel();
                foreach ($addresses as $name) {
                    if (!empty($name)) { // evita insertar vacíos
                        $addressModel->insert([
                            'directory_id' => $directoryId,
                            'name'         => $name,
                            'created_user' => session()->get('userId'),
                            'status'       => true
                        ]);
                    }
                }
            }

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Exito, Se efectuo el registro del cliente ' . $this->request->getPost('client_name') . '.'
            ]);
        } else {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Error, No se pudo guardar el registro. Intente más tarde.'
            ]);
        }
    }

    /**
     * Actualizar cliente existente
     */
    public function update($id)
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Actualizar datos básicos del Directorio
            $this->directoryModel->update($id, [
                'category_id'  => $this->request->getPost('category'),
                'country_id'   => $this->request->getPost('country'),
                'city_id'      => $this->request->getPost('city'),
                'company_name' => $this->request->getPost('company_name'),
                'client_name'  => $this->request->getPost('client_name'),
                'client_post'  => $this->request->getPost('client_post'),
                'email'        => $this->request->getPost('email'),
            ]);

            // 2. PROCESAR TELÉFONOS
            $this->syncPhones($id);

            // 3. PROCESAR DIRECCIONES
            $this->syncAddresses($id);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception("Error al completar la transacción.");
            }

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Registro actualizado con éxito'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function syncPhones($directoryId)
    {
        $phones    = $this->request->getPost('phonelist') ?? [];
        $internals = $this->request->getPost('internal_code') ?? [];
        $countries = $this->request->getPost('countrycode') ?? [];
        $regionals = $this->request->getPost('regioncode') ?? [];

        // Obtenemos los teléfonos actuales de la BD
        $currentPhones = $this->phoneModel->where('directory_id', $directoryId)->findAll();
        $currentCount = count($currentPhones);
        $newCount = count($phones);

        // Iteramos basándonos en el máximo de ambos para cubrir todos los casos
        $max = max($currentCount, $newCount);

        for ($i = 0; $i < $max; $i++) {
            $data = [
                'directory_id'  => $directoryId,
                'country_code'  => $countries[$i] ?? null,
                'region_code'   => $regionals[$i] ?? null,
                'number'        => $phones[$i] ?? null,
                'internal_code' => $internals[$i] ?? null,
                'status'        => true
            ];

            if ($i < $newCount && $i < $currentCount) {
                // CASO 1: Existe en ambos -> ACTUALIZAR
                $this->phoneModel->update($currentPhones[$i]['phone_id'], $data);
            } elseif ($i < $newCount) {
                // CASO 2: Hay más en formulario -> INSERTAR
                $data['created_user'] = session()->get('userId');
                $this->phoneModel->insert($data);
            } elseif ($i < $currentCount) {
                // CASO 3: Hay menos en formulario -> ELIMINAR
                $this->phoneModel->delete($currentPhones[$i]['phone_id']);
            }
        }
    }

    private function syncAddresses($directoryId)
    {
        $addressesForm = $this->request->getPost('address') ?? [];
        $currentAddresses = $this->addressModel->where('directory_id', $directoryId)->findAll();

        $currentCount = count($currentAddresses);
        $newCount = count($addressesForm);
        $max = max($currentCount, $newCount);

        for ($i = 0; $i < $max; $i++) {
            if ($i < $newCount && !empty(trim($addressesForm[$i]))) {
                $data = [
                    'directory_id' => $directoryId,
                    'name'         => $addressesForm[$i],
                    'created_user' => session()->get('userId')
                ];

                if ($i < $currentCount) {
                    // ACTUALIZAR
                    $this->addressModel->update($currentAddresses[$i]['address_id'], $data);
                } else {
                    // INSERTAR NUEVO
                    $this->addressModel->insert($data);
                }
            } elseif ($i < $currentCount) {
                // ELIMINAR SI EL FORMULARIO TRAE MENOS
                $this->addressModel->delete($currentAddresses[$i]['address_id']);
            }
        }
    }

    

    public function cancel()
    {
        // Opcional: registrar logs o limpiar datos de sesión
        // session()->remove('temp_data');

        return redirect()->to(site_url('directorio'))
            ->with('info', 'Operación cancelada por el usuario.');
    }
}
