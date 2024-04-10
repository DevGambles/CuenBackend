<?php

namespace App\Http\Controllers\General;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\CvComment;

class GeneralCommentController extends Controller {
    /*
     * Registrar un comentario de forma global
     */

    public function registerComment($infoComment) {

        $comment = new CvComment();

        $comment->description = $infoComment;

        if ($comment->save()) {
            return $comment->id;
        }
    }

}
