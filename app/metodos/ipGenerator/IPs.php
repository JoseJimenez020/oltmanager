<?php
require_once(__DIR__ . '../../../../db/conn.php');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

class IPsHandler extends DbConn
{
    public function handleRequest()
    {
        // Obtener y validar los datos JSON
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !$data) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Datos JSON inválidos']);
            exit;
        }

        // Validar campos obligatorios (incluyendo olt_id)
        $requiredFields = ['ip_range', 'mask', 'default_gateway', 'first_dns', 'second_dns', 'olt_id'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Campo $field es requerido"]);
                exit;
            }
        }

        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // 1. Verificar IPs existentes para esta OLT específica
            $existingIPs = $this->checkExistingIPs($data['ip_range'], $data['olt_id']);

            if (!empty($existingIPs)) {
                $this->pdo->rollBack();
                http_response_code(409); // Conflict
                echo json_encode([
                    'success' => false,
                    'message' => 'Algunas IPs ya existen para esta OLT',
                    'existing_ips' => $existingIPs,
                    'duplicate_count' => count($existingIPs)
                ]);
                exit;
            }

            // 2. Insertar solo IPs nuevas para esta OLT
            $insertResults = $this->insertNewIPs(
                $data['ip_range'],
                $data['mask'],
                $data['default_gateway'],
                $data['first_dns'],
                $data['second_dns'],
                $data['olt_id']
            );

            // Confirmar transacción
            $this->pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => $insertResults['inserted_count'] . ' IPs almacenadas correctamente para esta OLT',
                'inserted_count' => $insertResults['inserted_count'],
                'inserted_ips' => $insertResults['inserted_ips']
            ]);

        } catch (PDOException $e) {
            // Revertir transacción en caso de error
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error de base de datos: ' . $e->getMessage(),
                'error_code' => $e->getCode()
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Verifica qué IPs del rango ya existen para esta OLT específica
     */
    private function checkExistingIPs(array $ipRange, int $oltId): array
    {
        if (empty($ipRange)) {
            return [];
        }

        // Crear marcadores de posición para la consulta IN
        $placeholders = rtrim(str_repeat('?,', count($ipRange)), ',');
        $params = $ipRange;
        $params[] = $oltId;

        $stmt = $this->pdo->prepare("
            SELECT ipAddress 
            FROM ips 
            WHERE ipAddress IN ($placeholders)
            AND olt_id = ?
        ");

        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Inserta solo las IPs que no existen para esta OLT específica
     */
    private function insertNewIPs(
        array $ipRange,
        string $mask,
        string $gateway,
        string $firstDns,
        string $secondDns,
        int $oltId
    ): array {
        $insertedCount = 0;
        $insertedIPs = [];
        $stmt = $this->pdo->prepare("
            INSERT INTO ips (
                ipAddress, 
                mask, 
                defaultGateway, 
                firstDns, 
                secDns,
                olt_id
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($ipRange as $ip) {
            try {
                $stmt->execute([$ip, $mask, $gateway, $firstDns, $secondDns, $oltId]);
                $insertedCount++;
                $insertedIPs[] = $ip;
            } catch (PDOException $e) {
                // Error 1062 es clave duplicada en MySQL
                if ($e->getCode() !== '23000') {
                    throw $e; // Relanzar excepciones que no sean por duplicados
                }
                // Continuar con la siguiente IP si esta ya existe
                continue;
            }
        }

        return [
            'inserted_count' => $insertedCount,
            'inserted_ips' => $insertedIPs
        ];
    }
}

// Ejecutar el manejador
$handler = new IPsHandler();
$handler->handleRequest();