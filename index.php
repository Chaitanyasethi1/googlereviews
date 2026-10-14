<?php

require 'config/env.php';        // Load environment variables first
require 'config/database.php';   // This creates $conn variable
require 'app/SessionHandler.php';// Register Postgres session handler

session_start();
ob_start();
require 'app/requireOwner.php';
require 'app/Router.php';
require 'routes/index.php';

// Make $conn available globally
$GLOBALS['conn'] = $conn;