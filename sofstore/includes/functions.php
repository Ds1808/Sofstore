<?php
if (!function_exists('formatCOP')) {
    function formatCOP($amount) {
        return '$' . number_format(floatval($amount), 0, ',', '.') . ' COP';
    }
}

if (!function_exists('jsonResponse')) {
    function jsonResponse($success, $message = '', $data = []) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => (bool)$success,
            'message' => $message,
            'data'    => $data
        ]);
        exit;
    }
}