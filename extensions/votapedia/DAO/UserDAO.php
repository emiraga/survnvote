<?php
if (!defined('MEDIAWIKI')) die();

/**
 * This page includes classes which are used to access objects of Survey.
 *
 * @package DataAccessObject
 * @author Emir Habul <emiraga@gmail.com>
 */
class UserDAO
{
    /** @var String */ protected $name;
    public function __construct($name)
    {
        $this->name = $name;
    }
}
