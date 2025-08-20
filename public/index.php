<?php
require_once __DIR__ . '/../db.php';

$db = new Database();
$conn = $db->connect();

echo "Conectado correctamente";