<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 15.02.15
 * Time: 15:35
 */

namespace Spc;

class PgCall
{
    /** @var Application */
    protected $app;

    protected $prepared = [];
    protected $conn;

    public function __construct(Application $app)
    {
        $config = $app['config'];
        if (isset($config['db.options'])) {
            $params = $config['db.options'];
        } else {
            $app['db'] = null;
            return;
        }

        if (is_string($params)) {
            $this->conn = pg_connect($params);
        } elseif (is_array($params)) {
            $arr = [];
            foreach ($params as $key => $val) {
                $arr[] = $key . '=' . $val;
            }
            $this->conn = pg_connect(implode(' ', $arr));
        } else {
            $this->conn = $params;
        }
        $app['db'] = $this;
        $this->app = $app;

        /*
        $str = md5(microtime(true));
        if (!$res = $this->test_db($str, $str)) {
            $app->error(_('Ошибка БД'));
        }
        */
    }

    public function __destruct()
    {
        foreach($this->prepared as $stmt) {
            pg_query($this->conn, 'deallocate ' . $stmt);
        }
    }

    public function __call($fname, $fargs)
    {
        $stmt = $fname . '__' . count($fargs);
        if (!in_array($stmt, $this->prepared)) {
            $alist = [];
            for($i = 1; $i <= count($fargs); $i++) {
                $alist[$i] = '$' . $i;
            }
            $sql = 'SELECT * FROM ' . $fname . '(' . implode(',', $alist) . ')';
            $prep = pg_prepare($this->conn, $stmt, $sql);
            $this->prepared[$stmt] = $stmt;
        }

        if ($res = pg_execute($this->conn, $stmt, $fargs)) {
            $rows = pg_num_rows($res);
            $cols = pg_num_fields($res);
            if ($cols > 1) {
                return pg_fetch_assoc($res);
            } elseif ($rows == 0) {
                return null;
            } elseif ($rows == 1) {
                return pg_fetch_result($res, 0);
            }
            return pg_fetch_all_columns($res, 0);
        } else {
            unset($this->prepared[$stmt]);
        }

        return null;
    }
}