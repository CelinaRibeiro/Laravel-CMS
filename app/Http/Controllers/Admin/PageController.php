<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Page;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->middleware('auth'); //somente user autenticado
    }

     //lista todas as pages
    public function index()
    {
        $pages = Page::paginate(10); //paginação

        return view('admin.pages.index', [
            'pages' => $pages
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    //direcino para a criação da page
    public function create()
    {
        return view('admin.pages.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    //cadastra página
    public function store(Request $request)
    {
        //pega os campos
        $data = $request->only([
            'title',
            'body'  
        ]);

        //cria o slug 
        $data['slug'] = Str::slug($data['title'], '-');

        //cria o validador e execulta
        $validator = Validator::make($data, [
            'title' => ['required', 'string', 'max:100'],
            'body' => ['string'],
            'slug' => ['required', 'string', 'max:100', 'unique:pages']
        ]);

        if($validator->fails()) {
            return redirect()->route('pages.create')
                ->withErrors($validator)
                ->withInput();
        }

        //cria a page 
        $page = new Page;
        $page->title = $data['title'];
        $page->slug = $data['slug'];
        $page->body = $data['body'];
        $page->save();

        return redirect()->route('pages.index');
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
        $page = Page::find($id);

        if($page) {
            return view('admin.pages.edit', compact(''));
        }

       return redirect()->route('pages.index');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    //atualiza o edit
    public function update(Request $request, $id)
    {
        //busca por id
        $page = Page::find($id);

        //pega os dados 
        if($page) {
            $data = $request->only([
                'title',
                'body'
            ]);

            if($page['title'] !== $data['title']) {
                $data['slug'] = Str::slug($data['title'], '-');

                //se criou o titulo cria o validador com slug
                $validator = Validator::make($data, [
                    'title' => ['required', 'string', 'max:100'],
                    'body' => ['string'],
                    'slug' => ['required', 'string', 'max:100', 'unique:pages']
                ]);
            } else {
                //crio o validador s/ slug
                $validator = Validator::make($data, [
                    'title' => ['required', 'string', 'max:100'],
                    'body' => ['string']
                ]);
            }

            if($validator->fails()) {
                return redirect()->route('pages.edit', [
                    'page' => $id
                ])
                    ->withErrors($validator)
                    ->withInput();
            }

            $page->title = $data['title'];
            $page->body = $data['body'];

            //se existe o slug e não estiver vazio ele pega
            if(!empty($data['slug'])) {
                $page->slug = $data['slug'];
            }
            
            $page->save();
        }
        return redirect()->route('pages.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $page = Page::find($id);
        $page->delete();

        return redirect()->route('pages.index');
    }
}
