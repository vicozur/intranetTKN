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

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */

    protected $session;

    protected $menuItems;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Inicia la sesión
        $this->session = Services::session();

        // Obtiene el menú del usuario
        ; // llena $this->menuItems
        print_r($this->getMenuUser()); 
        // Agrupa menú por parent
        $menuGrouped = $this->groupMenu($this->menuItems);

        // Hace variables disponibles en todas las vistas
        $renderer = Services::renderer();
        $renderer->setVar('session', $this->session);
        $renderer->setVar('menuGrouped', $menuGrouped);

        // Validar sesión
        $this->checkSession();
    }

    /**
     * Función para agrupar el menú por parent
     */
    protected function groupMenu(array $menuItems)
    {
        $grouped = [];
        foreach ($menuItems as $item) {
            $grouped[$item['parent']][] = $item;
        }
        return $grouped;
    }

    protected function checkSession()
    {
        // Solo protege rutas que no sean login, register, etc.
        $uri = service('uri');
        $controller = strtolower($uri->getSegment(1)); // primer segmento de la URL

        $rutasPublicas = ['login', 'auth']; // controladores que no necesitan sesión

        if (!in_array($controller, $rutasPublicas) && !$this->session->get('loggedIn')) {
            // No está logueado → redirige al login
            return redirect()->to('/login')->send();
            exit;
        }
    }
    
    protected function getMenuUser()
    {
        // Preload any models, libraries, etc, here.
        $username = session()->get('user');
        $menuModel = new MenuModel();
        $this->menuItems = $menuModel->getMenuByUser($username);
    }


}
