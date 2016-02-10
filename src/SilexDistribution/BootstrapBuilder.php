<?php
/**
 * @author Dolgov_M <dolgov@bk.ru>
 * @date   09.02.2016 15:45
 *
 * Class based on Sensio\Bundle\DistributionBundle\Composer\ScriptHandler::buildBootstrap  method
 * @see \Sensio\Bundle\DistributionBundle\Composer\ScriptHandler::doBuildBootstrap
 */

namespace SilexDistribution;


use Symfony\Component\ClassLoader\ClassCollectionLoader;

class BootstrapBuilder {

    const MATCHED_DATA = 1;

    protected $classes = array(
        // symfony
        'Symfony\\Component\\HttpFoundation\\ParameterBag',
        'Symfony\\Component\\HttpFoundation\\HeaderBag',
        'Symfony\\Component\\HttpFoundation\\FileBag',
        'Symfony\\Component\\HttpFoundation\\ServerBag',
        'Symfony\\Component\\HttpFoundation\\Request',
        'Symfony\\Component\\HttpFoundation\\Response',
        'Symfony\\Component\\HttpFoundation\\ResponseHeaderBag',
        'Symfony\\Component\\HttpKernel\\Kernel',

        // silex
        'Silex\\Application',
        'Symfony\\Component\\EventDispatcher\\EventDispatcherInterface',
        'Symfony\\Component\\HttpFoundation\\BinaryFileResponse',
        'Symfony\\Component\\HttpKernel\\HttpKernel',
        'Symfony\\Component\\HttpKernel\\HttpKernelInterface',
        'Symfony\\Component\\HttpKernel\\TerminableInterface',
        'Symfony\\Component\\HttpKernel\\Event\\FilterResponseEvent',
        'Symfony\\Component\\HttpKernel\\Event\\GetResponseEvent',
        'Symfony\\Component\\HttpKernel\\Event\\PostResponseEvent',
        'Symfony\\Component\\HttpKernel\\EventListener\\ResponseListener',
        'Symfony\\Component\\HttpKernel\\EventListener\\RouterListener',
        'Symfony\\Component\\HttpKernel\\Exception\\HttpException',
        'Symfony\\Component\\HttpKernel\\KernelEvents',
        'Symfony\\Component\\HttpFoundation\\Request',
        'Symfony\\Component\\Routing\\RouteCollection',
        'Silex\\EventListener\\LocaleListener',
        'Silex\\EventListener\\MiddlewareListener',
        'Silex\\EventListener\\ConverterListener',
        'Silex\\EventListener\\StringToResponseListener',
    );

    protected $cacheRegexp = "/{{startCache}}(.*?){{stopCache}}/s";
    protected $classRegexp = "/new\\s([\\\\a-zA-Z]*)\\(/";
    protected $ignoreClass = array();

    private $hasTokenGetAllFunction = false;
    private $phpHeaderOpenTagLength;

    /**
     * BootstrapBuilder constructor.
     */
    public function __construct() {
        $this->phpHeaderOpenTagLength = strlen('<?php ');
        $this->hasTokenGetAllFunction = function_exists('token_get_all');
    }


    /**
     * Add fileList to parse
     *
     * @param array $fileList
     * @return $this
     */
    public function parseFileList(array $fileList) {
        foreach ($fileList as $file) {
            $this->parseFile($file);
        }

        return $this;
    }

    /**
     * Parse file and search class name between start and stop tags (in default {{startCache}} and {{stopCache}} )
     * Founded class will be dumped in *.php.cache file
     *
     * @param  string $filePath real path to file
     * @return $this
     */
    public function parseFile($filePath) {
        $content = file_get_contents($filePath);
        $comment = array(T_COMMENT, T_DOC_COMMENT);
        if(preg_match_all($this->cacheRegexp, $content, $res) > 0) {
            foreach ($res[self::MATCHED_DATA] as $string) {
                if($this->hasTokenGetAllFunction) {
                    // search and remove comment
                    $tokens = token_get_all("<?php\n".$string);
                    $string = '';
                    foreach ($tokens as $token) {
                        if(is_array($token)){
                            if(in_array($token[0],$comment)) {
                                continue;
                            }
                            $string .= $token[1];
                        } else {
                            $string .= $token;
                        }
                    }
                }
                if(preg_match_all($this->classRegexp, $string, $classesRaw) > 0) {
                   $this->addClassList($classesRaw[self::MATCHED_DATA]);
                }
            }

        }
        return $this;
    }

    /**
     * @param string $cacheRegexp
     * @return $this
     */
    public function setCacheRegexp($cacheRegexp) {
        $this->cacheRegexp = $cacheRegexp;
        return $this;
    }

    /**
     * @param string $classRegexp
     * @return $this
     */
    public function setClassRegexp($classRegexp) {
        $this->classRegexp = $classRegexp;
        return $this;
    }

    /**
     * @param array $classList
     * @return $this
     */
    public function addClassList(array $classList) {
        $this->classes = array_merge($this->classes, $classList);
        return $this;
    }

    /**
     * @param array $ignoreClass
     * @return $this
     */
    public function setIgnoreClass(array $ignoreClass) {
        $this->ignoreClass = $ignoreClass;
        return $this;
    }

    /**
     * @return array
     */
    public function getClassList() {
        return array_diff($this->classes, $this->ignoreClass);
    }

    /**
     * @param array $classes
     * @return $this
     */
    public function setClasses($classes) {
        $this->classes = $classes;
        return $this;
    }


    public function writeCache($file) {
        ClassCollectionLoader::load($this->getClassList(), dirname($file), basename($file, '.php.cache'), false, false, '.php.cache');

        $bootstrapContent = substr(file_get_contents($file), $this->phpHeaderOpenTagLength);

        file_put_contents($file, sprintf("<?php\n%s", $bootstrapContent));
    }
}