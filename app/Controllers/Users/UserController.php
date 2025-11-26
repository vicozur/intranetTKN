<?php

namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Models\AssignModel;
use App\Models\UserModel;

class UserController extends BaseController
{
    protected $userModel, $assignModel, $profileModel;
    protected $session;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->assignModel = new AssignModel();
        $this->session = session();
    }

    public function index()
    {
        $data = [
            'title' => "Usuarios",
            'titleMod' => "Administración de Usuarios",
            'profiles' => $this->userModel->getProfiles()
        ];

        return view('users/user', $data);
    }

    public function getData()
    {
        $request = $this->request;
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'];
        $orderColumn = $request->getPost('order')[0]['column'];
        $orderDir = $request->getPost('order')[0]['dir'];

        $data = $this->userModel->getDatatables($start, $length, $searchValue, $orderColumn, $orderDir);
        $total = $this->userModel->countAllUsers();
        $filtered = $this->userModel->countFilteredUsers($searchValue);

        return $this->response->setJSON([
            'draw' => intval($request->getPost('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $data
        ]);
    }

    public function save()
    {
        $userId = $this->request->getPost('user_id');
        $assignId = $this->request->getPost('assign_id');
        $username = trim($this->request->getPost('username'));
        $name = trim($this->request->getPost('name'));
        $lastName = trim($this->request->getPost('lastname'));
        $email = trim($this->request->getPost('email'));
        $phone = trim($this->request->getPost('phone'));
        $profileId = trim($this->request->getPost('profileId'));

        $data = [
            'username' => $username,
            'name' => $name,
            'lastname' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'created_user' => $this->session->get('user') ?? 'system'
        ];

        if (empty($userId)) {
            $data['password'] = password_hash($username, PASSWORD_DEFAULT);
            $insertId = $this->userModel->insert($data);
            if ($insertId) {
                // asignar perfil
                $this->assignModel->insert([
                    'user_user_id' => $insertId,
                    'profile_profile_id' => $profileId,
                    'created_user' => $data['created_user']
                ]);
            }
            return $this->response->setJSON(['status' => 'success', 'message' => 'Usuario registrado correctamente.']);
        } else {
            $this->userModel->update($userId, $data);
            $this->assignModel->update($assignId, ['profile_profile_id' => $profileId]);
            return $this->response->setJSON(['status' => 'success', 'message' => 'Usuario actualizado correctamente.']);
        }
    }

    public function toggleStatus($id)
    {
        $data = $this->request->getJSON(true);

        if (!$data || !isset($data['status'])) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Datos inválidos recibidos.'
            ]);
        }

        $newStatus = $data['status']; // true o false
        $this->userModel->update($id, ['status' => $newStatus]);
        return $this->response->setJSON([
            'status' => 'success',
            'message' => $newStatus ? 'Usuario activado' : 'Usuario desactivado'
        ]);
    }
}
