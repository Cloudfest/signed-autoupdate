<?php

class SignedAutoUpdate_MiniTemplate
{
    /**
     * @var
     */
    protected $template;
    /**
     * @var array
     */
    protected $data = array();
    /**
     * SignedAutoUpdate_MiniTemplate constructor.
     *
     * @param string $path
     * @throws Exception
     */
    public function __construct($path) {
        $this->template = $path;
        if  (!file_exists($this->template)) {
            throw new Exception('could not load template path: ' . $this->template);
        }
    }

    /**
     *
     */
    public function render()
    {
        ob_start();
        include $this->template;
        $string = ob_get_clean();
        return $string;
    }

    /**
     * @param $key
     * @return string
     */
    public function __get($key)
    {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        throw new Exception('missing key: ' .$key);
        return 'undefined';
    }

    /**
     * @param $key
     * @param $value
     */
    public function __set($key, $value) {
        $this->data[$key] = $value;
    }

    /**
     *
     */
    public function __toString()
    {
        return $this->render();
    }
}