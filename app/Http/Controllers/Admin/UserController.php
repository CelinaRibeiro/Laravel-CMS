<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('can:edit-users');
    }

    //lista todos os usuários
    public function index()
    {
        $users = User::paginate(10); //paginação

        //usuário logado 
        $loggedId = intval(Auth::id());
        
        return view('admin.users.index', [
            'users' => $users,
            'loggedId' => $loggedId
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //cadastra usuários
    public function store(Request $request)
    {
        //pega os dados
        $data = $request->only([
            'name',
            'email',
            'password',
            'password_confirmation'
        ]);

        //cria o validador e execulta
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:200', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed']
        ]);

        if($validator->fails()) {
            return redirect()->route('users.create')
            ->withErrors($validator)
            ->withInput();
        }

        //cria o usuário
        $user = new User;
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();

        return redirect()->route('users.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //mostra um item específico
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //inicia o processo de editação do usuário apenas buscando o user por id, se encontrar entra na pg de edição
    public function edit($id)
    {
        //pegar user por id 
        $user = User::find($id);

        if($user) {
            return view('admin.users.edit', [
                'user' => $user
            ]);
        }
        return redirect()->route('admin.users.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //realiza o processo de edição
    public function update(Request $request, $id)
    {
        //busca usrer por id
        $user = User::find($id);

        //pega os dados 
        if($user) {
            $data = $request->only([
                'name',
                'email',
                'password',
                'password_confirmation'
            ]);

            //criar o validador 
            $validator = Validator::make([
                'name' => $data['name'],
                'email' => $data['email']
            ], [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'string', 'email', 'max:100']
            ]);

            // 1. Alteração do nome
            $user->name = $data['name'];

            // 2. Alteração do email
            // 2.1 Primeiro, verificamos se o email foi alterado
            if($user->email != $data['email']) {
                // 2.2 Verificamos se o novo email já existe
                $hasEmail = User::where('email', $data['email'])->get();
                // 2.3 Se não existir, alteramos
                if(count($hasEmail) === 0) {
                    $user->email = $data['email'];
                } else {
                    $validator->errors()->add('email', __('validation.unique', [
                        'attribute' => 'email'
                    ]));
                }
            }
           
            // 3. Alteração da senha 
            // 3.1 Verifica se o usuário digitou a senha 
            if(!empty($data['password'])) {
                if(strlen($data['password']) >= 4) {
                    //3.2 Verifica se a confirmação está ok
                    if($data['password'] === $data['password_confirmation']) {
                    // 3.2 Altera a senha 
                    $user->password = Hash::make($data['password']); 
                    } else {
                        $validator->errors()->add('password', __('validation.confirmed', [
                            'attribute' => 'password'
                        ]));
                    }  
                } else {
                    $validator->errors()->add('password', __('validation.min.string', [
                        'attribute' => 'password', 
                        'min' => 4
                    ]));
                }
            }

            if(count( $validator->errors() ) > 0) {
                return redirect()->route('users.edit', [
                    'user' => $id
                ])->withErrors($validator);
            }

            $user->save();
        }
        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //deleta usuário
    public function destroy($id)
    {
        //verificar o user logado para nao deletá-lo
        $loggedId = intval(Auth::id());

        //se nao logado deleta
        if($loggedId !== intval($id)) {
            //busca usrer por id
            $user = User::find($id);
            $user->delete();
        } 
        return redirect()->route('users.index');
    }
}
