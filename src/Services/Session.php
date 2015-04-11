<?php namespace Ganymed\Services;


use SessionHandler;

/*
 * This file is part of the Ganymed Package.
 *
 *  This class is based on Edward Mann's SecureSessionHandler,
 *      https://gist.github.com/eddmann/10262795
 *
 */

class Session extends SessionHandler {

    protected $key;

    protected $cookie;

    public function __construct($config)
    {
        $this->config = $config;
        $this->key = getenv('app_key');
        $this->cookie = [];

        $this->cookie += [
            'lifetime' => $this->config['lifetime'] * 60,
            'path' => $this->config['path'],
            'domain' => $this->config['domain'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true
        ];

        $this->setup();

        $this->start();
    }

    private function setup()
    {
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);

        session_name($this->config['name']);

        session_set_cookie_params(
            $this->cookie['lifetime'],
            $this->cookie['path'],
            $this->cookie['domain'],
            $this->cookie['secure'],
            $this->cookie['httponly']
        );
    }

    public function start()
    {
        if (session_id() === '') {
            if (session_start()) {
                return mt_rand(0, 4) === 0 ? $this->refresh() : true; // 1/5
            }
        }

        return false;
    }

    public function forget()
    {
        if (session_id() === '') {
            return false;
        }

        $_SESSION = [];

        setcookie(
            session_name(),
            '',
            time() - 42000,
            $this->cookie['path'],
            $this->cookie['domain'],
            $this->cookie['secure'],
            $this->cookie['httponly']
        );

        return session_destroy();
    }

    public function refresh()
    {
        return session_regenerate_id(true);
    }

    public function read($id)
    {
        return mcrypt_decrypt(MCRYPT_3DES, $this->key, parent::read($id), MCRYPT_MODE_ECB);
    }

    public function write($id, $data)
    {
        return parent::write($id, mcrypt_encrypt(MCRYPT_3DES, $this->key, $data, MCRYPT_MODE_ECB));
    }

    public function isExpired($ttl = 30)
    {
        $last = isset($_SESSION['_last_activity'])
            ? $_SESSION['_last_activity']
            : false;

        if ($last !== false && time() - $last > $ttl * 60) {
            return true;
        }

        $_SESSION['_last_activity'] = time();

        return false;
    }

    public function isFingerprint()
    {
        $hash = md5($_SERVER['HTTP_USER_AGENT']);

        if (isset($_SESSION['_fingerprint'])) {
            return $_SESSION['_fingerprint'] === $hash;
        }

        $_SESSION['_fingerprint'] = $hash;

        return true;
    }

    public function isValid()
    {
        return !$this->isExpired() && $this->isFingerprint();
    }

    public function has($name)
    {
        return array_key_exists($name, $_SESSION);
    }

    public function get($name)
    {
        return array_key_exists($name, $_SESSION) ? $_SESSION[$name] : null;
    }

    public function put($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function hasErrors()
    {
        return $this->has('errors');
    }

    public function getErrors()
    {
        $errors = unserialize($_SESSION['errors']);
        unset($_SESSION['errors']);
        return $errors;
    }

    public function hasError($errorName)
    {
        $errors = unserialize($_SESSION['errors']);
        return array_key_exists($errorName, $errors);
    }

    public function getError($errorName)
    {
        $errors = unserialize($_SESSION['errors']);
        $error = $errors[$errorName];
        unset($errors[$errorName]);
        $this->put('errors', $errors);
        return $error;
    }

    public function hasFlashMessage()
    {
        return $this->has('flash_message');
    }

    public function getFlashMessage()
    {
        $flashMessage = $this->get('flash_message');
        unset($_SESSION['flash_message']);
        return $flashMessage;
    }

    public function putFlashMessage($message)
    {
        $this->put('flash_message', $message);
    }

}