<?php
/**
 * @author Dolgov_M <dolgov@bk.ru>
 * @date   09.02.2016 16:38
 */

namespace SilexDistribution\Tests;


use SilexDistribution\BootstrapBuilder;

class BootstrapBuilderTest extends \PHPUnit_Framework_TestCase
{
    private $prefix;

    public function __construct($name = null, array $data = array(), $dataName = '') {
        $this->prefix = __DIR__ . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR;
        parent::__construct($name, $data, $dataName);
    }


    public function testParseFile() {
        $builder = new BootstrapBuilder();
        $builder->parseFile($this->prefix . 'test.php');
        $founded = array_flip($builder->getClassList());
        $this->assertArrayHasKey('Fixtures\A', $founded);
        $this->assertArrayNotHasKey('Fixtures\B', $founded);
    }

    public function testLoad() {
        $builder = new BootstrapBuilder();
        $builder
            ->setClasses(array())
            ->parseFile($this->prefix . 'test2.php');
        $file    = sys_get_temp_dir().DIRECTORY_SEPARATOR.'foo.php.cache';
        if (is_file($file)) {
            unlink($file);
        }
        $builder->writeCache($file);
        $this->assertEquals("<?php\n\n".
"namespace Fixtures\n".
"{\n".
"class A {\n".
"}}\n".
"namespace Fixtures\n".
"{\n".
"class B {\n".
"}}"
, file_get_contents($file));
//        unlink($file);
    }
}