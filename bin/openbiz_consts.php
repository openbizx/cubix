<?php

/* ================== CORE CONFIG =================================== */
/**
 * application related path
 */
define('OPENBIZ_APP_PATH', dirname(dirname(__FILE__)));

//echo OPENBIZ_APP_PATH;
//enable minify 
define('OPENBIZ_PAGE_MINIFY', 0);

define('OPENBIZ_DEFAULT_SYSTEM_NAME', 'Cubi Platform');

define('OPENBIZ_APP_URL', get_app_url());

//echo OPENBIZ_APP_URL;
//exit;
/* OPENBIZ_APP_INDEX_URL is /a/b/index.php in case of http://host/a/b/index.php?... */
$indexScript = "/index.php"; // or "", or "/?"
define('OPENBIZ_APP_INDEX_URL', OPENBIZ_APP_URL . $indexScript);

/* define modules path */
define('OPENBIZ_APP_MODULE_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "modules");

/* define modules extension path that can store custom code who overrides default module logic */
//define('MODULE_EX_PATH',OPENBIZ_APP_PATH.DIRECTORY_SEPARATOR."xmodules");

/* define messages files path */
define('OPENBIZ_APP_MESSAGE_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "messages");


/* define themes const */
define('OPENBIZ_USE_THEME', 1);

define('OPENBIZ_THEME_URL', OPENBIZ_APP_URL . "/themes");
define('OPENBIZ_THEME_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "themes");    // absolution path the themes

define('OPENBIZ_SMARTY_CPL_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files/tpl_cpl");    // smarty template compiling path


/* js lib base, prototype (old) or jquery (new) */
if (DeviceUtil::$PHONE_TOUCH) {
    define('OPENBIZ_JSLIB_BASE', "JQUERY");
} else {
    //define('OPENBIZ_JSLIB_BASE', "JQUERY");
    define('OPENBIZ_JSLIB_BASE', "PROTOTYPE");
}

/* define javascript path */
if (OPENBIZ_JSLIB_BASE == 'JQUERY') {
    define('OPENBIZ_JS_URL', OPENBIZ_APP_URL . "/js/jq");
} else {
    define('OPENBIZ_JS_URL', OPENBIZ_APP_URL . "/js");
}

// define('OTHERS_URL', OPENBIZ_APP_URL . "/others");
/* Log file path */
define("OPENBIZ_LOG_PATH", OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "log");


/* file path. */
/* not used on openbiz framework, but derived from OPENBIZ_OPENBIZ_APP_FILE_PATH */
define('OPENBIZ_APP_FILE_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files");
/* not used on openbiz framework, but derived from OPENBIZ_APP_URL */
define('OPENBIZ_APP_FILE_URL', OPENBIZ_APP_URL . "/files");

if (!defined("OPENBIZ_USE_CUSTOM_SESSION_HANDLER")) {
    define("OPENBIZ_USE_CUSTOM_SESSION_HANDLER", true);
}

/* define session save handler */
if (is_file(OPENBIZ_APP_FILE_PATH . '/install.lock') && defined('OPENBIZ_USE_CUSTOM_SESSION_HANDLER') && OPENBIZ_USE_CUSTOM_SESSION_HANDLER == true) {
    define("OPENBIZ_SESSION_HANDLER", OPENBIZ_APP_MODULE_PATH . "/system/lib/SessionDBHandler"); // save session in DATABASE 
    //define("OPENBIZ_SESSION_HANDLER", OPENBIZ_APP_MODULE_PATH."/system/lib/SessionMCHandler"); // save session in MEMCACHE
    define("OPENBIZ_SESSION_PATH", OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "session"); // for default FILE type session handler
} else {
    define("OPENBIZ_SESSION_PATH", OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "session"); // for default FILE type session handler^M
}

/* resources path. */
// OPENBIZ_RESOURCE_PATH use OPENBIZ_ prefix because the related constants (OPENBIZ_RESOURCE_URL, OPENBIZ_RESOURCE_PHP) under OPENBIZ
define('OPENBIZ_RESOURCE_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "resources");
define('OPENBIZ_RESOURCE_URL', OPENBIZ_APP_URL . "/resources");
define('OPENBIZ_RESOURCE_PHP', OPENBIZ_APP_URL . "/rs.php");

/* secured upload / attachment file path. files cannot be accessed by a direct url */
//define('SECURE_UPLOAD_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "sec_upload");

/* public upload file path. for example, uploaded image files. files can be accessed by a direct url */
define('OPENBIZ_PUBLIC_UPLOAD_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "upload");
define('OPENBIZ_PUBLIC_UPLOAD_URL', OPENBIZ_APP_FILE_URL . '/upload');

/* file cache.DIRECTORY_SEPARATOR."rectory */
define('OPENBIZ_CACHE_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "cache");

/* metadata cache files directory */
define('OPENBIZ_CACHE_METADATA_PATH', OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "files" . DIRECTORY_SEPARATOR . "cache" . DIRECTORY_SEPARATOR . "metadata");


/****************************************************************************
  application system level constances
****************************************************************************/

/* whether print debug infomation or not */
define("OPENBIZ_DEBUG", 0);

/* check whether user logged in */
//define("CHECKUSER", "Y");
/* session timeout seconds */
define("OPENBIZ_TIMEOUT", 86400);  // 86400 = 1 day
//I18n
define('OPENBIZ_DEFAULT_LANGUAGE', 'en_US');
define("OPENBIZ_LANGUAGE_PATH", OPENBIZ_APP_PATH . DIRECTORY_SEPARATOR . "languages");

/* define locale to be set in typemanager.php depending on selected language */
//$local["es"]="es_ES.utf8";
//$local["en"]="en_EN.utf8";

// session timeout page
define('OPENBIZ_USER_TIMEOUT_VIEW', "common.view.TimeoutView");

// access deny page
define('OPENBIZ_ACCESS_DENIED_VIEW', "common.view.AccessDenyView");

// security deny page
define('OPENBIZ_SECURITY_DENIED_VIEW', "common.view.SecurityDenyView");

// not found page
define('OPENBIZ_NOTFOUND_VIEW', "common.view.NotfoundView");

// internal error page
define('OPENBIZ_INTERNAL_ERROR_VIEW', "common.view.ErrorView");

// define service namings
define('OPENBIZ_EVENTLOG_SERVICE', "eventlogService");
define('OPENBIZ_PDF_SERVICE', "pdfService");
define('OPENBIZ_PREFERENCE_SERVICE', "preferenceService");
define('OPENBIZ_DATAPERM_SERVICE', "dataPermService");
define('OPENBIZ_UTIL_SERVICE', "utilService");

define('OPENBIZ_DENY', 0);
define('OPENBIZ_ALLOW', 1);
define('OPENBIZ_ALLOW_OWNER', 2);

define('OPENBIZ_DEFAULT_OWNER_PERM', '3');
define('OPENBIZ_DEFAULT_GROUP_PERM', '1');
define('OPENBIZ_DEFAULT_OTHER_PERM', '0');
