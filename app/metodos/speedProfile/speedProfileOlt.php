<?php
require_once(__DIR__ . '../../snmp/speedProfile.php');
require_once(__DIR__ . '/speedProfileController.php');
require_once(__DIR__ . '../../oltProfile/oltProfileController.php');

class speedProfileOlt{
    private $speed;
    private $olt;

    public function __construct(){
        $this->speed = new speedProfileController();
        $this->olt = new oltProfileController();
    }
    public function insertSpeed(){
        $o=$this->olt->GetOlt();
        foreach ($o as $k) {
            $spe=new speedProfileS($k['OltIdApi'],'read');
            $this->speed->insertSpeedOlts($spe->getSpeedProfile(),$k['OltIdApi']);
        }
    }
}
