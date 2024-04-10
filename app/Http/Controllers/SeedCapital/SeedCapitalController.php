<?php

namespace App\Http\Controllers\SeedCapital;

use App\CvComment;
use App\CvCommentBySeedCapital;
use App\Http\Controllers\Controller;
use App\Http\Controllers\General\GeneralCommentController;
use App\SeedCapital;
use DB;
use http\Env\Response;
use Illuminate\Http\Request;

class SeedCapitalController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $totalUsd = 0;
        $totalCo = 0;

        $model = SeedCapital::all();

        foreach ($model as $item) {
            $item->associated;
            $totalUsd += $item->valueUsd;
            $totalCo += $item->valueCo;

            //---Comentarios ---//
            if (!empty($item->commentOne)) {
                $modelCvComment = CvComment::find($item->commentOne->comment_id);
                if (isset($modelCvComment)) {
                    $item["comment"] = CvComment::find($item->commentOne->comment_id)->description;
                }
            }
            unset($item->commentOne);
        }

        foreach ($model as $item) {
            $item['percent'] = ($item->valueUsd * 100) / $totalUsd;
        }

        return response()->json(
            [
                'data' => $model,
                'totalUsd' => $totalUsd,
                'totalCo' => $totalCo,
            ], 200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //--- Transaccion ---//
        DB::beginTransaction();

        $campos = $request->all();
        $model = new SeedCapital();

        $model->codeCenter = $campos['codeCenter'];
        $model->nit = $campos['nit'];
        $model->valueUsd = $campos['valueUsd'];
        $model->valueUsdOrigin = $campos['valueUsd'];
        $model->valueCo = $campos['valueCo'];
        $model->valueCoOrigin = $campos['valueCo'];
        $model->cv_associateds_id = $campos['associated']['id'];

        //--- Registrar comentario ---//
        $commentController = new GeneralCommentController();

        // $comment = "";
        // if($campos['comment'])
        //     $comment = $campos['comment'];

        // $idComment = $commentController->registerComment($comment);

        if ($model->save()) {

            //--- Registrar pivote de comentario ---//
            $commentBySeedCapitalModel = new CvCommentBySeedCapital();
            // $commentBySeedCapitalModel->comment_id = $idComment;
            $commentBySeedCapitalModel->seed_capital_id = $model->id;

            if ($commentBySeedCapitalModel->save()) {

                $model["comment"] = CvComment::find($model->commentOne->comment_id);
                unset($model->commentOne);
                DB::commit();
                return $model;
            } else {
                DB::rollback();
            }
        } else {
            return response()->json(['message' => 'Ha ocurrido un error creando el registro.', 'code' => 500], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\SeedCapital  $seedCapital
     * @return \Illuminate\Http\Response
     */
    public function show(SeedCapital $seedCapital)
    {
        return abort(404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\SeedCapital  $seedCapital
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, SeedCapital $seedCapital)
    {

        //--- Transaccion ---//
        DB::beginTransaction();

        $campos = $request->all();

        $seedCapital->codeCenter = $campos['codeCenter'];
        $seedCapital->nit = $campos['nit'];
        $seedCapital->valueUsd = $campos['valueUsd'];
        $seedCapital->valueCo = $campos['valueCo'];
        $seedCapital->cv_associateds_id = $campos['associated']['id'];

        if ($seedCapital->update()) {
            $seedCapital->associated;

            //--- Actualizar comentario ---//

            $save = false;

            if ($seedCapital->commentOne == null) {

                //--- Registrar comentario ---//
                $commentController = new GeneralCommentController();
                $idComment = $commentController->registerComment($campos['comment']);
                //--- Registrar pivote de comentario ---//
                $comment = new CvCommentBySeedCapital();
                $comment->comment_id = $idComment;
                $comment->seed_capital_id = $seedCapital->id;

                if ($comment->save()) {
                    $save = true;
                    $seedCapital["comment"] = CvComment::find($idComment);
                }

            } else {

                $comment = CvComment::find($seedCapital->commentOne->comment_id);
                $comment->description = $campos["comment"];

                if ($comment->save()) {
                    $save = true;
                    $seedCapital["comment"] = CvComment::find($seedCapital->commentOne->comment_id);
                }
            }

            if ($save == true) {

                unset($seedCapital->commentOne);
                DB::commit();
                return $seedCapital;
            } else {
                DB::rollback();
            }

        } else {
            return response()->json(['message' => 'Error al actualizar el registro', 'code' => 500], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\SeedCapital  $seedCapital
     * @return \Illuminate\Http\Response
     */
    public function destroy(SeedCapital $seedCapital)
    {
        return abort(404);
    }

}
