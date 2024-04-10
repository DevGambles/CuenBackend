<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NotificationOneSignal extends Model {

    //*** Crear notificacion ***//

    public function createNotification($contentInfo, $playersIds, $options, $data) {

        $content = array(
            "en" => $contentInfo
        );

        $fieldsData = array(
            'app_id' => "2a42d1b4-c963-4bae-a93d-916dbff0b2d4",
            'include_player_ids' => $playersIds,
            'data' => $data,
            'contents' => $content,
            'web_buttons' => $options
        );

        $fields = json_encode($fieldsData);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json; charset=utf-8',
            'Authorization: Basic M2Q4MTY5YzItMzY3NC00M2I4LTgxZmEtZjA1ZDRhMzMxNmEy'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }

}
