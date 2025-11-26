<?php

namespace App\Controllers;

class HomeController extends BaseController
{
    public function index()
    {   
        $data = [
            'title' => "Inicio",
            'titleMod' => ""
        ];
        return view('home', $data);
    }
}
