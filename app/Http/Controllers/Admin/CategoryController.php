<?php

namespace App\Http\Controllers\Admin;

use App\CvCategory;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    public function createCategory(Request $data)
    {
        $category=new CvCategory();
        $category->name=$data->name;
        $category->save();
        return [
            "message"=>'Registro de categoria',
            "code"=>200
        ];
    }


    public function allCategory()
    {
        return CvCategory::all();
    }

    public function detailCategory($id_category)
    {
        return  CvCategory::find($id_category);
    }

    public function updateCategory(Request $data)
    {
        $category=CvCategory::find($data->category_id);
        $category->name=$data->name;
        $category->save();
        return [
            "message"=>'Registro de categoria',
            "code"=>200
        ];
    }
}
