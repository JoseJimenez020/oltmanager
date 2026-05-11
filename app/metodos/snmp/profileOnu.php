<?php
require_once(__DIR__ . '/oltSnmp.php');

class profileOnuS
{
    protected $s;
    protected bool $hasError = false;
    protected const NAME = '.1.3.6.1.4.1.3902.1012.3.28.1.1.2';
    protected const STATUS = '.1.3.6.1.4.1.3902.1012.3.28.2.1.4';
    protected const OPERSTATE = '.1.3.6.1.4.1.3902.1012.3.50.11.2.1.8';
    protected const RX = '1.3.6.1.4.1.3902.1015.1010.11.2.1.2';
    protected const MODEL = '.1.3.6.1.4.1.3902.1012.3.28.1.1.1';
    protected const DESC = '.1.3.6.1.4.1.3902.1012.3.28.1.1.3';
    protected const DIS = '.1.3.6.1.4.1.3902.1012.3.11.4.1.2';
    protected const SN = '.1.3.6.1.4.1.3902.1012.3.28.1.1.5';
    protected const TCONT = '.1.3.6.1.4.1.3902.1012.3.30.1.1.3';
    protected const GEMPORT = '.1.3.6.1.4.1.3902.1012.3.30.2.1.7';
    protected const VLAN = '.1.3.6.1.4.1.3902.1012.3.50.13.3.1.1';
    protected const ADMINSTATE = '.1.3.6.1.4.1.3902.1012.3.28.2.1.1';
    protected const ONUIP = '.1.3.6.1.4.1.3902.1012.3.50.16.1.1.10';
    public function __construct(nSnmp $snmp = null)
    {
        if(isset($snmp)) $this->s = $snmp;
    }
    protected function ParseSnArray($sn)
    {
        if(empty($sn)) return null;
        $var = $sn;
        $new = array();

        foreach ($var as $key1 => $value1) {
            $posIndex = $this->posIndex($key1);
            $value1 = stripslashes($value1);
            $len = strlen($value1);
            if ($len == 26) {
                $replace = str_replace(' ', '', $value1);
                $subsVendor = substr($replace, 1, 8);
                $vendor = hex2bin($subsVendor);

                $subsSn = substr($replace, 9, -1);
                $sn = $vendor . $subsSn;
                $new['sn'][] = $sn;
                $new['index'][] = $posIndex['index'];
                $new['pos'][] = $posIndex['pos'];
            } elseif ($len == 11) {
                $vendorPos = strpos($value1, "\\");
                $subsVendor = substr($value1, 1, $vendorPos - 1);

                $subsSn = substr($value1, $vendorPos + 1, -1);
                $strToHex = bin2hex($subsSn);
                $sn = $subsVendor . $strToHex;
                $snUpCase = strtoupper($sn);

                $p = strlen($snUpCase);
                if ($p != 12) {
                    $subsErSn = substr($sn, 4);
                    $subsOne = substr($subsErSn, 0, 1);
                    $subsNewSn = substr($sn, 5);
                    $subsNewVendor = substr($subsVendor, 0, 4);

                    $oneToHex = bin2hex($subsOne);
                    $snUpCase = strtoupper($oneToHex . $subsNewSn);
                    $newSn = $subsNewVendor . $snUpCase;

                    $new['sn'][] = $newSn;
                    $new['index'][] = $posIndex['index'];
                    $new['pos'][] = $posIndex['pos'];
                    //array_push($new,$newSn);
                } else {
                    $new['sn'][] = $snUpCase;
                    $new['index'][] = $posIndex['index'];
                    $new['pos'][] = $posIndex['pos'];
                    //array_push($new,$snUpCase);
                }
            } elseif ($len != 26) {
                switch ($len) {
                    case 10:
                        $p = substr($value1, 0, 1);
                        if ($p == '"') {
                            $subsFirts = substr($value1, 1, $len - 2);
                            $lenFirst = strlen($subsFirts);
                            $vendor = substr($subsFirts, 0, $lenFirst - 4);

                            $subsSecond = substr($subsFirts, $lenFirst - 4);
                            $parseSn = bin2hex($subsSecond);
                            $sn = $vendor . $parseSn;
                            $snUpCase = strtoupper($sn);

                            $new['sn'][] = $snUpCase;
                            $new['index'][] = $posIndex['index'];
                            $new['pos'][] = $posIndex['pos'];
                        } else {
                            $new['sn'][] = 'nosn';
                            $new['index'][] = $posIndex['index'];
                            $new['pos'][] = $posIndex['pos'];
                        }
                        break;

                    default:
                        $new['sn'][] = $value1;
                        $new['index'][] = $posIndex['index'];
                        $new['pos'][] = $posIndex['pos'];
                        break;
                }
            }

        }
        return $new;
    }
    protected function deleteQuote($var)
    {
        $findString = substr($var, 0, 1);
        if ($findString === '"') {
            $subs1 = substr($var, 1);
            $subs2 = substr($subs1, 0, -1);
            return $subs2;
        } else {
            return $var;
        }
    }
    protected function posIndex($index)
    {
        $dot = strpos($index, '.');
        $len = strlen($index);
        $subs = $dot - $len;
        $po = substr($index, $subs + 1);
        $in = substr($index, 0, $subs);
        $result = [
            'index' => $in,
            'pos' => $po
        ];
        return $result;
    }
    private function dataArray($data)
    {
        $new = [
            'index' => array(),
            'pos' => array(),
            'value' => array()
        ];
        foreach ($data as $k => $v) {
            $reIndex = $this->posIndex($k);
            $new['index'][] = $reIndex['index'];
            $new['pos'][] = $reIndex['pos'];
            $new['value'][] = $this->deleteQuote($v);
        }
        return $new;
    }
    protected function dataKey(?array $array): ?array
    {
        if (!is_array($array)) return null;
        $i = 0;
        $new = [];
        foreach ($array as $k => $v) {
            $new[$i] = $this->deleteQuote($v);
            $i++;
        }
        return $new;
    }
    public function getProfile(): ?array
    {
        $name = $this->getWalk('NAME');
        if (is_null($name))
            return null;
        $snParse = $this->ParseSnArray($this->getWalk('SN'));
        if (!$snParse || !isset($snParse['sn']))
            return null;
        $sn = $snParse['sn'];
        $index = $snParse['index'];
        $pos = $snParse['pos'];
        $name = $this->dataKey($name);
        $model = $this->dataKey($this->getWalk('MODEL'));
        $desc = $this->dataKey($this->getWalk('DESC'));
        $dis = $this->dataKey($this->getWalk('DIS'));
        $rx = $this->dataKey($this->getWalk('RX'));
        $status = $this->dataKey($this->getWalk('STATUS'));
        $tcont = $this->dataTcont($this->getWalk('TCONT'));
        $gemport = $this->dataTcont($this->getWalk('GEMPORT'));

        $datasets = [
            'name' => $name,
            'sn' => $sn,
            'index' => $index,
            'pos' => $pos,
            'model' => $model,
            'desc' => $desc,
            'dis' => $dis,
            'rx' => $rx,
            'status' => $status,
            'tcont' => $tcont,
            'gemport' => $gemport,
        ];

        $expectedLength = count($name);
        foreach ($datasets as $key => $array) {
            if (!is_array($array) || count($array) !== $expectedLength) {
                return null;
            }
        }

        $p = [];
        for ($i = 0; $i < count($name); $i++) {
            $p[] = [
                'sn' => $sn[$i],
                'index' => $index[$i],
                'pos' => $pos[$i],
                'name' => $name[$i],
                'model' => $model[$i],
                'desc' => $desc[$i],
                'dis' => $dis[$i],
                'rx' => $rx[$i],
                'status' => $status[$i],
                'tcont' => $tcont[$i],
                'gemport' => $gemport[$i],
            ];
        }
        return $p;
    }
    public function close()
    {
        $this->s->close();
    }
    public function getWalk($constante): ?array{
        $clase = get_class($this);
        if(defined("$clase::$constante")){
            if ($this->hasError)
            return null;
            $result = $this->s->walk(constant("$clase::$constante"));
            if (isset($result['error'])) {
                $this->hasError = true;
                return null;
            }
            return $result;
        }else{
            return null;
        }
    }
    public function dataTcont($var): ?array
    {
        if (!is_array($var)) return null;
        $tcont = array();
        foreach ($var as $key => $value) {
            $in = substr($key, -1);
            if ($in == 1) {
                $tcont[] = $value;
            }
        }
        return $tcont;
    }
    public function getOnuProfile($index): ?array
    {
        $rx = self::RX . '.' . $index;
        $sta = self::STATUS . '.' . $index;
        $dis = self::DIS . '.' . $index;
        $admin = self::ADMINSTATE . '.' . $index;
        $ip = self::ONUIP . '.' . $index;
        $var = [$rx, $sta, $dis, $admin, $ip . '.1', $ip . '.2'];
        return $this->s->gett($var);
    }
    public function gettOneProfile($index): ?array{
        if(empty($index)) return null;
        $p = ['name'=>self::NAME,'model'=>self::MODEL,'desc'=>self::DESC,'dis'=>self::DIS,'tcont'=>self::TCONT,'gemport'=>self::GEMPORT];
        $new = [];
        foreach ($p as $key => $value) {
            if($key == 'tcont' || $key == 'gemport') {
                $new[] = $value . '.' . $index . '.1';
                continue;
            }
            $new[] = $value . '.' . $index;
        }
        $profile = $this->s->gett($new);
        if(isset($profile['error'])) return null;
        $keys = array_keys($p);
        $newArray = array_combine($keys, $profile);
        return $newArray;
    }
}