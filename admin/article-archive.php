<?php

declare(strict_types=1);

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../config/database.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid article ID.');
}

try {

    $stmt = $pdo->prepare("
        UPDATE articles
        SET
            status = 'archived',
            published_at = NULL
        WHERE id = ?
        LIMIT 1
    ");

    $stmt->execute([$id]);

    header('Location: articles.php?archived=1');
    exit;

} catch (PDOException $e) {

    exit('Unable to archive article.');
}