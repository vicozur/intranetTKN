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
            'company_name' => $this->request->getPost('company_name'),
            'client_name' => $this->request->getPost('client_name'),
            'client_post' => $this->request->getPost('client_post'),
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

            if ($phones && is_array($phones)) {
                $phoneModel = new PhoneModel();
                foreach ($phones as $idx => $number) {
                    if (!empty($number)) { // evita insertar vacíos
                        $phoneModel->insert([
                            'directory_id'  => $directoryId,
                            'country_code'  => $countryCode,
                            'region_code'     => $cityCode,
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
            // Actualizar datos básicos
            $cityCountryData = $this->cityModel->getCityById($this->request->getPost('city'));

            $this->directoryModel->update($id, [
                'category_id'  => $this->request->getPost('category'),
                'country_id'   => $this->request->getPost('country'),
                'city_id'      => $this->request->getPost('city'),
                'company_name' => $this->request->getPost('company_name'),
                'client_name'  => $this->request->getPost('client_name'),
                'client_post'  => $this->request->getPost('client_post'),
                'email'        => $this->request->getPost('email'),
            ]);

            // Actualizar teléfonos: borrar anteriores y volver a insertar
            //$this->phoneModel->where('directory_id', $id)->delete();
            $phoneList = $this->phoneModel->getPhoneByDirectoryId($id); 
            for ($i=0; $i < count($phoneList); $i++) { 
                
            }
            $phones = $this->request->getPost('phone');
            if ($phones) {
                foreach ($phones as $p) {
                    if (trim($p) != '') {
                        $this->phoneModel->insert([
                            'directory_id' => $id,
                            'number'       => $p,
                            'country_code' => $cityCountryData["country_code"],
                            'city_code' => $cityCountryData["city_code"],
                            'created_user' => session()->get('userId'),
                        ]);
                    }
                }
            }

            // Actualizar direcciones: igual, borrar e insertar
            $this->addressModel->where('directory_id', $id)->delete();
            $addresses = $this->request->getPost('address');
            if ($addresses) {
                foreach ($addresses as $a) {
                    if (trim($a) != '') {
                        $this->addressModel->insert([
                            'directory_id' => $id,
                            'name'         => $a,
                            'created_user' => session()->get('userId'),
                        ]);
                    }
                }
            }

            $db->transComplete();

            return $this->response->setJSON([
                'status'  => 'success',
                'message' => 'Cliente actualizado con éxito'
            ]);
        } catch (\Exception $e) {
            $db->transRollback();
            return $this->response->setJSON([
                'status'  => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
