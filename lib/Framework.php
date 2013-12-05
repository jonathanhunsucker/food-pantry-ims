<?php

define("DEBUG", stripos($_SERVER["HTTP_HOST"], "eta") === false);
define("DEBUG_VIEW", false);
define("PROJECT_ROOT", realpath(dirname(__FILE__) . "/.."));

if (DEBUG) {
    error_reporting(E_ALL ^ E_NOTICE);
    ini_set("display_errors", 1);
}

function dump($item) {
    echo "<pre>";
    ob_start();
    var_dump($item);
    echo htmlspecialchars(ob_get_clean());
    echo "</pre>";
}

function __autoload($name) {
    if (class_exists($name)) return;
    $src_dirs = array("controllers", "lib", "models");
    $class_filename = str_replace("_", "/", $name);
    foreach ($src_dirs as $dir) {
        $filename = PROJECT_ROOT . "/$dir/" . $class_filename . ".php";
        if (file_exists($filename)) {
            include($filename);
            if (method_exists($name, "__init_static")) $name::__init_static();
            return;
        }
    }
    throw new Exception("Class not found $name");
}

function dispatch_controller($name, $method) {
    $name = str_replace("_", DIRECTORY_SEPARATOR, $name);
    if (!class_exists($name)) {
        throw new Exception("No such class $name");
    }
    $controller = new $name();
    
    if (!method_exists($controller, $method)) {
        throw new Exception("No such action $method on $name");
    }

    return $controller->dispatch($method);
}

function get_view_filename() {
    $filename = $_SERVER["SCRIPT_URL"];
    $filename = ltrim($filename, "/");
    if (!$filename) $filename = "index";
    if (strrpos($filename, "/") === strlen($filename)-1) $filename .= "index";
    return PROJECT_ROOT . "/views/" . $filename . ".html";
}

function getControllerAndAction() {
    $uri_bits = explode("/", $_SERVER["SCRIPT_URL"]);
    array_shift($uri_bits);

    $method = array_pop($uri_bits);
    $method = project_fmt($method ? $method : "index") . "Action";

    $name = implode("_", $uri_bits);
    $name = project_fmt($name ? $name : "index", true) . "Controller";
    
    return array($name, $method);
}

function project_fmt($name, $cap_first=false) {
    $name = explode("-", $name);
    return ($cap_first ? "" : array_shift($name)) . implode("", array_map("ucfirst", $name));
}

function flash($message, $type="info") {
    if (!$_SESSION["flash"]) $_SESSION["flash"] = array();
    if (!$_SESSION["flash"][$type]) $_SESSION["flash"][$type] = array();
    $_SESSION["flash"][$type][] = $message;
}

function flash_error($message) {
    return flash($message, "error");
}

function flash_warning($message) {
    return flash($message, "warning");
}

function flash_info($message) {
    return flash($message, "info");
}

function flash_success($message) {
    return flash($message, "success");
}

function flash_end($message) {
    return flash($message, "end");
}

function has_flash($type="info") {
    if (!$_SESSION["flash"]) $_SESSION["flash"] = array();
    return key_exists($type, $_SESSION["flash"]);
}

function get_flash($type="info") {
    if (!$_SESSION["flash"]) $_SESSION["flash"] = array();
    if (has_flash($type)) {
        $flashes = $_SESSION["flash"][$type];
        $_SESSION["flash"][$type] = array();
        return $flashes;
    } else {
        return array();
    }
}

try {
    session_start();
    include_once PROJECT_ROOT . "/config/Config.php";
    
    list($name, $method) = getControllerAndAction();    
    $params = array("config" => (new Config)->view);
    $controller_params = dispatch_controller($name, $method);
    if (is_array($controller_params)) {
        $params = array_merge($params, $controller_params);
    } else {
        $params = $controller_params;
    }
    
    $view_filename = get_view_filename();
    $view = new View($view_filename, $params);
    if (is_array($controller_params) && !DEBUG_VIEW) {
        $view->dispatch();
    } else {
        $view->dispatchDirect();
    }
    
} catch (Exception $e) {
    if (DEBUG) {
        dump(get_class($e) . ": " . $e->getMessage());
        dump($e->getTrace());
    } else {
        echo "<pre>That's an error</pre>";
    }
}
?>
