<?php
namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use CodeBaby\CodeGenerator\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class DbSchemaStructure
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
    private Data $helper;

    /**
     * FileProcessor constructor.
     * @param ManagerInterface $messageManager
     * @param Filesystem $filesystem
     * @param UploaderFactory $fileUploader
     * @param ResourceConnection $resource
     * @param File $file
     * @param FileIo $filesystemIo
     * @param UrlInterface $urlBuilder
     * @param Data $helper
     */
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
        $this->_file = $file;
        $this->urlBuilder = $urlBuilder;
        $this->filesystemIo = $filesystemIo;
        $this->helper = $helper;
    }

    public function generateDbSchemaXmlFile($vendorNamespace, $tableName, $columns)
    {
        // get app path
        $result = [];
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $vendorNamespaceArr = explode('_', $vendorNamespace);
        $moduleFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc';
        //check if code folder already exists, if not create
        try {
            $this->filesystemIo->checkAndCreateFolder($moduleFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        if ($this->generateDbFile($moduleFolder,$vendorNamespaceArr, $tableName, $columns)) {
            $result['success'] = true;
        } else {
            $result['success'] = false;
            $result['message'] = 'Could not create db_schema.xml';
        }
        return $result;
    }

    public function generateDbFile($dir,$vendorNamespaceArr, $tableName, $columns)
    {
        $file = $dir . '/' . 'db_schema.xml';
        if (!$this->filesystemIo->fileExists($file)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= $this->helper->getXmlSignature('db_schema.xml');
            $contents .= '<schema xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:Setup/Declaration/Schema/etc/schema.xsd">' . PHP_EOL;
            $contents .= '    <table name="' . $tableName . '" resource="default" engine="innodb" charset="utf8" comment="Auto generated Table">' . PHP_EOL;
            $contents .= '        <column xsi:type="int" name="id" padding="10" unsigned="true" nullable="false" comment="Entity Id" identity="true" />' . PHP_EOL;
            $contents .= '        <column xsi:type="int" name="store_id" padding="11" nullable="true" />' . PHP_EOL;
            foreach ($columns as $column) {
                $type = $column['type'];
                $columnStr = '        <column xsi:type="' . $type . '" ';
                unset($column['type']);
                unset($column['backend_type']);
                unset($column['backend_label']);
                unset($column['backend_grid']);
                unset($column['backend_options']);
                unset($column['backend_fieldset']);
                unset($column['backend_dynamic_rows']);

                foreach ($column as $key => $value ) {
                    $columnStr .= $key . '="' . $value . '" ';
                }
                $columnStr .= '/>';
                $contents .= $columnStr . PHP_EOL;
            }
            $contents .= '        <!-- Define Primary Key -->' . PHP_EOL;
            $contents .= '        <constraint xsi:type="primary" referenceId="PRIMARY">' . PHP_EOL;
            $contents .= '            <column name="id" />' . PHP_EOL;
            $contents .= '        </constraint>' . PHP_EOL;
            //$contents .= '        <constraint xsi:type="foreign" referenceId="ONEDIRECT_PRODUCT_UPLOADS_RELATED_PRODUCT_CATALOG_PRODUCT_ENTITY_ENTITY_ID" table="onedirect_product_uploads" column="related_product" referenceTable="catalog_product_entity" referenceColumn="entity_id" onDelete="CASCADE"/>' . PHP_EOL;
            $contents .= '    </table>' . PHP_EOL;
            $contents .= '</schema>' . PHP_EOL;
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
