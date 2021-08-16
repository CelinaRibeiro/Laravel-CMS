<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Visitor;
use App\User;
use App\Page;

class HomeController extends Controller
{
    public function __construct() {
       $this->middleware('auth'); //permite acesso apenas user p/ autenticado no sistema
    }

    public function index(Request $request) {
        $visitsCount = 0;
        $onlineCount = 0;
        $pageCount = 0;
        $userCount = 0;
        $interval = intval($request->input('interval', 30));
        if($interval > 120) {
            $interval = 120;
        }

       //contagem de visitantes
       $dateInterval = date('Y-m-d H:i:s', strtotime('-'.$interval.' days'));
       $visitsCount = Visitor::where('date_access', '>=', $dateInterval)->count();

       //contagem de usuários online
       $dateLimit = date('Y-m-d H:i:s', strtotime('-5 minutes'));
       $onlineList = Visitor::select('ip')->where('date_access', '>=', $dateLimit)->groupBy('ip')->get();
       $onlineCount = count($onlineList);

       //contagem de páginas
        $pageCount = Page::count();

       //contagem de usuários
       $userCount = User::count();

       //contagem para o PagePie
       $pagePie = [];
       $visitsAll = Visitor::selectRaw('page, count(page) as c')
            ->where('date_access', '>=', $dateInterval)
            ->groupBy('page')
            ->get();
       foreach($visitsAll as $visit) {
           $pagePie[ $visit['page'] ] = intval($visit['c']);
       }
       
       $pageLabels = json_encode( array_keys($pagePie) );
       $pageValues = json_encode( array_values($pagePie) );

       return view('admin.home', [
           'visitsCount' => $visitsCount,
           'onlineCount' => $onlineCount,
           'pageCount' => $pageCount,
           'userCount' => $userCount,
           'pageLabels' => $pageLabels,
           'pageValues' => $pageValues,
           'dateInterval' => $interval

       ]);
    }
}
