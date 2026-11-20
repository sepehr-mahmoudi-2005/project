<?php

session_start();

//
// config
const BASE_PATH = __DIR__;
define('BASE_URL', currentDomain() . '/project/');
const DISPLAY_ERROR = true;
const DB_HOST = 'localhost';
const DB_NAME = 'project';
const DB_USERNAME = 'root';
const DB_PASSWORD = '';

// display errors
displayError(DISPLAY_ERROR);


// reserve addresses
uri('admin/category/{id}', 'Category', 'index');
uri('admin/category/store', 'Category', 'store', 'POST');

//
// helpers

function protocol()
{
    return stripos($_SERVER['SERVER_PROTOCOL'], 'https')
        ? 'https://'
        : 'http://';
}

function currentDomain()
{
    return protocol() . $_SERVER['HTTP_HOST'];
}

function dd(mixed $data)
{
    echo '<pre>';
    var_dump($data);
    echo '</pre>';
    die;
}

function asset(string $src)
{
    $domain = rtrim(BASE_URL, '/ ');
    $src = trim($src, '/ ');
    return $domain . '/' . $src;
}

function url(string $url)
{
    $domain = rtrim(BASE_URL, '/ ');
    $url = trim($url, '/ ');
    return $domain . '/' . $url;
}

function currentUrl()
{
    return currentDomain() . $_SERVER['REQUEST_URI'];
}

function requestMethod()
{
    return $_SERVER['REQUEST_METHOD'];
}

function displayError(bool $display)
{
    if ($display) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    } else {
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        error_reporting(0);
    }
}

const FLASH = 'flash_message';
global $flashMessage;
if (isset($_SESSION[FLASH])) {
    $flashMessage = $_SESSION[FLASH];
    unset($_SESSION[FLASH]);
}

function flash(string $name, ?string $message = null)
{
    if ($message === null) {
        global $flashMessage;
        return isset($flashMessage[$name])
            ? $flashMessage[$name]
            : '';
    } else {
        $_SESSION[FLASH][$name] = $message;
    }
}

function uri(
    string $reservedUrl,
    string $class,
    string $method,
    string $requestMethod = 'GET'
) {
    if (requestMethod() !== $requestMethod)
        return;

    $currentUrl = explode('?', currentUrl())[0];
    $currentUrl = str_replace(BASE_URL, '', $currentUrl);
    $currentUrl = trim($currentUrl, '/ ');

    $currentUrlArray = explode('/', $currentUrl);
    $currentUrlArray = array_filter($currentUrlArray);
    $currentUrlArray = array_values($currentUrlArray);

    $reservedUrl = trim($reservedUrl, '/ ');

    $reservedUrlArray = explode('/', $reservedUrl);
    $reservedUrlArray = array_filter($reservedUrlArray);
    $reservedUrlArray = array_values($reservedUrlArray);


    if (
        count($currentUrlArray) !== count($reservedUrlArray)
    ) {
        return;
    }

    $parameters = [];
    for ($i = 0; $i < count($reservedUrlArray); $i++) {
        if (preg_match('/{.+}/i', $reservedUrlArray[$i], $matches)) {
            $parameters[$matches[0]] = $currentUrlArray[$i];
        } elseif ($reservedUrlArray[$i] !== $currentUrlArray[$i]) {
            return;
        }
    }

    if ($requestMethod === 'POST') {
        $request = isset($_FILES)
            ? array_merge($_POST, $_FILES)
            : $_POST;

        $parameters = array_merge([$request], $parameters);
    }

    $object = new $class;
    call_user_func_array([$object, $method], $parameters);
    die;
}
