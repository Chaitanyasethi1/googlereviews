<?php

function isOwner() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_email'])) {
        return false;
    }
    
    $ownerEmail = env('OWNER_EMAIL');
    return strtolower($_SESSION['user_email']) === strtolower($ownerEmail);
}

