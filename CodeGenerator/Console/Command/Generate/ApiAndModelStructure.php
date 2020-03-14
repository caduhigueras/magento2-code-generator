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

    /**
     * Generates Api Repository Interface File
     * @param $vendorNamespace
     * @param $dbColumns
     * @param $entityName
     * @param $dbName
     * @return array|bool
     * @throws FileSystemException
     */
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
        if (!$this->generateApiRepositoryFile($appFolderPath, $vendorNamespaceArr, $entityName))
        {
            $result['success'] = false;
            $result['message'] = 'Could not generate Api Repository File';
            return $result;
        }
        if (!$this->generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Api Interface File';
            return $result;
        }
        if (!$this->generateModelFiles($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName, $dbName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Model Files';
            return $result;
        }
        $result['success'] = true;
//        $result['message'] = 'Could not generate Api Repository File';
        return $result;
    }

    /**
     * Generates Api Repository Interface File
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @return bool
     */
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
            $contents .= '     * @param ' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . PHP_EOL;
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

    /**
     * Generates Api/Data Entity Interface file
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $dbColumns
     * @param $entityName
     * @return bool
     */
    public function generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName)
    {
        $apiRepositoryFile = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api/Data' . '/' . $entityName . 'Interface.php';
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'interface ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ' . strtoupper($this->helper->convertToSnakeCase($entityName)) . '_ID = "id" ;' . PHP_EOL;
            $contents .= '    const STORE_ID = "store_id" ;' . PHP_EOL;
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
            $contents .= '    /**' .PHP_EOL;
            $contents .= '     * @return int' .PHP_EOL;
            $contents .= '     */' .PHP_EOL;
            $contents .= '    public function getStoreId();' .PHP_EOL;
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
            $contents .= '    /**' .PHP_EOL;
            $contents .= '     * @param $storeId' . PHP_EOL;
            $contents .= '     * @return int' .PHP_EOL;
            $contents .= '     */' .PHP_EOL;
            $contents .= '    public function setStoreId($storeId);' .PHP_EOL;
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

    /**
     * generates all files needed on the Model level
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $dbColumns
     * @param $entityName
     * @param $dbName
     * @return bool
     */
    public function generateModelFiles($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName, $dbName)
    {
        $modelFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/' . $entityName;
        $modelFileName = $entityName . '.php';
        if (!$this->generateModelFile($modelFolder, $modelFileName, $dbColumns, $entityName, $dbName,$vendorNamespaceArr)) {
            return false;
        }
        $modelRepositoryFile = $entityName . 'Repository.php';
        if (!$this->generateModelRepositoryFile($modelFolder, $modelRepositoryFile, $entityName, $vendorNamespaceArr)) {
            return false;
        }
        $resourceModelFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/' . $entityName . '/ResourceModel';
        $resourceModelFile = $entityName . '.php';
        if (!$this->generateResourceModelFile($resourceModelFolder, $resourceModelFile, $entityName, $dbName,$vendorNamespaceArr)) {
            return false;
        }
        $resourceModelCollectionFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/' . $entityName .
            '/ResourceModel' . '/' . $entityName;
        $resourceModelCollectionFile = 'Collection.php';
        if (!$this->generateResourceModelCollectionFile($resourceModelCollectionFolder, $resourceModelCollectionFile, $entityName, $vendorNamespaceArr)) {
            return false;
        }
        $resourceModelGridCollectionFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/' . $entityName .
            '/ResourceModel' . '/' . $entityName . '/Grid';
        $resourceModelGridCollectionFile = 'Collection.php';
        if (!$this->generateResourceModelGridCollectionFile($resourceModelGridCollectionFolder, $resourceModelGridCollectionFile, $entityName, $vendorNamespaceArr, $dbName)) {
            return false;
        }
        return true;
    }

    /**
     * generate collection grid file
     * @param $folder
     * @param $file
     * @param $entityName
     * @param $vendorNamespaceArr
     * @param $dbName
     * @return bool
     */
    public function generateResourceModelGridCollectionFile($folder, $file, $entityName, $vendorNamespaceArr, $dbName)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'Grid;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Data\\Collection\\Db\\FetchStrategyInterface as FetchStrategy;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Data\\Collection\\EntityFactoryInterface as EntityFactory;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Event\\ManagerInterface as EventManager;' . PHP_EOL;
            $contents .= 'use Psr\\Log\\LoggerInterface as Logger;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class Collection extends \\Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\SearchResult' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        EntityFactory $entityFactory,' . PHP_EOL;
            $contents .= '        Logger $logger,' . PHP_EOL;
            $contents .= '        FetchStrategy $fetchStrategy,' . PHP_EOL;
            $contents .= '        EventManager $eventManager,' . PHP_EOL;
            $contents .= '        $mainTable = \'' . $dbName . '\',' . PHP_EOL;
            $contents .= '        $resourceModel = \'' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . '\'' . PHP_EOL;
            $contents .= '    )' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        parent::__construct(' . PHP_EOL;
            $contents .= '            $entityFactory,' . PHP_EOL;
            $contents .= '            $logger,' . PHP_EOL;
            $contents .= '            $fetchStrategy,' . PHP_EOL;
            $contents .= '            $eventManager,' . PHP_EOL;
            $contents .= '            $mainTable,' . PHP_EOL;
            $contents .= '            $resourceModel' . PHP_EOL;
            $contents .= '        );' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            if ($this->filesystemIo->write($filePath, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * generate resource model collection file
     * @param $folder
     * @param $file
     * @param $entityName
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateResourceModelCollectionFile($folder, $file, $entityName, $vendorNamespaceArr)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . ' as ' . $entityName . 'Resource;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class Collection extends AbstractCollection' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected $_idFieldName = \'id\';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(' . $entityName . '::class, ' . $entityName . 'Resource::class);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($filePath, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * generates resource model file
     * @param $folder
     * @param $file
     * @param $entityName
     * @param $dbName
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateResourceModelFile($folder, $file, $entityName, $dbName,$vendorNamespaceArr)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\' . 'ResourceModel;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\EntityManager\\EntityManager;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\AbstractModel;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\ResourceModel\\Db\\AbstractDb;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\ResourceModel\\Db\\Context;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class ' . $entityName . ' extends AbstractDb' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var EntityManager' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $entityManager;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    public function __construct(Context $context, EntityManager $entityManager, $connectionName = null)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->entityManager = $entityManager;' . PHP_EOL;
            $contents .= '        parent::__construct($context, $connectionName);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(\'' . $dbName . '\', \'id\');' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    public function save(AbstractModel $object)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->entityManager->save($object);' . PHP_EOL;
            $contents .= '        return $this;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            if ($this->filesystemIo->write($filePath, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * generates entity repository file
     * @param $folder
     * @param $file
     * @param $entityName
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateModelRepositoryFile($folder, $file, $entityName, $vendorNamespaceArr)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\CouldNotDeleteException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\CouldNotSaveException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\NoSuchEntityException;' . PHP_EOL;
            $contents .= 'use Magento\\Store\Model\\StoreManagerInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . $entityName . 'RepositoryInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . ' as Resource' . $entityName . ';' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'CollectionFactory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class ' . $entityName . 'Repository implements ' . $entityName . 'RepositoryInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var CollectionFactory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $collectionFactory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var Resource' . $entityName . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $resource;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Factory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var StoreManagerInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $storeManager;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * ' . $entityName . 'Repository constructor.' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Factory $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory' . PHP_EOL;
            $contents .= '     * @param Resource' . $entityName . ' $resource' . PHP_EOL;
            $contents .= '     * @param CollectionFactory $collectionFactory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Factory $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory,' . PHP_EOL;
            $contents .= '        Resource' . $entityName . ' $resource,' . PHP_EOL;
            $contents .= '        StoreManagerInterface $storeManager,' . PHP_EOL;
            $contents .= '        CollectionFactory $collectionFactory' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        $this->resource = $resource;' . PHP_EOL;
            $contents .= '        $this->' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory = $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory;' . PHP_EOL;
            $contents .= '        $this->collectionFactory = $collectionFactory;' . PHP_EOL;
            $contents .= '        $this->storeManager = $storeManager;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return \\Magento\\Framework\\DataObject[]|\\' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data\\' . $entityName . 'Interface[]' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getList()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->collectionFactory->create()->getItems();' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getById($id)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $' . $this->helper->convertToLowerCamelCase($entityName) . ' = $this->' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory->create();' . PHP_EOL;
            $contents .= '        $this->resource->load($' . $this->helper->convertToLowerCamelCase($entityName) . ', $id);' . PHP_EOL;
            $contents .= '        if (!$' . $this->helper->convertToLowerCamelCase($entityName) . '->getId()) {' . PHP_EOL;
            $contents .= '            throw new NoSuchEntityException(__(\'The Entity with the "%1" ID doesn\\\'t exist.\', $id));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $' . $this->helper->convertToLowerCamelCase($entityName) . ';' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . PHP_EOL;
            $contents .= '     * @return Data\\' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     * @throws CouldNotSaveException' . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function save(Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . ')' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        if (empty($' . $this->helper->convertToLowerCamelCase($entityName) . '->getStoreId())) {' . PHP_EOL;
            $contents .= '            $' . $this->helper->convertToLowerCamelCase($entityName) . '->setStoreId($this->storeManager->getStore()->getId());' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '        try {' . PHP_EOL;
            $contents .= '            $this->resource->save($' . $this->helper->convertToLowerCamelCase($entityName) . ');' . PHP_EOL;
            $contents .= '        } catch (\Exception $exception) {' . PHP_EOL;
            $contents .= '            throw new CouldNotSaveException(__($exception->getMessage()));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $' . $this->helper->convertToLowerCamelCase($entityName) . ';' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . PHP_EOL;
            $contents .= '     * @return bool' . PHP_EOL;
            $contents .= '     * @throws CouldNotDeleteException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function delete(Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . ')' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        try {' . PHP_EOL;
            $contents .= '            $this->resource->delete($' . $this->helper->convertToLowerCamelCase($entityName) . ');' . PHP_EOL;
            $contents .= '        } catch (\Exception $exception) {' . PHP_EOL;
            $contents .= '            throw new CouldNotDeleteException(__($exception->getMessage()));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return true;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return bool' . PHP_EOL;
            $contents .= '     * @throws CouldNotDeleteException' . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function deleteById($id)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->delete($this->getById($id));' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($filePath, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * Generates entity model file
     * @param $folder
     * @param $file
     * @param $dbColumns
     * @param $entityName
     * @param $dbName
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateModelFile($folder, $file, $dbColumns, $entityName, $dbName,$vendorNamespaceArr)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\AbstractModel;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class ' . $entityName . ' extends AbstractModel implements ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected $_eventPrefix = "' . $dbName . '_grid_collection";' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(\\' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . '\\ResourceModel\\' . $entityName . '::class);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' .PHP_EOL;
            $contents .= '     * @return mixed' .PHP_EOL;
            $contents .= '     */' .PHP_EOL;
            $contents .= '    public function getStoreId()' .PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->getData(self::STORE_ID);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                $contents .= '    /**' .PHP_EOL;
                $contents .= '     * @return mixed' .PHP_EOL;
                $contents .= '     */' .PHP_EOL;
                $contents .= '    public function get' . $this->helper->convertToUpperCamelCase($column['name']) . '()' .PHP_EOL;
                $contents .= '    {' . PHP_EOL;
                $contents .= '        return $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                $contents .= '    }' . PHP_EOL;
                $contents .= '' . PHP_EOL;
            }
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param $storeId' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setStoreId($storeId)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->setData(self::STORE_ID, $storeId);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            //defining setters
            foreach ($dbColumns as $column) {
                $contents .= '    /**' . PHP_EOL;
                $contents .= '     * @param $' . $this->helper->convertToLowerCamelCase($column['name']) . PHP_EOL;
                $contents .= '     * @return mixed' . PHP_EOL;
                $contents .= '     */' . PHP_EOL;
                $contents .= '    public function set' . $this->helper->convertToUpperCamelCase($column['name']) . '($' . $this->helper->convertToLowerCamelCase($column['name']) . ')' . PHP_EOL;
                $contents .= '    {' . PHP_EOL;
                $contents .= '        return $this->setData(self::' . strtoupper($column['name']) . ', $' . $this->helper->convertToLowerCamelCase($column['name']) . ');' . PHP_EOL;
                $contents .= '    }' . PHP_EOL;
                $contents .= '' . PHP_EOL;
            }
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($filePath, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

}