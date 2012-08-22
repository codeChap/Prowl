<?php

/**
 * PHP library to access the Prowl service.
 *
 * @author     Derrick Egersdorfer
 * @license    MIT License
 * @copyright  2011 - 2012 Derrick Egersdorfer
 */

namespace Egers/Prowl;

class Prowl
{
    protected $key = false;
    protected $application = false;
    protected $priority = 0;
    protected $subject = false;
    protected $message = false;
    protected $action = false;
    protected $url = "https://api.prowlapp.com/publicapi/add";
    protected $error = false;

    /**
     * The main push method.
     *
     * @param  string    $text     Text to push to device
     * @param  array     $config   Data with api keys etc
     */
    public function push($text, $config)
    {
        print "hello";
    }
}
