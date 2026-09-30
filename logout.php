<<?php
    require_once __DIR__ . '/includes/app.php';

    logout_user();
    session_start();
    session_regenerate_id(true);
    flash_add('success', 'You have been logged out.');
    redirect('index.php');
