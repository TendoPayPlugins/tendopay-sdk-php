<?php
session_start();

$initCredentials = $_SESSION['credentials'] ?? '{}';

require __DIR__.'/index.html.php';
