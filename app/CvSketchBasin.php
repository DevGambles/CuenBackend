<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class CvSketchBasin extends Model {

    protected $client;

    public function __construct() {
        $this->client = new Client([
            // Base URI is used with relative requests
            'base_uri' => 'https://hidden-gorge-97519.herokuapp.com/croquis/containingLocation',
            // You can set any number of default request options.
            'timeout' => 2.0,
        ]);
    }

    public function ubicate($lat, $log) {

        try {
            $NroURL = $this->client;
            $response = $NroURL->request('GET', '?lat=' . $lat . '&lng=' . $log . '&maxDistance=250');
            return($response->getBody()->getContents());
        } catch (RequestException $e) {
            return 1;
        }
    }

}
