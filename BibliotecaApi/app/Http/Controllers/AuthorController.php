<?php

namespace App\Http\Controllers;

use App\Models\Author;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class AuthorController extends Controller
{


    public function index()
    {
        $authors = Author::orderBy('name', 'asc')->get();
        return $this->getResponse200($authors);
    }

    public function response()
    {
        return [
            "error" => true,
            "message" => "Wrong action!",
            "data" => []
        ];
    }


    public function store(Request $request)
    {

        $author = new Author();

        if($author){

            $author = new Author();
            $author->name = $request->name;
            $author->first_surname = $request->first_surname;
            $author->second_surname = $request->second_surname;

            $author->save();

           return $this->getResponse201("Author", "Create", $author);
        }else{

            return $this->getResponse500();


        }

    }



    public function update(Request $request, $id)
    {
        $author = Author::find($id);

        if($author){
            $author->name = $request->name;
            $author->first_surname = $request->first_surname;
            $author->second_surname = $request->second_surname;
            $author->update();

            return $this->getResponse201("Author", "Update", $author);
        }else{
            return $this->getResponse404();

        }


    }

    public function show($id){
        $author = Author::find($id);
        return $this->getResponse200($author);

    }



}
