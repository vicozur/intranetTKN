<?php namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services; // <--- ¡Esta línea es la clave!

class Cors implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Las peticiones OPTIONS (preflight) se responden inmediatamente
        if ($request->getMethod() === 'options')
        {
            $response = Services::response();
            $response->setHeader('Access-Control-Allow-Origin', '*'); // O el dominio específico
            $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
            $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
            return $response;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Aplica las cabeceras CORS a la respuesta final
        $response->setHeader('Access-Control-Allow-Origin', '*'); // Permite cualquier origen
        $response->setHeader('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept, Authorization');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
        $response->setHeader('Access-Control-Allow-Credentials', 'true'); // Si usas cookies o sesiones
        return $response; // <--- ESTO ES CRUCIAL
    }
}