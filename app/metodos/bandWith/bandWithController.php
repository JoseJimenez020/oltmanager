<?php
require_once(__DIR__ . '/bandWithDb.php');
require_once(__DIR__ . '../../oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../snmp/oltSnmp.php');
require_once(__DIR__ . '../../snmp/bandWith.php');
class bandWithController{
    private $db;
    private $olt;
    public function __construct(){
        $this->db = new bandWith();
        $this->olt = new oltProfileController();
    }

    public function getAll(){
        $result = $this->db->getAll();
        return $result;
    }
    public function getWhere($id){
        $result = $this->db->getAllWhere($id);

        $new = array(
        );
        foreach ($result as $v) {
            $rx = $this->getFormattedFileSize($v['RxBand'],0);
            $tx=$this->getFormattedFileSize($v['TxBand'],0);
            $new[] =[
                'Rx'=>$rx['size'][0],
                'typeRx'=>$rx['type'][0],
                'Tx'=>$tx['size'][0],
                'typeTx'=>$tx['type'][0],
                'date'=>$v['Date']
            ];
        }
        return $new;

    }
    public function insertBand(){
        $band = $this->getBands();
        if(is_null($band))return null;
        $result = $this->db->insertBand($band);
        return $result;
    }
    public function getBands(){
        $ol = $this->olt->getOlt();
        $band = [];
        foreach ($ol as $val) {
            $b=$this->bandWithAdd($val['OltIdApi']);
            if(is_null($b)) continue;
            $band = array_merge($band, $b);
            set_time_limit(120);
        }
        return $band;
    }
    public function bandWithAdd($zone){
        date_default_timezone_set('America/Merida');
        $band=[];
        $re=$this->olt->getIndexOlt($zone);
        $snmp = new nSnmp($zone,'read');
        $walk= new bandWithS($snmp);
        $date= date("Y-m-d H:i:s");
        $down =$walk->bandWithDown();
        if (empty($down) ){
            $walk->close(); 
            return null;
            }
        $up =$walk->bandWithUp();
        if (empty($up) ){
            $walk->close();
            return null;
        }
        $walk->close();
        foreach ($re as $val) {
            $index = $val['IndexIntGpon'] . '.'.$val['OntPos'];
            $d=@$down[$index];
            if (isset($d)) {
                $band[] = [ 
                'IdOnu'=>$val['OntId'],
                'Rx'=>$up[$index],
                'Tx'=> $down[$index],
                'Date'=>$date
                ];
            }
        }
        return $band;
    }    
    function getFormattedFileSize($size, $precision){
        $format = array(
            'size'=>array(),
            'type'=>array()
        );
        switch (true) 
        {
            case ($size/1024 < 1):
                $format['size'][]=$size;
                $format['type'][]='B';
                return $format;
                //return $size.' B';
            case ($size/pow(1024, 2) < 1):
                $format['size'][]=round($size/1024, $precision);
                $format['type'][]='K';
                return $format;
                //return round($size/1024, $precision).' KB';
            case ($size/pow(1024, 3) < 1):
                $format['size'][]=round($size/pow(1024, 2), $precision);
                $format['type'][]='M';
                return $format;
                //return round($size/pow(1024, 2), $precision).' MB';
            case ($size/pow(1024, 4) < 1):
                return round($size/pow(1024, 3), $precision).'GB';
            case ($size/pow(1024, 5) < 1):
                return round($size/pow(1024, 4), $precision).'TB';
            default:
                return 'Error: invalid input or file is too large.';
        }
    }
}