<?php
session_start();
spl_autoload_register(function ($classes) {
    require_once 'classes/' . $classes . '.php';
});
