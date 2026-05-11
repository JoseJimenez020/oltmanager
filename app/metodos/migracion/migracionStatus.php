<?php
require_once(__DIR__ . '/../../metodos/onuProfile/onuProfileController.php');
require_once(__DIR__ . '/../../metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '/../../metodos/gpon/gponController.php');
require_once(__DIR__ . '/../../metodos/snmp/profileOnu.php');
require_once(__DIR__ . '/migracionController.php');

class migracionStatus
{
    private $o;
    private $olt;
    private static $gpon;

    public function __construct()
    {
        $gpon        = new gponController();
        self::$gpon  = $gpon->getGpon();
        $this->o     = new onuProfileController();
        $this->olt   = new oltProfileController();
    }

    public function insertMigracion($onus)
    {
        $migracion = [];
        $agrupado  = [];

        foreach ($onus as $elemento) {
            $olt = $elemento['olt'];
            if (!isset($agrupado[$olt])) {
                $agrupado[$olt] = [];
            }
            $agrupado[$olt][] = $elemento;
        }

        foreach ($agrupado as $k => $v) {
            $mi        = $this->getMigracion($k, $v);
            $migracion = array_merge($migracion, $mi);
        }

        $m = new migracionController();
        $r = $m->insertMigracions($migracion);
        return $r;
    }

    public function getMigracion($id, $v)
    {
        $r      = $this->o->getOnuGponM($id);
        $re     = $v;
        $result = [];
        $newSnIndexMap = [];

        if (empty(self::$gpon)) return [];

        foreach ($re as $sn) {
            $index = $sn['index'];
            $dot   = strpos($index, '.');
            $len   = strlen($index);
            $subs  = $dot - $len;
            $i     = substr($index, 0, $subs);

            $newSnIndexMap[$sn['sn']]['index'] = $sn['index'];
            $newSnIndexMap[$sn['sn']]['pos']   = $sn['pos'];
            $newSnIndexMap[$sn['sn']]['gpon']  = self::$gpon[$i]['IdOlt'];
        }

        foreach ($r as $entry) {
            $oldIndex = $entry['IndexOid'] . '.' . $entry['OntPos'];
            $sn       = $entry['OnuSn'];

            if (isset($newSnIndexMap[$sn]['index'])) {
                $newIndex = $newSnIndexMap[$sn]['index'] . '.' . $newSnIndexMap[$sn]['pos'];
                if ($newIndex !== $oldIndex) {
                    $result[] = [
                        'indexNuevo' => $newSnIndexMap[$sn]['gpon'],
                        'posNuevo'   => $newSnIndexMap[$sn]['pos'],
                        'idOnu'      => $entry['OntId'],
                    ];
                }
            }
        }

        return $result;
    }
}
