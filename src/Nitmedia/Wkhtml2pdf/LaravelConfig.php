<?php namespace Nitmedia\Wkhtml2pdf;

use Illuminate\Config\Repository;

/**
 * Class LaravelConfig
 * @package Nitmedia\Wkhtml2pdf
 */
class LaravelConfig implements ConfigInterface{

    public $config;
    public $version;


    public function __construct(Repository $config){
        $this->config = $config;
        
        $app = app();
        $this->version = substr($app::VERSION, 0, 1);
    }

    /**
     * Get Laravel config
     *
     * @param $key
     * @return mixed
     */
    public function get($key){
        if ($this->version == '4') {
            return $this->config->get('Wkhtml2pdf::' . $key);
        }
        
        return $this->config->get('Wkhtml2pdf.' . $key);
    }

}
