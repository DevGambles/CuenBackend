<?php

namespace App\Http\Controllers\Associated;

use App\CvAssociated;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Arr;

class AssociatedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return CvAssociated::orderBy('id', 'desc')->get();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $campos = $request->all();
        unset($campos['id']);
        return CvAssociated::create($campos);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\CvAssociated  $cvAssociated
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $model = CvAssociated::findOrFail($id);
        return $model;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\CvAssociated  $cvAssociated
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $model = CvAssociated::findOrFail($id);
        if($model->update($request->all())) {
            return response()->json(['message' => 'Registro actualizado', 'code' => 200], 200);
        } else{
            return response()->json(['message' => 'Ha ocurrido un error al realizar la actualización', 'code' => 500], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\CvAssociated  $cvAssociated
     * @return \Illuminate\Http\Response
     */
    public function destroy(CvAssociated $cvAssociated)
    {
        //
    }

    public function getAssociatedByType(Request $request){
        $arrResponse = Array();

        $campos = $request->all();
        $model = CvAssociated::all();

        if(array_key_exists('type', $campos)){
            $model = $model->where('type', $campos['type']);
        }

        foreach ($model as $item) {
            array_push( $arrResponse, $item->toArray());
        }
        return $arrResponse;
    }
}
