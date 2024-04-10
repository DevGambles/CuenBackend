<?php

namespace App\Http\Controllers\General;

use App\CvComment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManagerStatic as Image;
use App\CvFile;
use App\CvProject;
use App\CvTask;
use Carbon\Carbon;
use App\CvProcess;
use App\CvTypeFile;
use App\CvUsersFile;
use App\User;
use App\CvAttachmentFiles;
use App\CvCommentByFiles;
use App\CvTaskByFile;
use App\Http\Controllers\General\GeneralCommentController;
use App\CvImgBase64Task;

class GeneralFileController extends Controller {

    //*** Guardar archivos de tareas ***//
    public function saveFiles(Request $request) {

        //--- Saber si todos los archivos fueron guardados ---//
        $files = array();

        //--- Obtener los archivos ---//
        $arrayFiles = $request->file('files');

        if (is_array($arrayFiles) && !empty($arrayFiles)) {

            foreach ($arrayFiles as $file) {

                //--- Guardar los archivos localmente ---//
                $nameFile = $this->saveFilesLocal($file);

                //--- Guardar archivos ---//
                $saveFile = new CvFile();
                $saveFile->name = $nameFile;

                //--- Validar tipo de archivo entrante ---//
                if (isset($request->task_id) && $request->subType_id) {
                    $taskByFile = new CvTaskByFile();

                    $taskByFile->task_id = $request->task_id;
                    $taskByFile->task_sub_type_id = $request->subType_id;
                }

                // --- Filtrar si es un archivo especifico --- //
                $taskByFile->task_type_file_id = $this->filtesTypeFileSpecific($request->type_file);

                if ($saveFile->save()) {

                    $taskByFile->file_id = $saveFile->id;
                    $taskByFile->save();

                    if (isset($request->user_id) && !empty($request->user_id)) {
                        //--- carga de archivo por tipo de usuario Contratista ---//
                        $pivotFile = new CvUsersFile();
                        $pivotFile->user_id = $request->user_id;
                        $pivotFile->cv_file_id = $saveFile->id;
                        $pivotFile->save();
                    }
                } else {

                    array_push($files, array(
                        "file" => $file->getClientOriginalName()
                    ));
                }
            }

            //--- Verificar si todos los archivos fueron guardados ---//

            if (!empty($files)) {
                return $files;
            }

            return [
                "message" => "Registro exitoso",
                "response_code" => 200
            ];
        } else {

            return [
                "message" => "No se ha enviado informacion",
                "response_code" => 200
            ];
        }
    }

    //*** Consultar todas las imagenes de un proyecto ***//

    public function consultImagesProject($id) {

        $tasksByProject = CvProject::find($id)->task;

        //--- Guardar todos los archivos de cada tarea vinculada a cada proyecto ---//
        $files = array();

        foreach ($tasksByProject as $tasks) {

            array_push($files, CvTask::find($tasks->id)->taskFile);
        }

        return $files;
    }

    //*** Consultar todos los archivos por tarea ***//

    public function consultFilesTask($id, $type) {

        $task = CvTask::find($id);

        if (empty($task)) {

            return [
                "message" => "El tarea no existe en el sistema",
                "response_code" => 200
            ];
        }

        //--- Consulta de las tareas de un procedimiento ---//
        $process = CvProcess::find($task->process[0]->id);

        if (!empty($process)) {

            $taskByProcess = $process->processByTasks;

            //--- Consultar los archivos de cada tarea ---//
            $infoFiles = array();

            foreach ($taskByProcess as $files) {

                $tasksByFiles = CvTask::find($files->id)->taskFile;

                foreach ($tasksByFiles as $value) {
                    $value->task_sub_type_id = $value->taskDetall->task_sub_type_id;
                    $value->task_id = $value->taskDetall->task_id;
                }

                array_push($infoFiles, $tasksByFiles);
            }

            return $this->consultFilterFiles($infoFiles, $type);
        }

        return [
            "message" => "El procedimiento no existe en el sistema",
            "response_code" => 200
        ];
    }

    //*** Consultar todos los archivos por usuario ***//

    public function consultFilesUser($id) {

        $date = Carbon::now();

        $info = array();
        $infoImages = array();
        $infoDocuments = array();

        $user = User::find($id);

        if (empty($user)) {

            return [
                "message" => "El usuario no existe en el sistema",
                "response_code" => 200
            ];
        }

        if (!empty($user->files)) {

            foreach ($user->files as $file) {

                if (strtolower(File::extension($file->name)) == "png" || strtolower(File::extension($file->name)) == "jpg" || strtolower(File::extension($file->name)) == "jpeg") {
                    array_push($infoImages, array(
                        "id" => $file->id,
                        "name" => $file->name,
                        "created_at" => $date->format($file->created_at)
                    ));
                } else {
                    array_push($infoDocuments, array(
                        "id" => $file->id,
                        "name" => $file->name,
                        "created_at" => $date->format($file->created_at)
                    ));
                }
            }


            array_push($info, array("images" => $infoImages, "documents" => $infoDocuments));

            return $info[0];
        }
    }

    //*** Guardar imagenes ***//
    public function storageImage($nameFile, $file) {

        //--- Original ---//
        Storage::disk('local')->put('images/' . $nameFile, File::get($file));

        //--- Thumbnails -> Reducir el tamaño de las imagenes ---//
        $imgThumbnails = Image::make($file)->resize(300, 300)->stream();
        Storage::disk('local')->put('images/img-thumbnails/' . $nameFile, $imgThumbnails);
    }

    //*** Guardar en storage ***//
    public function diskstorageImage($nameFile, $file) {

        //--- Original ---//
        Storage::disk('storage')->put('images/' . $nameFile, File::get($file));

        //--- Thumbnails -> Reducir el tamaño de las imagenes ---//
        $imgThumbnails = Image::make($file)->resize(300, 300)->stream();
        Storage::disk('storage')->put('images/img-thumbnails/' . $nameFile, $imgThumbnails);
    }

    //*** Codigo para diferenciar el nombre de los archivos ***//

    public function codeRandomFiles() {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 10);
    }

    //*** Eliminar archivos  ***//

    public function deleteFiles(Request $request) {
        
        //--- Consultar archivo a eliminar de la bd ---//
        $file = CvFile::find($request->id_file);

        $type = $request->type_file;
        $locationFile = "";
        $destinationFile = "";
        $deleteFile = false;

        if ($file->state_delete == false) {

            switch ($type) {

                case "doc":
                    $locationFile = "documents";
                    $destinationFile = "documentsDelete";
                    break;

                case "img":
                    $locationFile = "images";
                    $destinationFile = "imagesDelete";
                    //--- Eliminar imagenes de miniaturas ---//
                    $deleteFile = File::delete(public_path('files/' . $locationFile . '/img-thumbnails/' . $file->name));
                    break;

                default :
                    return [
                        "message" => "Tipo de archivo no es el admitido",
                        "response_code" => 200
                    ];
            }

            //--- Mover archivos a carpetas de eliminacion ---//
            if (Storage::disk('local')->exists($locationFile . '/' . $file->name) == true) {
                Storage::disk('local')->move($locationFile . '/' . $file->name, $destinationFile . '/' . $file->name);
            }

            $file->state_delete = true;

            if ($file->save() || $deleteFile == true) {
                return [
                    "message" => "Eliminacion exitosa",
                    "code" => 200,
                    "name_file" => $file->name
                ];
            }
        } else {
            return [
                "message" => "El archivo ya ha sido eliminado",
                "code" => 500
            ];
        }
    }

    // *** Mensajes de acuerdo al sub tipo del archivo *** //

    public function nameSubTypeFile($id, $name) {

        if ($name != "") {
            return $name;
        }

        $message = "";

        switch ($id) {

            case 5:
                $message = "Ficha predial";

                break;

            case 2:
                $message = "Cedula del propietario";

                break;

            case 8:
                $message = "Certificado de tradición";

                break;

            case 15:
                $message = "Mapa de verificación y seguimiento";

                break;

            case 27:
                $message = "Concepto de coordinación presupuesto";

                break;

            case 28:
                $message = "Concepto de jurídico presupuesto";

                break;

            case 29:
                $message = "Edicion SIG presupuesto, buenas prácticas";

                break;

            case 16:
                $message = "Minuta firmada por dirección";

                break;

            case 32:
                $message = "Firma de minuta por el propietario";

                break;

            default :

                $message = "Opcion no valida";
                break;
        }

        return $message;
    }

    // --- Consulta general de los archivos --- //

    public function consultFilterFiles($files, $type) {

        $info = array();
        $infoImages = array();
        $infoDocuments = array();

        $date = Carbon::now();

        // --- Filtrar los sub tipo de archivos --- //
        $filterSubTypes = $this->listSubTypeFiles($type);

        if ($filterSubTypes != "") {

            foreach ($files as $file) {

                foreach ($filterSubTypes as $subType) {

                    if (count($file) > 0) {

                        foreach ($file as $dataFile) {

                            //--- Mostrar archivos que no han sido eliminados ---//

                            if ($dataFile->state_delete == false) {

                                $name = "";

                                if (isset($dataFile->task_type_file_id)) {
                                    $name = CvTypeFile::find($dataFile->task_type_file_id)->name;
                                }

                                if ($dataFile->task_sub_type_id == $subType) {

                                    if (strtolower(File::extension($dataFile->name)) == "png" || strtolower(File::extension($dataFile->name)) == "jpg" || strtolower(File::extension($dataFile->name)) == "jpeg") {
                                        array_push($infoImages, array(
                                            "id" => $dataFile->id,
                                            "name" => $dataFile->name,
                                            "sub_type" => $this->nameSubTypeFile($dataFile->task_sub_type_id, $name),
                                            "id_sub_type" => $dataFile->task_sub_type_id,
                                            "created_at" => $date->format($dataFile->created_at)
                                        ));
                                    } else {
                                        array_push($infoDocuments, array(
                                            "id" => $dataFile->id,
                                            "name" => $dataFile->name,
                                            "sub_type" => $this->nameSubTypeFile($dataFile->task_sub_type_id, $name),
                                            "id_sub_type" => $dataFile->task_sub_type_id,
                                            "created_at" => $date->format($dataFile->created_at)
                                        ));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            //Los dcumentos llegan en array con key=numero
            $documenarray = array_map('unserialize', array_unique(array_map('serialize', $infoDocuments)));
            $all_documents = array();
            //se recosntruye en array
            foreach ($documenarray as $array) {
                array_push($all_documents, array(
                    'id' => $array['id'],
                    'name' => $array['name'],
                    'sub_type' => $array['sub_type'],
                    'id_sub_type' => $array['id_sub_type'],
                    'created_at' => $array['created_at'],
                ));
            }

            // array_push($info, array("images" => $infoImages, "documents" => array_map('unserialize', array_unique(array_map('serialize', $infoDocuments)))));
            array_push($info, array("images" => $infoImages, "documents" => $all_documents));

            return $info[0];
        }
    }

    // *** Filtrar por archivos especificos para seleccionar su sup tipo *** //

    public function filtesTypeFileSpecific($data) {

        $info = 0;

        switch ($data) {
            case "OFAC":

                $info = 1;

                break;
            case "contractor":

                $info = 2;

                break;

            default:
                return null;
        }

        return $info;
    }

    //*** Filtrar por listas de sub tipos de archivos ***//

    public function listSubTypeFiles($type) {

        $filterSubTypes = array();

        switch ($type) {

            case 5:

                array_push($filterSubTypes, $type);

                break;

            case 2:

                array_push($filterSubTypes, $type, "5");

                break;

            case 6:

                array_push($filterSubTypes, $type, "2", "8", "5");

                break;

            case 8:

                array_push($filterSubTypes, $type);

                break;

            case 9:

                array_push($filterSubTypes, $type, "5", "4", "8");

                break;

            case 10:

                array_push($filterSubTypes, $type, "5", "4", "8");

                break;

            case 11:

                array_push($filterSubTypes, $type, "2", "8", "5", "6");

                break;

            case 13:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11");

                break;

            case 15:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13");

                break;

            case 24:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15");

                break;

            case 25:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24");

                break;

            case 26:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25");

                break;

            case 27:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26");

                break;

            case 28:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27");

                break;

            case 29:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28");

                break;

            case 14:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29");

                break;

            case 22:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14");

                break;

            case 20:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22");

                break;

            case 16:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20");

                break;

            case 21:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20", "16");

                break;

            case 32:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20", "16", "21");

            case 33:

                array_push($filterSubTypes, $type, "2", "8", "5", "6", "11", "13", "15", "24", "25", "26", "27", "28", "29", "14", "22", "20", "16", "21", "32");

                break;

            default:
                return [
                    "message" => "No existe la opcion de sub tipo en los archivos",
                    "response_code" => 200
                ];
        }

        return $filterSubTypes;
    }

    //*** Filtrar por los tipos de archivos ***//

    public function listTypesFiles($type) {

        $filterSubTypes = array();

        switch ($type) {

            case 2:

                array_push($filterSubTypes, $type);

                break;


            default:
                return [
                    "message" => "No existe la opcion de sub tipo en los archivos",
                    "response_code" => 200
                ];
        }
    }

    //*** Guardar anexos de un archivo ***//

    public function saveAttachment(Request $request) {

        //--- Guardar los anexos de los archivos ---//
        $idsAttachments = array();

        //--- Validar que el archivo exista en el sistema ---//
        $file = CvFile::find($request->file_id);

        if (empty($file)) {
            return [
                "message" => "El archivo no existe en el sistema",
                "code" => 500
            ];
        }

        //--- Obtener los archivos ---//
        if (!empty($request->file('files'))) {

            $arrayFiles = $request->file('files');

            if (is_array($arrayFiles) && !empty($arrayFiles)) {

                foreach ($arrayFiles as $file) {

                    //--- Guardar los archivos localmente ---//
                    $nameFile = $this->saveFilesLocal($file);

                    //--- Guardar archivos en la base de datos ---//
                    $saveFileAttachment = new CvAttachmentFiles();
                    $saveFileAttachment->name = $nameFile;
                    $saveFileAttachment->file_id = $request->file_id;
                    $saveFileAttachment->save();

                    array_push($idsAttachments, $saveFileAttachment->id);
                }
            }

            //--- Registrar comentario ---///
            $commentController = new GeneralCommentController();

            if (!empty($request->comment)) {

                $idComment = $commentController->registerComment($request->comment);

                //--- Relacionar el comentario con el archivo ---//

                foreach ($idsAttachments as $idAttachment) {

                    $commentByFile = new CvCommentByFiles();

                    $commentByFile->file_id = $request->file_id;
                    $commentByFile->comment_id = $idComment;
                    $commentByFile->attachment_id = $idAttachment;
                    $commentByFile->save();
                }

                if (count($idsAttachments) > 0) {
                    return [
                        "message" => "Registro exitoso",
                        "code" => 200
                    ];
                }
            } else {
                if (count($idsAttachments) > 0) {
                    return [
                        "message" => "Registro exitoso",
                        "code" => 200
                    ];
                }
            }
        } else {

            return [
                "message" => "Adjunte anexos del archivo",
                "code" => 500
            ];
        }
    }

    //*** Funcion general para guardar los archivos en una carpeta local ***//

    public function saveFilesLocal($file) {

        //--- Guardar archivos e imagenes ---//

        $typeFile = strtolower(File::extension($file->getClientOriginalName()));

        $nameFile = $this->codeRandomFiles() . "_" . $file->getClientOriginalName();

        //--- Filtrar imagenes ---//
        if ($typeFile == "png" || $typeFile == "jpg" || $typeFile == "jpeg" || $typeFile == 'JPG' || $typeFile == 'PNG' || $typeFile == 'JPEG') {

            $this->storageImage($nameFile, $file);
        }

        //--- Filtrar archivos ---//
        if ($typeFile == "pdf" || $typeFile == "xlsx" || $typeFile == "xls" || $typeFile == "doc" || $typeFile == "docx") {
            Storage::disk('local')->put('documents/' . $nameFile, File::get($file));
        }

        return $nameFile;
    }

    //*** Funcion para traer archivos relacionado a un archivo de tarea ***//
    public function getAttachment($id_file) {
        $info = array();
        $atach = CvAttachmentFiles::where('file_id', $id_file)->where('state_delet', 0);
        if ($atach->exists()) {
            foreach ($atach->get() as $detail_atach) {
                $ext = pathinfo($detail_atach->name, PATHINFO_EXTENSION);
                if ($ext == 'jpg' || $ext == 'png' || $ext == 'jpeg' || $ext == 'JPG' || $ext == 'PNG' || $ext == 'JPEG') {
                    $type = 'image';
                } else {
                    $type = 'doc';
                }
                $comment_string = "";
                $commentid = CvCommentByFiles::where('attachment_id', $detail_atach->id);
                if ($commentid->exists()) {
                    $comment_string = $comment = CvComment::find((int) $commentid->first()['comment_id'])->description;
                }


                array_push($info, array(
                    "image" => $detail_atach->name,
                    "type" => $extension = $type,
                    "create_ad" => $detail_atach->created_at->format('Y-m-d H:i:s'),
                    "attach_id" => $detail_atach->id,
                    'comment' => $comment_string
                ));
            }
        }

        return $info;
    }

    //*** Funcion para traer archivos relacionado a un archivo de tarea ***//
    public function deleteAttachment($id_file) {
        $atach = CvAttachmentFiles::find($id_file);
        $atach->state_delet = 1;
        $atach->save();
    }

    public function getallFiles64($folder, $url) {
        $images_array = array();

        if (Storage::disk('local')->exists($folder . '/' . $url)) {
            $image = Storage::disk('local')->get($folder . '/' . $url);
            $type = Storage::disk('local')->mimeType($folder . '/' . $url);
        } elseif (Storage::disk('public')->exists($folder . '/' . $url)) {
            $image = Storage::disk('public')->get($folder . '/' . $url);
            $type = Storage::disk('public')->mimeType($folder . '/' . $url);
        } elseif (Storage::disk('storage')->exists($folder . '/' . $url)) {
            $image = Storage::disk('storage')->get($folder . '/' . $url);
            $type = Storage::disk('storage')->mimeType($folder . '/' . $url);
        } else {
            return[
                "message" => "el archivo no existe",
                "code" => 500
            ];
        }
        $forms['file'] = base64_encode($image);
        $forms['type'] = $type;

        return $forms;
    }

    //*** Convertir cualquier archivo en imagen y posteriormente a Base 64 ***//

    public function convertFileToImageAfterBase64() {
        $getFile = Storage::disk('local')->get('documents/0Bm1zCwX7J_Cuenca Verde.pdf');
        return $getFile;
    }

    //*** Guardar archivos de formato imagen y documento ***//
    public function saveFilesShapeGeneral($file) {

        $nameFile = $file->getClientOriginalName();
        $filename = sha1(time() . $nameFile);
        $extension = $file->getClientOriginalExtension();
        $nameFileSha = $filename . '_' . $file->getClientOriginalName() . '.' . $extension;

        if ($extension == "png" || $extension == "jpg" || $extension == "jpeg" || $extension == "JPEG" || $extension == "JPG" || $extension == "PNG") {
            $this->storageImage($nameFileSha, $file);
        } else {
            Storage::disk('local')->put('documents/' . $nameFileSha, File::get($file));
        }

        return $nameFileSha;
    }

    //*** Guardar archivos en la base de datos de forma general ***//
    public function saveFilesTableBD($file) {

        $cvFile = new CvFile();
        $cvFile->name = $file;
        if ($cvFile->save()) {
            return $cvFile->id;
        }
    }

    //*** Obtener extension del archivo ***//
    public function extensionFile($fileName) {

        $extension = null;

        if (strtolower(File::extension($fileName)) == "png" || strtolower(File::extension($fileName)) == "jpg" || strtolower(File::extension($fileName) == "jpeg")) {
            $extension = "img";
        } else {
            $extension = "doc";
        }

        return $extension;
    }

    /**============================
     * Guardar imagenes por base 64 
     *=============================*/

    public function saveImgBase64($file){

        $saveFile64 = new CvImgBase64Task();

        $saveFile64->file = $file;

        if ($saveFile64->save()) {
            return $saveFile64->id;
        }

        return false;

    }

}
