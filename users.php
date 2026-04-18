<?php
require_once '../includes/config.php';
if(!isAdmin()) redirect('../login.php');
// Redirect to dedicated access page
redirect('aksè.php');
