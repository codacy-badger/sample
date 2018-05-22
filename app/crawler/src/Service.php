<?php //-->
/**
 * This file is part of the Salaaap Project.
 * (c) 2016-2018 Openovate Labs
 *
 * Copyright and license information can be found at LICENSE.txt
 * distributed with this package.
 */

namespace Cradle\App\Crawler;

use Cradle\Framework\App;
use Cradle\Sql\SqlFactory;

/**
 * Methods given the app that will return 3rd party services
 *
 * @vendor   Salaaap
 * @package  Component
 * @author   Christian Blanquera <cblanquera@openovate.com>
 * @standard PSR-2
 */
class Service
{
    /**
     * @var App $app
     */
    protected $app = null;

    /**
     * Registers the app for use
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Returns main database object
     *
     * @return object
     */
    public function database()
    {
        return SqlFactory::load($this->custom('sql-crawler'));
    }

    /**
     * Returns a component model
     *
     * @return object
     */
    public function model($name)
    {
        return $this->app->package('/app/crawler')->model($name);
    }

    /**
     * Returns a custom service
     *
     * @return object
     */
    public function custom($key)
    {
        return $this->app->package('global')->service($key);
    }
}
