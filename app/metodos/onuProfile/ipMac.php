<?php
require_once(__DIR__ . '/../../../vendor/mikrotik.php');

class ipMac extends Mikrotik {

    public function __construct() {
        parent::__construct();
    }

    public function open() {
        parent::open();
    }

    private function macUpper($mac) {
        return strtoupper($mac);
    }

    public function ip($m, $olt) {
        $mac = self::macOnu($m);
        $ip  = [];
        for ($i = 0; $i < count($mac); $i++) {
            $r    = json_decode(parent::getIpMac($mac[$i], $olt), true);
            $ip[] = $r['entity'];
        }
        return $ip;
    }

    private function macOnu(array $lineas): array {
        $inicioBloque = -1;
        $finBloque    = -1;

        foreach ($lineas as $i => $linea) {
            if ($inicioBloque === -1 && stripos($linea, 'Total mac') === 0) {
                $inicioBloque = $i;
            } elseif ($inicioBloque !== -1 && preg_match('/^-{5,}$/', trim($linea))) {
                $finBloque = $i;
                break;
            }
        }

        $pos = strpos($lineas[$inicioBloque], ':');

        if ($inicioBloque === -1 || $finBloque === -1
            || trim(substr($lineas[$inicioBloque], $pos + 1)) === 0) {
            return ["xx:xx:xx:xx:xx:xx"];
        }

        $mac = [];
        for ($j = $finBloque + 1; $j < count($lineas); $j++) {
            $linea = $lineas[$j];
            if (preg_match(
                '/^([0-9a-fA-F]{4})\.([0-9a-fA-F]{4})\.([0-9a-fA-F]{4})/',
                $linea,
                $matches
            )) {
                $macPlano     = $matches[1] . $matches[2] . $matches[3];
                $macFormateada = implode(':', str_split($macPlano, 2));
                $mac[]        = self::macUpper($macFormateada);
                $lineas[$j]   = preg_replace(
                    '/^([0-9a-fA-F]{4}\.[0-9a-fA-F]{4}\.[0-9a-fA-F]{4})/',
                    $macFormateada,
                    $linea
                );
            }
        }

        return $mac;
    }
}
