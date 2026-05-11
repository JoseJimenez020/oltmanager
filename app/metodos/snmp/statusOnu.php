<?php
require_once(__DIR__ . '/profileOnu.php');

class statusOnuS extends profileOnuS{
    public function getProfileStatus(){
        $snParse = $this->ParseSnArray($this->getWalk('SN'));
        if (!$snParse || !isset($snParse))
            return null;
        $sn = $snParse['sn'];
        $index = $snParse['index'];
        $pos = $snParse['pos'];
        $rx = $this->dataKey($this->getWalk('RX'));
        $status = $this->dataKey($this->getWalk('STATUS'));

        $dataSets = [
            'sn'=>$sn,
            'index'=>$index,
            'pos'=>$pos,
            'rx'=>$rx,
            'status'=>$status
        ];

        $expectedLength = count($sn);
        foreach ($dataSets as $key => $array) {
            if (!is_array($array) || count($array) !== $expectedLength) {
                return null;
            }
        }
        $p = [];

        for ($i=0; $i < $expectedLength ; $i++) { 
            $p[] = [
                'sn'=>$sn[$i],
                'index'=>$index[$i] . '.' . $pos[$i],
                'pos'=>$pos[$i],
                'rx'=>$rx[$i],
                'status'=>$status[$i]
            ];
        }
        return $p;
    }
    public function gettProfile($onu): ?array{
        if(empty($onu))return null;
        for ($i=0; $i <count($onu) ; $i++) { 
            $profile = $this->gettOneProfile($onu[$i]['index']);
            if(empty($profile)) return null;
            $onu[$i] = array_merge($onu[$i], $profile);
        }
        return $onu;
    }
    public function rxOnu(){
        return $this->getWalk('RX');
    }
}