<?php
require_once(__DIR__ . '../../../../db/conn.php');

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

class IPsTableHandler extends DbConn
{
    protected $pdo;

    public function __construct()
    {
        parent::__construct();
        $this->pdo = $this->getPdo();
    }

    public function handleRequest()
    {
        try {
            // Parámetros de DataTables
            $draw = $_GET['draw'] ?? 1;
            $start = $_GET['start'] ?? 0;
            $length = $_GET['length'] ?? 10;
            $searchValue = $_GET['search']['value'] ?? '';
            $oltId = $_GET['olt_id'] ?? $_GET['id'] ?? null;

            if (!$oltId) {
                throw new Exception("Se requiere el parámetro olt_id");
            }

            // 1. Primero verificamos que el OLT exista
            $stmtCheckOlt = $this->pdo->prepare("SELECT IdOltList FROM olts_list WHERE OltIdApi = :oltId");
            $stmtCheckOlt->execute([':oltId' => $oltId]);

            if ($stmtCheckOlt->rowCount() === 0) {
                echo json_encode([
                    "draw" => intval($draw),
                    "recordsTotal" => 0,
                    "recordsFiltered" => 0,
                    "data" => [],
                    "message" => "El OLT con ID $oltId no existe en la base de datos"
                ]);
                return;
            }

            // Columnas disponibles para ordenamiento
            $columns = [
                0 => 'i.ipAddress',
                1 => 'i.mask',
                2 => 'i.defaultGateway',
                3 => 'i.firstDns',
                4 => 'i.secDns'
            ];

            // Configuración de ordenamiento
            $orderColumn = $_GET['order'][0]['column'] ?? 0;
            $orderDir = strtoupper($_GET['order'][0]['dir'] ?? 'asc');
            $orderDir = in_array($orderDir, ['ASC', 'DESC']) ? $orderDir : 'ASC';
            $orderField = $columns[$orderColumn] ?? $columns[0];

            // Consulta base con LEFT JOIN para mayor seguridad
            $baseQuery = "SELECT 
                        i.id_Ip as id, 
                        i.ipAddress, 
                        i.mask, 
                        i.defaultGateway, 
                        i.firstDns, 
                        i.secDns,
                        o.OltName
                      FROM ips i
                      LEFT JOIN olts_list o ON i.olt_id = o.IdOltList
                      WHERE i.olt_id = :oltId";

            // Parámetros iniciales
            $params = [':oltId' => $oltId];
            $filterQuery = '';

            // Aplicar filtro de búsqueda si existe
            if (!empty($searchValue)) {
                $filterQuery = " AND (i.ipAddress LIKE :search 
                              OR i.mask LIKE :search 
                              OR i.defaultGateway LIKE :search
                              OR i.firstDns LIKE :search
                              OR i.secDns LIKE :search)";
                $params[':search'] = "%$searchValue%";
            }

            // Consulta para el total de registros (sin paginación)
            $totalQuery = "SELECT COUNT(*) FROM ips WHERE olt_id = :oltId";
            $stmtTotal = $this->pdo->prepare($totalQuery);
            $stmtTotal->execute([':oltId' => $oltId]);
            $totalRecords = $stmtTotal->fetchColumn();

            // Consulta para registros filtrados (con búsqueda)
            $filteredQuery = $totalQuery;
            if (!empty($searchValue)) {
                $filteredQuery .= " AND (ipAddress LIKE :search 
                                    OR mask LIKE :search 
                                    OR defaultGateway LIKE :search
                                    OR firstDns LIKE :search
                                    OR secDns LIKE :search)";
            }

            $stmtFiltered = $this->pdo->prepare($filteredQuery);
            $stmtFiltered->execute($params);
            $filteredRecords = $stmtFiltered->fetchColumn();

            // Consulta principal con paginación
            $query = $baseQuery . $filterQuery . " ORDER BY $orderField $orderDir LIMIT :start, :length";
            $params[':start'] = (int) $start;
            $params[':length'] = (int) $length;

            $stmt = $this->pdo->prepare($query);
            foreach ($params as $key => $value) {
                $paramType = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $paramType);
            }
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Respuesta para DataTables
            echo json_encode([
                "draw" => intval($draw),
                "recordsTotal" => intval($totalRecords),
                "recordsFiltered" => intval($filteredRecords),
                "data" => $data,
                "oltInfo" => [
                    "id" => $oltId,
                    "exists" => true
                ]
            ]);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode([
                "error" => true,
                "message" => "Error de base de datos: " . $e->getMessage(),
                "draw" => intval($draw ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                "error" => true,
                "message" => $e->getMessage(),
                "draw" => intval($draw ?? 1),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }
    }
}

$handler = new IPsTableHandler();
$handler->handleRequest();