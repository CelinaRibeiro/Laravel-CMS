<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Setting;

class SettingController extends Controller
{

    //permiste acesso apenas para user logado
    public function __construct() {
        $this->middleware('auth');
    }

    //lista as configurações
    public function index() {
        $settings = [];

        $dbsettings = Setting::get();

        foreach($dbsettings as $dbsetting) {
            $settings[ $dbsetting['name'] ] = $dbsetting['content'];
        }

        return view('admin.settings.index', [
            'settings' => $settings
        ]);
    }

    public function save(Request $request) {
        //pega os dados 
        $data = $request->only([
            'title', 'subtitle', 'email', 'bgcolor', 'textcolor'
        ]);

        //usa o validador
        $validator = $this->validator($data);

        if($validator->fails()) {
            return redirect()->route('settings')
                ->withErrors($validator);
        }

        foreach($data as $item => $value) {
            Setting::where('name', $item)->update([
                'content' => $value
            ]);
        }
        return redirect()->route('settings')
            ->with('warning', 'Informações alteradas com sucesso!');
    }

    //cria o validador
    protected function validator($data) {
        return Validator::make($data, [
            'title' => ['string', 'max:100'],
            'subtitle' => ['string', 'max:100'],
            'email' => ['string', 'email'],
            'bgcolor' => ['string', 'regex:/#[A-Z0-9]{6}/i'],
            'textcolor' => ['string', 'regex:/#[A-Z0-9]{6}/i']
        ]);
    }
}

