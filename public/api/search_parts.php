<?php

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/database.php';

try {

    $database = new Database();
    $conn = $database->connect();

    // Get the search keyword
    $keyword = trim($_GET['q'] ?? '');

    // Prepare the search pattern
    $search = '%' . $keyword . '%';

    // Search by name, category or brand
    $sql = "SELECT part_id, name, category, brand, price, stock
            FROM parts
            WHERE name LIKE :name_keyword
               OR category LIKE :category_keyword
               OR brand LIKE :brand_keyword
            ORDER BY name ASC
            LIMIT 50";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
        ':name_keyword' => $search,
        ':category_keyword' => $search,
        ':brand_keyword' => $search
    ]);

    $parts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'count' => count($parts),
        'parts' => $parts
    ]);

} catch (PDOException $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'message' => 'Unable to search spare parts.'
    ]);

}