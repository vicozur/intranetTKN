<?php
namespace Config;
use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
//$routes->get('/', 'Home::index');

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
	require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('codeigniter', 'Home::index');
// Paginas de autenicacion
$routes->get('/', 'LoginController::index');           // Muestra formulario de login
$routes->post('login', 'LoginController::login');      // Procesa login
$routes->post('logout', 'LoginController::logout');    // Cierra sesión
$routes->get('home', 'HomeController::index');         // Página después del login

// Paginas de configuracion rubro
$routes->get('rubro', 'Configuration\CategoryController::index');
$routes->post('rubro/getData', 'Configuration\CategoryController::getData');
$routes->post('rubro/save', 'Configuration\CategoryController::save');
$routes->post('rubro/toggleStatus/(:num)', 'Configuration\CategoryController::toggleStatus/$1');

// Paginas de configuracion country
$routes->get('pais', 'Configuration\CountryController::index');
$routes->post('pais/getData', 'Configuration\CountryController::getData');
$routes->post('pais/save', 'Configuration\CountryController::save');
$routes->post('pais/toggleStatus/(:num)', 'Configuration\CountryController::toggleStatus/$1');

// Paginas de configuracion city
$routes->get('ciudad', 'Configuration\CityController::index');
$routes->post('ciudad/getData', 'Configuration\CityController::getData');
$routes->post('ciudad/save', 'Configuration\CityController::save');
$routes->post('ciudad/toggleStatus/(:num)', 'Configuration\CityController::toggleStatus/$1');

// Paginas de configuracion user
$routes->get('user', 'Users\UserController::index');
$routes->post('user/getData', 'Users\UserController::getData');
$routes->post('user/save', 'Users\UserController::save');
$routes->post('user/toggleStatus/(:num)', 'Users\UserController::toggleStatus/$1');

// Paginas de configuracion library
$routes->get('proyecto', 'Administration\LibraryController::index');
$routes->post('proyecto/getData', 'Administration\LibraryController::getData');
$routes->post('proyecto/save', 'Administration\LibraryController::save');
$routes->post('proyecto/file', 'Administration\LibraryController::uploadFile');
$routes->post('proyecto/toggleStatus/(:num)', 'Administration\LibraryController::toggleStatus/$1');

// Paginas de configuracion directorio
$routes->get('directorio', 'Administration\DirectoryController::index');
$routes->post('directorio/getData', 'Administration\DirectoryController::getData');
$routes->post('directorio/importar', 'Administration\DirectoryController::importar');

// Paginas de configuracion directorio Form
$routes->get('directorio/clienteForm/(:num)', 'Administration\DirectoryFormController::index/$1');
$routes->get('directorio/clienteForm', 'Administration\DirectoryFormController::index');
$routes->post('directorio/clienteForm/create', 'Administration\DirectoryFormController::create');
$routes->post('directorio/clienteForm/update/(:num)', 'Administration\DirectoryFormController::update/$1');

$routes->get('directorio/getcityList/(:num)', 'Administration\DirectoryFormController::getcityList/$1');


//http://localhost/intranetTKN/public/directorio/getcityList/1

$routes->post('directorio/save', 'Administration\DirectoryController::save');
$routes->post('directorio/file', 'Administration\DirectoryController::uploadFile');
$routes->post('directorio/toggleStatus/(:num)', 'Administration\DirectoryController::toggleStatus/$1');