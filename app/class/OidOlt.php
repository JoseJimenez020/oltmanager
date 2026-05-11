<?php

class Olt{

    public $oid =[
        'OltGponIndex'=>'.1.3.6.1.4.1.3902.1012.3.13.1.1.1',
        "SysTemp"=>"1.3.6.1.4.1.3902.1015.2.1.3.2",
        "SysUpTime"=>".1.3.6.1.2.1.1.3",
        "SysOlt"=>".1.3.6.1.2.1.1.5",
        "SysIntGpon"=>".1.3.6.1.2.1.31.1.1.1.1"
    ];

    public function oltOid(){
        return $this->oid;
    }
}