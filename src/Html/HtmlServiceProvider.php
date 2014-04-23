<?php namespace Iyoworks\Html;

use Iyoworks\Html\Forms\Form;
use Iyoworks\Html\Forms\LaravelFormRenderer;

class HtmlServiceProvider extends \Illuminate\Html\HtmlServiceProvider {
    public function boot()
    {
        Form::setFieldRenderer($this->app['form.renderer']);
    }


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

    /**
     * Register the form builder instance.
     *
     * @return void
     */
    protected function registerFormBuilder()
    {
        $this->app->bindShared('form', function($app)
        {
            $form = new FormBuilder($app['form.renderer'], $app['html'], $app['url'],
                $app['session.store']->getToken());

            return $form->setSessionStore($app['session.store']);
        });

        $this->app->bindShared('form.renderer', function($app)
        {
            return new LaravelFormRenderer($app['session']->driver(), $app['events']);
        });

    }

    public function provides()
    {
        $provides = parent::provides();
        $provides[] = 'breadcrumbs';
        return $provides;
    }


} 