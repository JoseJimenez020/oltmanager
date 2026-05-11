<?php
require_once(__DIR__ . '/oltSnmp.php');

class bandWithS{
    protected $s;
    protected const TXOCT = '.1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.46';
    protected const RXOCT = '.1.3.6.1.4.1.3902.1082.500.4.2.2.2.1.3';
    public function __construct(nSnmp $snmp)
    {
        $this->s = $snmp;
    }
    public function close(){
        $this->s->close();
    }
    public function bandWithDown(){
        $tx = $this->s->walk(self::TXOCT);
        if (isset($tx['error'])) {
            return null;
        }
        return $tx;
    }
    public function bandWithUp(){
        $rx = $this->s->walk(self::RXOCT);
        if (isset($rx['error'])) {
            return null;
        }
        return $rx;
    }
}