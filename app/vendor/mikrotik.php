<?php
class Mikrotik{
    private $c;
    function __construct(){
        $this->c = curl_init();
    }
    protected function open(){
        $this->c = curl_init();
    }
    protected function getIpMac($mac,$olt){
        curl_setopt_array($this->c, array(
          CURLOPT_URL => "http://apiphp.local.com:8081/ip/mac?mac=$mac&olt=$olt",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_HTTPHEADER => array(
            'X-Api-Key: fa3b2c9c-a96d-48a8-82ad-0cb775dd3e5d'
          ),
        ));
        $response = curl_exec($this->c);
        curl_close($this->c);
        return $response;
    }
}
