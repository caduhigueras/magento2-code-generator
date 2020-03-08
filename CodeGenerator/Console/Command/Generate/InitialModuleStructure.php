<?php
namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class InitialModuleStructure
{
    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var UploaderFactory
     */
    protected $fileUploader;

    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var File
     */
    private $_file;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var FileIo
     */
    private $filesystemIo;

    /**
     * FileProcessor constructor.
     * @param ManagerInterface $messageManager
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploader
     * @param ResourceConnection $resource
     * @param File $file
     * @param FileIo $filesystemIo
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
        ResourceConnection $resource,
        File $file,
        FileIo $filesystemIo,
        UrlInterface $urlBuilder
    ) {
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->resource = $resource;
        $this->_file = $file;
        $this->urlBuilder = $urlBuilder;
        $this->filesystemIo = $filesystemIo;
    }

    /**
     * @param $vendorNamespace
     * @param $sequenceFiles
     * @return array
     * @throws FileSystemException
     * @throws \Exception
     */
    public function createInitialModuleStructure($vendorNamespace, $sequenceFiles)
    {
        // get app path
        $result = [];
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $codeFolder = $appFolderPath . 'code';
        //check if code folder already exists, if not create
        try {
            $this->filesystemIo->checkAndCreateFolder($codeFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $moduleDirArr = explode('_',$vendorNamespace);
        try {
            $moduleFolder = $codeFolder . '/' . $moduleDirArr[0] . '/' . $moduleDirArr[1];
            //check if main directory of module already exists
            $this->filesystemIo->checkAndCreateFolder($moduleFolder);
            //set permissions | TODO: test in different install to see if it is necessary and fix all permissions
//            $this->filesystemIo->chmodRecursive($codeFolder . '/' . $moduleDir[0], '777');
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }

        //generate registration.php
        if (!$this->generateRegistrationFile($moduleFolder, $vendorNamespace)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate registration.php';
        }
        //generate module.xml
        if (!$this->generateModuleXmlFile($moduleFolder, $vendorNamespace, $sequenceFiles)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate module.xml';
        }
        //generate composer.json
        if (!$this->generateComposerFile($moduleFolder, $moduleDirArr)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate composer.json';
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * @param $dir
     * @param $vendorNamespace
     * @return bool
     */
    public function generateRegistrationFile($dir, $vendorNamespace)
    {
        $file = $dir . '/' . 'registration.php';
        if (!$this->filesystemIo->fileExists($file)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '    \Magento\Framework\Component\ComponentRegistrar::register(' . PHP_EOL;
            $contents .= '        \Magento\Framework\Component\ComponentRegistrar::MODULE,' . PHP_EOL;
            $contents .= '        "' . $vendorNamespace . '",' . PHP_EOL;
            $contents .= '        __DIR__' . PHP_EOL;
            $contents .= '    );' . PHP_EOL;
            if ($this->filesystemIo->write($file, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $dir
     * @param $vendorNamespace
     * @param $sequenceModules
     * @return bool
     * @throws \Exception
     */
    public function generateModuleXmlFile($dir, $vendorNamespace, $sequenceModules)
    {
        $etcFolder = $this->filesystemIo->checkAndCreateFolder($dir . '/etc');
        $file = $dir . '/etc/module.xml';
        if (!$this->filesystemIo->fileExists($file)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Module/etc/module.xsd">' . PHP_EOL;
            $contents .= '    <module name="' . $vendorNamespace . '" setup_version="0.0.1">' . PHP_EOL;
            if ($sequenceModules) {
                $contents .= '        <sequence>' . PHP_EOL;
                $sequenceModulesArr = explode(',', $sequenceModules);
                foreach ($sequenceModulesArr as $module ) {
                    $contents .= '            <module name="' . $module . '"/>' . PHP_EOL;
                }
                $contents .= '        </sequence>' . PHP_EOL;
            }
            $contents .= '    </module>' . PHP_EOL;
            $contents .= '</config>' . PHP_EOL;
            if ($this->filesystemIo->write($file, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $dir
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateComposerFile($dir, $vendorNamespaceArr)
    {
        $file = $dir . '/' . 'composer.json';
        if (!$this->filesystemIo->fileExists($file)){
            $contents = '{' . PHP_EOL;
            $contents .= '  "name": "' . strtolower($vendorNamespaceArr[0]) . '/' . strtolower($vendorNamespaceArr[1]) . '-magento2",' . PHP_EOL;
            $contents .= '  "description": "Magento 2 module",' . PHP_EOL;
            $contents .= '  "type": "magento2-module",' . PHP_EOL;
            $contents .= '  "version": "0.0.1",' . PHP_EOL;
            $contents .= '  "authors": [' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        "name": "Cadu Higueras",' . PHP_EOL;
            $contents .= '      "email": "cadu@codebaby.tech",' . PHP_EOL;
            $contents .= '      "homepage": "https://codebaby.tech"' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '  ],' . PHP_EOL;
            $contents .= '  "license": [' . PHP_EOL;
            $contents .= '                "OSL-3.0",' . PHP_EOL;
            $contents .= '                "AFL-3.0"' . PHP_EOL;
            $contents .= '            ],' . PHP_EOL;
            $contents .= '  "require": {' . PHP_EOL;
            $contents .= '                "php": "~7.2.0",' . PHP_EOL;
            $contents .= '    "magento/magento-composer-installer": "*",' . PHP_EOL;
            $contents .= '    "magento/framework": "~100.0"' . PHP_EOL;
            $contents .= '  },' . PHP_EOL;
            $contents .= '  "autoload": {' . PHP_EOL;
            $contents .= '    "files": [ "registration.php" ],' . PHP_EOL;
            $contents .= '    "psr-4": {' . PHP_EOL;
            $contents .= '    "' . $vendorNamespaceArr[0] .'\\\\' . $vendorNamespaceArr[1] . '\\\\": ""' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '  }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            if ($this->filesystemIo->write($file, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}