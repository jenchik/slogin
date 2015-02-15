<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 15.02.15
 * Time: 11:20
 */

namespace Spc;

class Auth
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
        $app['auth'] = $this;
        session_start();
    }

    /**
     * @param string $login
     * @param string $password
     * @param bool $remember_me
     * @return bool
     */
    public function authenticate($login, $password, $remember_me = false)
    {
        $ipClient = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'x';
        /** @var PgCall $db */
        $db = $this->app['db'];
        $res = $db->authenticate($login, $password);

        if ((bool) $res) {
            session_regenerate_id();
            if ($remember_me === true) {
                $params = session_get_cookie_params();
                setcookie(session_name(), session_id(), time() + 60*60*24*30, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            $_SESSION['id'] = $res['id'];
            $_SESSION['username'] = $res['name'];

            $this->app->log(sprintf('Entered user: [%s, %s] %s', $res['id'], $ipClient, $res['name']));
            return true;
        }
        session_destroy();
        unset($_SESSION['id']);

        $this->app->log(sprintf('Login fail: [%s] %s', $ipClient, $login));

        return false;
    }

    /**
     * @return int|null
     */
    public function getIdentity()
    {
        if (isset($_SESSION['id']) && $_SESSION['id']) {
            return $_SESSION['id'];
        }

        return null;
    }
}