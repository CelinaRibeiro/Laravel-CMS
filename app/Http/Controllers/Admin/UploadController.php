<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function imageUpload(Request $request) {
        $request->validate([
            'file' => 'required|image|mimes:jpeg,jpg,png'
        ]);

        $ext = $request->file->extension(); //extensão da img 
        $imageName = time().'.'.$ext; //nome img

        //move o arquivo para a pasta correta
        $request->file->move(public_path('media/images'), $imageName);

        //retorna a url completa da img
        return [
            'location' => asset('media/images/'.$imageName)
        ];
    }
}
