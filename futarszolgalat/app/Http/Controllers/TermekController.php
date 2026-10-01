<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Termek;

class TermekController extends Controller
{
    
 public function get()
    {
        $termekek = Termek::all();
        return view('termekek.index', ['termekek' => $termekek]);
    }

 public function show(int $id)
 {
    $termek = Termek::findOrFail($id);
    return view('termekek.show', ['termek' => $termek]);
 }

 public function post(Request $request){
    $termek = new Termek();
    $termek->nev = $request->nev;
    $termek->ar = $request->ar;
    $termek->save();
    return redirect()->route('termekek.index');
 }

 public function put(Request $request, int $id){
    $termek = Termek::find($id);
    $termek->nev = $request->nev;
    $termek->ar = $request->ar;
    $termek->save();
    return redirect()->route('termekek.index');
 }
 
 public function delete(int $id){
    $termek = Termek::find($id);
    $termek->delete();
    return redirect()->route('termekek.index');
 }

}
