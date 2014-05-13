<?php namespace Nitmedia\Wkhtml2pdf;

use Illuminate\Config\Repository;

/**
 * Class LaravelConfig
 * @package Nitmedia\Wkhtml2pdf
 */
class LaravelConfig implements ConfigInterface{

    public $config;

    public function __construct(Repository $config){
        $this->config = $config;
    }

    /**
     * Get Laravel config
     *
     * @param $key
     * @return mixed
     */
    public function get($key){
        return $this->config->get($key);
    }

}