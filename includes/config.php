<?php

define('DB_HOST', 'localhost');
define('DB_NAME', 'fnh_pos'); 
define('DB_USER', 'root');    
define('DB_PASS', '');

define('SITE_TITLE', 'FnH Groceries');  
define('DEVELOPER_NAME', 'Sagesse Joseph'); 
define('FEEDBACK_EMAIL', 's.joseph0934@student.nu.edu');       


define('STORE_ID', 1);                   

define('TAX_RATE', 0.0775);


define('LANDING_PAGE', 'menu.php');     

define( 'EXPRESS_DAILY_CAPACITY',20);
define('DELIVERY_FEE', '10.00');   // charged on home delivery, added to total due
define('DELIVERY_WINDOW_START', '08:00:00'); // van runs from 8 AM ...
define('DELIVERY_WINDOW_END', '16:00:00'); // ... until 4 PM
define('SALE_STATUS_COMPLETED', 'completed'); // the value your `sales.status` uses for a finished sale
