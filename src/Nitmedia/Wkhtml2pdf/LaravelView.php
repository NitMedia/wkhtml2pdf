<?php namespace Nitmedia\Wkhtml2pdf;

/**
 * Class LaravelView
 * @package Nitmedia\Wkhtml2pdf
 */
class LaravelView implements ViewInterface{

    /**
     * @var \Illuminate\View\Environment
     */
    protected $view;

    /**
     * @param Environment $view
     */
    public function __construct($view){
        $this->view = $view;
    }

    /**
     * @param $view
     * @param array $data
     * @return \Illuminate\View\View
     */
    public function make($view, $data=array()){
        return $this->view->make($view, $data);
    }

}
