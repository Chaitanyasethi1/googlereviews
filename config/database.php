<?php

try {
    $dbUser = env('DB_USER');
    $dsn = "pgsql:host=" . env('DB_HOST') . ";port=" . env('DB_PORT') . ";dbname=" . env('DB_NAME');
    
    // Fix for Supabase Supavisor pooler (ENOIDENTIFIER error)
    if (strpos(env('DB_HOST'), 'pooler.supabase.com') !== false && strpos($dbUser, '.') !== false) {
        $projectRef = explode('.', $dbUser)[1];
        $dsn .= ";options=endpoint=" . $projectRef;
    }

    $conn = new PDO(
        $dsn,
        $dbUser,
        env('DB_PASS'),
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}