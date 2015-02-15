<?php
/**
 * Created by PhpStorm.
 * User: evgenii
 * Date: 15.02.15
 * Time: 12:33
 */

namespace Spc;

class Form
{
    protected $data = [];
    protected $errors = [];
    protected $name;

    /**
     * @param array $params
     * @param string $name
     * @return null|Form
     */
    public static function getForm($params, $name)
    {
        if (!isset($params[$name]) || !is_array($params[$name])) {
            return null;
        }

        return new static($params[$name], $name);
    }

    protected function __construct(array $data, $name)
    {
        $this->data = $data;
        $this->name = $name;
    }

    /**
     * @param array $fields
     * @return bool
     */
    public function validate(array $fields)
    {
        $result = true;
        $this->errors = [];
        foreach ($fields as $field) {
            if (!isset($this->data[$field]) || !$this->data[$field]) {
                $result = false;
                $this->errors[$field][] = _('Данное поле обязательно для заполнения');
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getLastErrors()
    {
        return $this->errors;
    }
}