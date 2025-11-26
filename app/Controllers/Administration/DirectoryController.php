<?php

namespace App\Controllers\Administration;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\CityModel;
use App\Models\CountryModel;
use App\Models\DirectoryModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DirectoryController extends BaseController
{
    protected $directoryModel, $countryModel, $cityModel, $categoryModel;
    protected $session;

    public function __construct()
    {
        $this->directoryModel = new DirectoryModel();
        $this->countryModel = new CountryModel();
        $this->cityModel = new CityModel();
        $this->categoryModel = new CategoryModel();
        $this->session = session();
    }

    public function index()
    {
        $data = [
            'title' => "Directorio",
            'titleMod' => "Administración de tarjetas",
        ];

        return view('administration/directory', $data);
    }

    // DataTables AJAX
    public function getData()
    {
        $request = $this->request->getPost();

        $data = $this->directoryModel->getDataTable($request);
        $total = $this->directoryModel->countAllData();
        $filtered = $this->directoryModel->countFilteredData($request);

        return $this->response->setJSON([
            'draw' => intval($request['draw']),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    // Activar / Desactivar registro
    public function toggleStatus($id)
    {
        $current = $this->directoryModel->find($id);
        if (!$current) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Registro no encontrado']);
        }

        $newStatus = !$current['status'];
        $this->directoryModel->update($id, ['status' => $newStatus]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newStatus ? 'Registro activado' : 'Registro dado de baja'
        ]);
    }

    // Obtener datos para edición
    public function edit($id)
    {
        $data = $this->directoryModel->getClientFullData($id);
        return $this->response->setJSON($data);
    }

    // Guardar / actualizar
    public function save()
    {
        $post = $this->request->getPost();
        $id = $post['directory_id'] ?? null;

        $data = [
            'city_id' => $post['city_id'],
            'country_id' => $post['country_id'],
            'category_id' => $post['category_id'],
            'company_name' => $post['company_name'],
            'client_name' => $post['client_name'],
            'client_post' => $post['client_post'],
            'email' => $post['email'],
            'created_user' => $this->session->get('user') ?? 'system',
            'status' => isset($post['status']) ? (bool)$post['status'] : true
        ];

        if ($id) {
            $this->directoryModel->update($id, $data);
            $message = 'Registro actualizado';
        } else {
            $this->directoryModel->insert($data);
            $message = 'Registro creado';
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $message]);
    }

    public function importar()
    {
        try {
            $files = $this->request->getFiles();

            if (!isset($files['files'])) {
                throw new \Exception("No se recibieron archivos");
            }

            foreach ($files['files'] as $file) {
                if (!$file->isValid()) {
                    throw new \Exception("Archivo inválido: " . $file->getErrorString());
                }

                $this->procesarCSV($file->getTempName());
            }

            return "Archivos procesados correctamente.";
        } catch (\Throwable $e) {
            log_message('error', $e->getMessage());
            return "Error durante la importación: " . $e->getMessage();
        }
    }

    private function procesarCSV($path)
    {
        $handle = fopen($path, 'r');
        $delimiter = ';'; // Confirmado que usas punto y coma
        $resultados_finales = [];

        // Lee la primera línea como encabezados
        $headers = fgetcsv($handle, 0, $delimiter);

        // Si hay problemas con el encoding, puedes forzar la codificación de origen
        // $source_encoding = 'Windows-1252'; 

        while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {

            // 1. Manejo de Encoding (Si el error persiste, descomenta la línea de 'Windows-1252' arriba)
            // La conversión a UTF-8 debe ser lo primero
            $data = array_map(function ($v) /*use ($source_encoding)*/ {
                $v = trim($v); // Limpiamos espacios extra inmediatamente
                // return mb_convert_encoding($v, 'UTF-8', $source_encoding); // Usar si 'auto' falla
                return mb_convert_encoding($v, 'UTF-8', 'auto'); // Mejor dejar 'auto' si no da error
            }, $data);

            // Aseguramos que los arrays tengan el mismo número de elementos antes de combinar
            if (count($headers) !== count($data)) {
                log_message('warning', 'Fila saltada por número de columnas inconsistente.');
                continue; // Saltamos filas mal formadas
            }

            // 2. Creación del Array Asociativo con TODOS los campos
            $row = array_combine($headers, $data);

            // 3. Extracción y Evaluación de TODOS los campos

            // Campos que se comparan con catálogos
            $country  = $row['country'] ?? '';
            $city     = $row['city'] ?? '';
            $category = $row['category'] ?? '';

            // Campos que se usan directamente para la inserción/evaluación
            $empresa  = $row['empresa'] ?? '';
            $name     = $row['name'] ?? '';
            $profile  = $row['profile'] ?? '';
            $numbers  = $row['numbers'] ?? '';
            $email    = $row['email'] ?? '';
            $address  = $row['address'] ?? '';

            // 4. Búsqueda de IDs en Modelos
            // Asegúrate de que tus modelos están cargados (ej. en el constructor)

            $countryObj = $this->countryModel->getCountryIdByName(trim($country));
            $cityObj = $this->cityModel->getCityIdByName(trim($city));
            $categoryObj = $this->categoryModel->getCategoryIdByName(trim($category)); // Corregido: Asumo CategoryModel

            $id_country = $countryObj ? ($countryObj->id ?? null) : null;
            $id_city = $cityObj ? ($cityObj->id ?? null) : null;
            $id_category = $categoryObj ? ($categoryObj->id ?? null) : null;

            // 5. Evaluación para la Inserción (o Almacenamiento Temporal)
            $exists = $this->directoryModel->where('email', $email)->get()->getRow();

            // Creamos el array de datos para insertar (incluyendo los IDs y todos los demás campos)
            $clientData = [
                'country_id'   => $id_country,
                'city_id'      => $id_city,
                'category_id'  => $id_category,
                'company_name' => $empresa,
                'client_name'  => $name,
                'client_post'  => $profile,
                'email'        => $email,
                'created_user' => session()->get('user'),
                'status'       => true
            ];

            // 6. Ejecución de la lógica (Inserción/Actualización)
            if ($exists) {
                log_message('info', "Registro con email {$email} ya existe.");
                // Puedes agregar aquí una lógica de actualización si lo requieres
            } else {
                $this->directoryModel->insert($clientData);
            }

            // 7. Almacenamos el resultado (incluyendo IDs) para la exportación a Excel
            // Esto es útil si quieres un Excel de salida con todos los campos MÁS los IDs encontrados
            $resultados_finales[] = $clientData;
        }

        fclose($handle);

        // Retorna el array con todos los datos procesados para generar el Excel de salida
        return $resultados_finales;
    }

}
