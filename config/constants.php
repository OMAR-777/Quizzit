<?php
define('WEBLINK','http://quizzit.us-east-1.elasticbeanstalk.com');//change to ur location/filename

// if ($_SERVER['HTTP_HOST'] == 'localhost') {//responsive to host
//     define('DB_HOST', 'localhost');
//     define('DB_USER', 'root');
//     define('DB_PASS', '123456Ab');
//     define('DB_NAME', 'quizzit');
//     define('PORT', '3306');
// } else {
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
    define('DB_NAME', getenv('DB_NAME'));
    define('PORT', getenv('PORT'));
    
// }
