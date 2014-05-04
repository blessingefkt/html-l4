<?php namespace Iyoworks\Html;

use Iyoworks\Html\Forms\Form;
use Iyoworks\Html\Forms\LaravelFormRenderer;

class HtmlServiceProvider extends \Illuminate\Html\HtmlServiceProvider {


    /**
     * Register the HTML builder instance.
     *
     * @return void
     */
    protected function registerHtmlBuilder()
    {
        $this->app->bindShared('html', function($app)
        {
            return new HtmlBuilder($app['url']);
        });

        $this->app->bindShared('breadcrumbs', function($app)
        {
            return new BreadCrumbs($app['html']);
        });
    }

    public function provides()
    {
        $provides = parent::provides();
        $provides[] = 'breadcrumbs';
        return $provides;
    }


} 