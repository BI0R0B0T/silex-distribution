<?php
/**
 * @author Dolgov_M <dolgov@bk.ru>
 * @date   09.02.2016 16:52
 *
 *
 * @var $loader \Composer\Autoload\ClassLoader
 */
$loader = require(dirname(dirname(dirname(__DIR__))).DIRECTORY_SEPARATOR.'vendor/autoload.php');
$loader->setPsr4('Fixtures\\', array(__DIR__.DIRECTORY_SEPARATOR.'Fixtures'));
return $loader;