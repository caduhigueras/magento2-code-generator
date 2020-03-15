<?php
namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use CodeBaby\CodeGenerator\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class DiXmlStructure
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var UploaderFactory
     */
    private $fileUploader;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var File
     */
    private $file;
    /**
     * @var FileIo
     */
    private $filesystemIo;
    /**
     * @var UrlInterface
     */
    private $urlBuilder;
    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
        ResourceConnection $resource,
        File $file,
        FileIo $filesystemIo,
        UrlInterface $urlBuilder,
        Data $helper
    ) {
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->resource = $resource;
        $this->file = $file;
        $this->filesystemIo = $filesystemIo;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
    }

    public function generateDiXmlFile($vendorNamespaceArr, $dbColumns, $entityName, $dbName)
    {
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $folder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/' . 'etc';
        $diFile = $folder . '/' . 'di.xml';
        if (!$this->filesystemIo->fileExists($diFile)){
            $contents = '<?xml version="1.0"?>' .PHP_EOL;
            $contents .= '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:ObjectManager/etc/config.xsd">' .PHP_EOL;
            $contents .= '    <preference for="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data' . '\\' . $entityName . 'Interface" type="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . '"/>' .PHP_EOL;
            $contents .= '    <preference for="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\ ' . $entityName . 'RepositoryInterface" type="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' .'Model' . '\\' . $entityName . '\\' . $entityName . 'Repository"/>' .PHP_EOL;
            $contents .= '    <type name="Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\CollectionFactory">' .PHP_EOL;
            $contents .= '        <arguments>' .PHP_EOL;
            $contents .= '            <argument name="collections" xsi:type="array">' .PHP_EOL;
            $contents .= '                <item name="' . $this->helper->convertToSnakeCase('$entityName') . '_grid_data_source" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'Grid\Collection</item>' .PHP_EOL;
            $contents .= '            </argument>' .PHP_EOL;
            $contents .= '        </arguments>' .PHP_EOL;
            $contents .= '    </type>' .PHP_EOL;
            $contents .= '    <!-- Necessary to enable saving through the CustomFormRepository-->' .PHP_EOL;
            $contents .= '    <type name="Magento\\Framework\\Model\\Entity\\RepositoryFactory">' .PHP_EOL;
            $contents .= '        <arguments>' .PHP_EOL;
            $contents .= '            <argument name="entities" xsi:type="array">' .PHP_EOL;
            $contents .= '                <item name="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data' . '\\' . $entityName . 'Interface" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . $entityName . 'RepositoryInterface</item>' .PHP_EOL;
            $contents .= '            </argument>' .PHP_EOL;
            $contents .= '        </arguments>' .PHP_EOL;
            $contents .= '    </type>' .PHP_EOL;
            $contents .= '    <type name="Magento\\Framework\\EntityManager\\MetadataPool">' .PHP_EOL;
            $contents .= '        <arguments>' .PHP_EOL;
            $contents .= '            <argument name="metadata" xsi:type="array">' .PHP_EOL;
            $contents .= '                <item name="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data' . '\\' . $entityName . 'Interface" xsi:type="array">' .PHP_EOL;
            $contents .= '                    <item name="entityTableName" xsi:type="string">' . $dbName . '</item>' .PHP_EOL;
            $contents .= '                    <item name="identifierField" xsi:type="string">id</item>' .PHP_EOL;
            $contents .= '                </item>' .PHP_EOL;
            $contents .= '            </argument>' .PHP_EOL;
            $contents .= '        </arguments>' .PHP_EOL;
            $contents .= '    </type>' .PHP_EOL;
            $contents .= '</config>' .PHP_EOL;
            if ($this->filesystemIo->write($diFile, $contents)) {
                $result['success'] = true;
                return $result;
            }
            $result['success'] = false;
            $result['message'] = 'Error generating di.xml file';
            return $result;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}