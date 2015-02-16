<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 15.02.15
 * Time: 11:34
 */

namespace Spc;

class Controller
{
    /** @var Application */
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;

        $app->match('/', function () use ($app) {
            if ($app->getIdentity()) {
                $app->redirect('/main');
            }

            return [];
        });

        $app->match('/login', function ($params) use ($app) {
            if (!$app->isAjax()) {
                $app->redirect('/');
            }

            $error = ['success' => 0, 'message' => _('Ошибка аутентификации')];
            if (!$form = Form::getForm($params, 'form-login')) {
                $app->jsonResponse($error);
            }
            if (!$form->validate(['login', 'password'])) {
                $app->jsonResponse($error + [
                        'errors' => [$form->getName() => $form->getLastErrors()]
                    ]);
            }

            /** @var Auth $auth */
            $auth = $app['auth'];
            $data = $form->getData();
            if ($auth && $auth->authenticate($data['login'], $data['password'], isset($data['remember_me']))) {
                $app->jsonResponse(['success' => 1, 'redirect' => '/main']);
            }
            $app->jsonResponse(['success' => 0, 'message' => _('Неправильный логин или пароль')]);
        });

        $app->match('/logout', function () use ($app) {
            session_destroy();
            unset($_SESSION);

            if ($app->isAjax()) {
                $app->jsonResponse(['success' => 1, 'redirect' => '/']);
            }
            $app->redirect('/');
        });

        $app->match('/main', function ($params) use ($app) {
            $this->preDispatch($params);
            $app['script'] = 'views/home.phtml';

            return [];
        });

        $app->match('/order/example', function ($params) use ($app) {
            $this->preDispatch($params);

            return [];
        });

        $app->match('/order/example2', function ($params) use ($app) {
            $this->preDispatch($params);

            return _('Пример без вьюшки');
        });

        $app->match('/other', function ($params) use ($app) {
            $this->preDispatch($params);
            $app->error(_('Ошибка! Пример прерывания и вывода ошибки.'));

            return [];
        });

        $app->match('/other2', function ($params) use ($app) {
            $this->preDispatch($params);

            return [];
        });

        $app->match('/reg', [$this, 'registerAction']);
    }

    /**
     * @param mixed $params
     * @return $this
     */
    public function preDispatch($params)
    {
        $app = $this->app;
        if (!$app->getIdentity()) {
            if ($app->isAjax()) {
                $app->jsonResponse(['success' => 1, 'redirect' => '/']);
            }
            $app->redirect('/');
        }
        $app['layout'] = 'main.phtml';
        $app->setSpace('title', $_SESSION['username'] . ' - ' . $app->getSpace('title'));
        $app->setSpace('menu', function () use ($app) {
            return $app->render('layout/menu.phtml', new \stdClass(), [
                'user_name' => $_SESSION['username'],
            ]);
        });

        return $this;
    }

    /**
     * @param mixed $params
     */
    public function registerAction($params)
    {
        $app = $this->app;
        if (!$app->isAjax()) {
            $app->redirect('/');
        }

        $error = ['success' => 0, 'message' => _('Ошибка регистрации')];
        if (!$form = Form::getForm($params, 'form-reg')) {
            $app->jsonResponse($error);
        }
        if (!$form->validate(['login', 'password', 'confirm', 'email'])) {
            $app->jsonResponse($error + [
                    'errors' => [$form->getName() => $form->getLastErrors()]
                ]);
        }
        $data = $form->getData();

        /** @var PgCall $db */
        $db = $this->app['db'];
        if ((bool) $db->get_user_by_login($data['login'])) {
            $app->jsonResponse($error + [
                    'errors' => [$form->getName() => ['login' => [_('Укажите другой логин')]]]
                ]);
        }

        if ($data['password'] !== $data['confirm']) {
            $app->jsonResponse($error + [
                    'errors' => [$form->getName() => ['confirm' => [_('Пароли должны совпадать')]]]
                ]);
        }

        if (!$data['name']) {
            $data['name'] = $data['login'];
        }
        $db->registered($data['login'], $data['password'], $data['name'], $data['email']);

        /** @var Auth $auth */
        $auth = $app['auth'];
        if ($auth && $auth->authenticate($data['login'], $data['password'])) {
            $app->jsonResponse(['success' => 1, 'redirect' => '/main']);
        }
        $app->jsonResponse($error);
    }
}