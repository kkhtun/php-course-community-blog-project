<?php

class Helper
{
    public static function redirect($page)
    {
        header("location: $page");
        die();
    }

    public static function makeSlug($str)
    {
        $str = strtolower(trim($str));
        $str = str_replace(' ', '-', $str);
        return $str . '-' . time();
    }

    public static function filter($str)
    {
        $str = trim($str);
        $str = stripslashes($str);
        $str = strip_tags($str);
        $str = htmlspecialchars($str);
        return $str;
    }

    public static function echoArr($arr)
    {
        echo "<pre style='color: red;'>";
        print_r($arr);
        die();
    }

    public static function checkAllowedType($filename, $allowedTypes = ['jpg', 'png', 'gif', 'jpeg'])
    {
        $fileType = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return true ? in_array($fileType, $allowedTypes) : false;
    }

    public static function removeFile($path)
    {
        return file_exists($path) ? unlink($path) : false;
    }
}
