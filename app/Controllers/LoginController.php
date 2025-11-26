<?php

namespace App\Controllers;

use App\Models\UserModel;

class LoginController extends BaseController
{
    public function index(): string
    {
        return view('login');
    }

    public function login()
    {
        $session = session();
        $userModel = new UserModel();
        // Validar usuario
        $user = $this->request->getPost('user');
        $password = $this->request->getPost('password');

        $userFind = $userModel->getUserByUserName($user);
        if (!$userFind) {
            return redirect()->back()->with('error', 'Usuario no encontrado o inactivo.');
        }
        // Verificar contraseña
        if (!password_verify($password, trim($userFind['password']))) {
            return redirect()->back()->with('error', 'Contraseña incorrecta.');
        }
        
        // Guardar datos en sesión
        $session->set([
            'userId' => $userFind['user_id'],
            'name' => $userFind['name'],
            'lastName' => $userFind['lastname'],
            'email' => $userFind['email'],
            'user' => $userFind['username'],
            'phone' => $userFind['phone'],
            'menuItems' => $this->menuItems,
            'loggedIn' => true
        ]);

        return redirect()->to('/home'); // Puedes redirigir al dashboard
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/');
    }
}
