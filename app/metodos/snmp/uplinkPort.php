<?php
require_once(__DIR__ . '/oltSnmp.php');
class uplinkPortS
{
    public $hasError = false;
    private $s;
    protected const IFNAME = '.1.3.6.1.2.1.31.1.1.1.1';
    protected const IFSPEED = '.1.3.6.1.2.1.2.2.1.5';
    protected const IFMTU = '.1.3.6.1.2.1.2.2.1.4';
    protected const IFOPERSTATUS = '.1.3.6.1.2.1.2.2.1.8';
    protected const IFALIAS = '.1.3.6.1.2.1.31.1.1.1.18';
    protected const IFADMINSTATUS = '.1.3.6.1.2.1.2.2.1.7';
    protected const ETHACTUALDUPLEX = '1.3.6.1.4.1.3902.1082.30.20.2.2.3.1.3';
    protected const CONFCONNECTORTYPE = '1.3.6.1.4.1.3902.1082.30.20.2.2.3.1.6';
    protected const CONFDUPLEXSPEED = '1.3.6.1.4.1.3902.1082.30.20.2.2.3.1.2';
    protected const VLANTAGGED = '1.3.6.1.4.1.3902.1082.40.50.2.1.4.1.7';
    protected const DEFAULTVID = '1.3.6.1.4.1.3902.1082.40.50.2.1.4.1.4';
    protected const CONFMODE = '1.3.6.1.4.1.3902.1082.40.50.2.1.4.1.1';//zxAnVlanIfConfMode
    protected const OPTICALWAVE = '1.3.6.1.4.1.3902.1082.30.40.2.4.1.7';
    protected const OPTICALTEMP = '1.3.6.1.4.1.3902.1082.30.40.2.4.1.8';
    public function __construct(nSnmp $snmp)
    {
        $this->s = $snmp;
    }
    public function getWalk($constante): ?array
    {
        $clase = get_class($this);
        if (defined("$clase::$constante")) {
            if ($this->hasError)
                return null;
            $result = $this->s->walk(constant("$clase::$constante"));
            if (isset($result['error'])) {
                $this->hasError = true;
                return null;
            }
            return $result;
        } else {
            return null;
        }
    }
    public function gettUplink($index): ?array
    {
        if (empty($index))
            return null;
        $p = ['ifspeed' => self::IFSPEED, 'mtu' => self::IFMTU,
                'operstatus' => self::IFOPERSTATUS, 'alias' => self::IFALIAS,'adminstatus'=>self::IFADMINSTATUS,
                'actualduplex' => self::ETHACTUALDUPLEX, 'conectortype' => self::CONFCONNECTORTYPE,
                'confduplexspeed'=>self::CONFDUPLEXSPEED,'vlantagged'=>self::VLANTAGGED,
                'optwavelength'=>self::OPTICALWAVE,'opttemp'=>self::OPTICALTEMP];
        $new = [];
        foreach ($p as $key => $value) {
            if ($key == 'vlantagged') {
                $new[] = $value . '.' . $index . '.0';
                continue;
            }
            $new[] = $value . '.' . $index;
        }
        $profile = $this->s->gett($new);
        if (isset($profile['error']))
            return null;
        $keys = array_keys($p);
        $newArray = array_combine($keys, $profile);
        return $newArray;
    }
    public function getOid($constante)
    {
        $clase = get_class($this);
        if (defined("$clase::$constante")) {
            return constant("$clase::$constante");
        } else {
            return null;
        }
    }
}