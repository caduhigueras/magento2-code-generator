<?php
namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use CodeBaby\CodeGenerator\Helper\Data;

class ApiAndModelStructure
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

    public function generateApiAndModelFiles($vendorNamespace, $dbColumns, $entityName, $dbName)
    {
        $result = [];
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $vendorNamespaceArr = explode('_', $vendorNamespace);
        $moduleFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api/Data';
        try {
            $this->filesystemIo->checkAndCreateFolder($moduleFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        if ($this->generateApiRepositoryFile($appFolderPath, $vendorNamespaceArr, $entityName))
        {
            if ($this->generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName)) {
                return $dbColumns;
            }
        }
        return false;
    }

    public function generateApiRepositoryFile($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $apiRepositoryFile = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api' . '/' . $entityName . 'RepositoryInterface.php';
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($entityName);
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\LocalizedException;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Api\\Data\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'interface ' . $entityName . 'RepositoryInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface[]' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getList();' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     * @throws LocalizedException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getById($id);' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . '' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function save(Data\\'. $entityName .'Interface $' . $lowerCamelCaseEntityName . ');' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Interface $customForm' . PHP_EOL;
            $contents .= '     * @return bool true on success' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function delete(Data\\' . $entityName . 'Interface $' . $lowerCamelCaseEntityName .');' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return bool true on success' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function deleteById($id);' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;

            if ($this->filesystemIo->write($apiRepositoryFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }

    }

    public function generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName)
    {
        $apiRepositoryFile = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api/Data' . '/' . $entityName . 'Interface.php';
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($entityName);
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'interface ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ' . strtoupper($this->helper->convertToSnakeCase($entityName)) . '_ID = "id" ;' . PHP_EOL;
            //start iterating the columns to declare the constants
            foreach ($dbColumns as $column) {
                $contents .= '    const ' . strtoupper($column['name']) . ' = "' . $column['name'] . '";' . PHP_EOL;
            }
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' .PHP_EOL;
            $contents .= '     * @return int' .PHP_EOL;
            $contents .= '     */' .PHP_EOL;
            $contents .= '    public function getId();' .PHP_EOL;
            $contents .= '' . PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                $contents .= '    /**' .PHP_EOL;
                $contents .= '     * @return mixed' .PHP_EOL;
                $contents .= '     */' .PHP_EOL;
                $contents .= '    public function get' . $this->helper->convertToUpperCamelCase($column['name']) . '();' .PHP_EOL;
                $contents .= '' . PHP_EOL;
            }
            //defining setters
            $contents .= '    /**' .PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return int' .PHP_EOL;
            $contents .= '     */' .PHP_EOL;
            $contents .= '    public function setId($id);' .PHP_EOL;
            $contents .= '' . PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                $contents .= '    /**' .PHP_EOL;
                $contents .= '     * @param $' . $this->helper->convertToLowerCamelCase($column['name']) . PHP_EOL;
                $contents .= '     * @return mixed' . PHP_EOL;
                $contents .= '     */' .PHP_EOL;
                $contents .= '    public function set' . $this->helper->convertToUpperCamelCase($column['name']) . '($' . $this->helper->convertToLowerCamelCase($column['name']) . ');' .PHP_EOL;
                $contents .= '' . PHP_EOL;
            }
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($apiRepositoryFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}