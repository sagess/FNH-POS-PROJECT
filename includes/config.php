<?php

// Database configuration constants
define('DB_HOST', 'localhost');
define('DB_NAME', 'fnh_pos'); 
define('DB_USER', 'root'); 
define('DB_PASS', ''); 

define('SITE_TITLE', 'FnH Groceries');  // The title of the site, used in the header and other places
define('DEVELOPER_NAME', 'Sagesse Joseph'); // Developer's name for credits or contact information
define('FEEDBACK_EMAIL', 's.joseph0934@student.nu.edu'); // Email address for feedback and support      


define('STORE_ID', 1);  // The ID of the store in the database (used for multi-store setups)                

define('TAX_RATE', 0.0775);// Sales tax rate (7.75%)


define('LANDING_PAGE', 'menu.php'); // The page to redirect users to after login   

define( 'EXPRESS_DAILY_CAPACITY',20); // Maximum number of express orders allowed per day
define('DELIVERY_FEE', '10.00');   // Constants for delivery fee and time window
define('DELIVERY_WINDOW_START', '08:00:00'); // ... from 8 AM
define('DELIVERY_WINDOW_END', '16:00:00'); // ... until 4 PM
define('SALE_STATUS_COMPLETED', 'completed'); // the value your `sales.status` uses for a finished sale
