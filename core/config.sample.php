<?php

# System
define("DEVELOPMENT", 1);
define("__ROOTPATH__", $_SERVER['DOCUMENT_ROOT']);

# Database
define("MYSQL_HOST", "");
define("MYSQL_DBNAME", "");
define("MYSQL_USER", "");
define("MYSQL_PASS", "");

# Email
define("EMAIL_FROM", "noreply@research.jyu.fi");
define("EMAIL_HOST", "");
define("EMAIL_PORT", 25);
define("EMAIL_AUTH", false);
define("EMAIL_AUTH_USERNAME", "");
define("EMAIL_AUTH_PASSWORD", "");
define("EMAIL_SMTP_SECURE", "ssl");
define("EMAIL_SMTP_OPTIONS", array());