<?php

namespace App\Controllers;

use App\Models\MenuModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    protected $request;
    protected $helpers = [];
    protected $session;
    protected $menuItems = []; // Inicializamos como array vacío

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $this->session = Services::session();

        // 1. VALIDAR SESIÓN PRIMERO
        // Si no hay sesión, checkSession detendrá la ejecución aquí mismo.
        //$this->checkSession();

        // 2. CARGAR MENÚ SOLO SI HAY SESIÓN
        $menuGrouped = [];
        if ($this->session->get('loggedIn')) {
            $this->getMenuUser();
            
            // Usamos ?? [] para garantizar que siempre pase un array a la función
            $menuGrouped = $this->groupMenu($this->menuItems ?? []);
        }

        // 3. HACER VARIABLES DISPONIBLES EN VISTAS
        $renderer = Services::renderer();
        $renderer->setVar('session', $this->session);
        $renderer->setVar('menuGrouped', $menuGrouped);
    }

    /**
     * Agrupa menú por parent, ahora acepta null o array y devuelve siempre array
     */
    protected function groupMenu(?array $menuItems)
    {
        $grouped = [];
        if (empty($menuItems)) {
            return $grouped;
        }

        foreach ($menuItems as $item) {
            // Verificamos que 'parent' exista para evitar otros errores
            $parentId = $item['parent'] ?? 0;
            $grouped[$parentId][] = $item;
        }
        return $grouped;
    }

    protected function checkSession()
    {
        $uri = service('uri');
        // Usamos segment(1) con un fallback por si la URI está vacía
        $segment = $uri->getSegment(1);
        $controller = strtolower($segment !== '' ? $segment : '');

        $rutasPublicas = ['login', 'auth']; 

        if (!in_array($controller, $rutasPublicas)) {
            if (!$this->session->get('loggedIn')) {
                // En BaseController, es más seguro usar header y exit para cortar el flujo
                header("Location: " . base_url('login'));
                exit(); 
            }
        }
    }
    
    protected function getMenuUser()
    {
        $username = $this->session->get('user');
        if ($username) {
            $menuModel = new MenuModel();
            $this->menuItems = $menuModel->getMenuByUser($username);
        } else {
            $this->menuItems = [];
        }
    }
}