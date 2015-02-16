<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 14.02.15
 * Time: 14:17
 */

namespace Spc;

class Application implements \ArrayAccess
{
    protected $isError = false;

    protected $di = [];
    protected $params = [];
    protected $routes = [];
    protected $spaces = [];

    public function __construct(array $values = array())
    {
        $this['logger'] = null;
        $this['debug'] = false;
        $this['charset'] = 'UTF-8';
        $this['locale'] = 'ru';

        $this['route'] = '/';

        $this['layout'] = 'default.phtml';
        $this['script'] = 'index.phtml';
        $this['view'] = new \stdClass();

        foreach ($values as $key => $value) {
            $this[$key] = $value;
        }

        $this->match('/', []);
        $this->setSpace('title', _('Test Login'))
            ->setSpace('footer', '&copy; SPC, 2015');
    }

    /**
     * @param string $url
     * @param mixed $handler
     * @return $this
     */
    public function match($url, $handler)
    {
        $this->routes[strtolower($url)] = $handler;

        return $this;
    }

    /**
     * @param string $text
     * @return string
     */
    public function escape($text)
    {
        return htmlspecialchars($text, ENT_COMPAT, $this['charset'], true);
    }

    /**
     * @param string $message
     * @param string $level
     * @return $this
     */
    public function log($message, $level = \Psr\Log\LogLevel::INFO)
    {
        if ($logger = $this['logger']) {
            $logger->log($level, $message, []);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest';
    }

    /**
     * @param object $view
     * @return \Closure
     */
    public function getRender($view)
    {
        $path = APP_DIR . DIRECTORY_SEPARATOR;
        $render = function ($script) use ($path) {
            include $path . trim($script, '/');
        };

        return $render->bindTo($view, get_class($view));
    }

    /**
     * @param string $name
     * @param string $content
     * @return $this
     */
    public function setSpace($name, $content)
    {
        $this->spaces[$name] = $content;

        return $this;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getSpace($name)
    {
        return isset($this->spaces[$name]) ? $this->spaces[$name] : null;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function eraseSpace($name)
    {
        unset($this->spaces[$name]);

        return $this;
    }

    /**
     * @param string $script
     * @param mixed|null $view
     * @param array $params
     * @return string
     */
    public function render($script, $view = null, array $params = [])
    {
        if ($view === null) {
            $view = $this['view'];
        }
        $params['app'] = $this;

        if (method_exists($view, 'render')) {
            return $view->render($script, $params);
        }

        foreach ($params as $key => $val) {
            $view->$key = $val;
        }

        $render = $this->getRender($view);
        ob_start();
        $render($script);
        return ob_get_clean();
    }

    /**
     * @param string $content
     * @return $this
     */
    public function sendResponse($content)
    {
        $layout = $this['layout'];
        if (is_callable($layout)) {
            echo $layout($content);
            return $this;
        }

        $params = [
            'content' => $content,
        ];
        foreach ($this->spaces as $name => $space) {
            $params[$name] = is_callable($space) ? $space($name) : $space;
        }
        echo $this->render(
            'layout/' . trim($layout, '/'),
            new \stdClass(),
            $params
        );

        return $this;
    }

    /**
     * @param array|object $data
     */
    public function jsonResponse($data)
    {
        ob_end_clean();
        http_response_code(200);
        header('Content-type: application/json', true);
        echo json_encode($data);
        exit;
    }

    /**
     * @param string $url
     */
    public function redirect($url)
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * @return $this
     */
    public function run()
    {
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->params = $_POST;
        } else {
            $this->params = $_GET;
        }
        $route = strtolower(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
        $this['route'] = $route;

        if (!array_key_exists($route, $this->routes)) {
            $this->error(_('Ошибка 404'));
            return $this;
        }

        $script = $route;
        if (substr($script, strlen($script) - 1, 1) == '/') {
            $script .= 'index';
        }
        $this['script'] = 'views' . $script . '.phtml';

        ob_start();
        if (is_callable($this->routes[$route])) {
            $result = call_user_func($this->routes[$route], $this->params);
            if ($this->isError) {
                return $this;
            }
        } else {
            $result = $this->routes[$route];
        }
        $output = ob_get_clean();

        if (is_array($result)) {
            $script = $this['script'];
            if (file_exists(realpath(APP_DIR . DIRECTORY_SEPARATOR . trim($script, '/')))) {
                $output .= $this->render($script, null, $result);
            } else {
                $this->error(_('Ошибка 404'));
                return $this;
            }
        } else {
            $output .= $result;
        }

        $this->sendResponse($output);

        return $this;
    }

    /**
     * @param string|null $message
     * @return $this
     */
    public function error($message = null)
    {
        if (!$message) {
            $message = _('Неизвестная ошибка');
        }
        if ($this->isAjax()) {
            $this->jsonResponse(['success' => 0, 'message' => $message]);
        }
        ob_end_clean();
        $this->sendResponse(
            $this->render(
                'views/error.phtml',
                $this['view'],
                ['message' => $message]
            )
        );
        $this->isError = true;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getIdentity()
    {
        $auth = $this['auth'];
        if ($auth && is_callable([$auth, 'getIdentity'])) {
            return $auth->getIdentity();
        }

        return null;
        // or
        // return $auth;
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->di);
    }

    public function offsetGet($offset)
    {
        if (array_key_exists($offset, $this->di)) {
            return $this->di[$offset];
        }

        return null;
    }

    public function offsetSet($offset, $value)
    {
        $this->di[$offset] = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->di[$offset]);
    }
}