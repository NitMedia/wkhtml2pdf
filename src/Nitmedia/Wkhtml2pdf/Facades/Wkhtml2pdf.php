<?php namespace Nitmedia\Wkhtml2pdf\Facades;

use Illuminate\Support\Facades\Facade;

class Wkhtml2pdf extends Facade {

    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'wkhtml2pdf'; }
}