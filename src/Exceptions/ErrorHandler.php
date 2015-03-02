<?php namespace Ganymed\Exceptions;


use Ganymed\Services\View;

class ErrorHandler {

    public static function checkForFatal()
    {
        $error = error_get_last();
        if ($error['type'] == E_ERROR)
            self::logError($error["type"], $error["message"], $error["file"], $error["line"]);
                        
    }
    
    public static function logError($num, $str, $file, $line, $context = null)
    {
        self::logException( new \ErrorException($str, 0, $num, $file, $line));
    }

    public static function logException(\Exception $exception)
    {
        $view = new View(__DIR__ . '/views/');
        echo $view->withTemplate('error')->withData(compact('exception'))->render();
    }
    
}