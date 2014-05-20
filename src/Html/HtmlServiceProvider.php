<?php namespace Iyoworks\Html;

class HtmlServiceProvider extends \Illuminate\Html\HtmlServiceProvider {

	protected function registerFormBuilder()
	{
		$this->app->bindShared('form', function ($app)
		{
			$form = new FormBuilder($app['html'], $app['url'], $app['session.store']->getToken());

			return $form->setSessionStore($app['session.store']);
		});

	}

	protected function registerHtmlBuilder()
	{
		$this->app->bindShared('html', function ($app)
		{
			return new HtmlBuilder($app['url']);
		});

		$this->app->bindShared('breadcrumbs', function ($app)
		{
			return new BreadCrumbs($app['html']);
		});
	}

	public function provides()
	{
		return ['html', 'form', 'breadcrumbs'];
	}
} 