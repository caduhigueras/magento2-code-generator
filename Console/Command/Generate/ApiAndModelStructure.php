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

class ApiAndModelStructure
{
    private ManagerInterface $messageManager;
    private Filesystem $filesystem;
    private UploaderFactory $fileUploader;
    private ResourceConnection $resource;
    private File $file;
    private FileIo $filesystemIo;
    private UrlInterface $urlBuilder;
    private Data $helper;

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
        if (!$this->generateApiRepositoryFile($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Api Repository File';
            return $result;
        }
        if (!$this->generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName, $dbName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Api Interface File';
            return $result;
        }
        if (!$this->generateApiDataSearchInterfaceFile($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Api Search Interface File';
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
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @return bool
     */
    public function generateApiDataSearchInterfaceFile($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $apiRepositoryFile = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api/Data' . '/' . $entityName . 'SearchResultsInterface.php';
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature($entityName . 'SearchResultsInterface.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\SearchResultsInterface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'interface ' . $entityName . 'SearchResultsInterface extends SearchResultsInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Get ' . $entityName . ' list.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface[]' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getItems(): array;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Set ' . $entityName . ' list.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Interface[] $items' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setItems(array $items): array;' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
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
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature($entityName . 'RepositoryInterface.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\SearchCriteriaInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\LocalizedException;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Api\\Data\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'interface ' . $entityName . 'RepositoryInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param SearchCriteriaInterface $criteria' . PHP_EOL;
            $contents .= '     * @return Data\\' . $entityName . 'SearchResultsInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getList(SearchCriteriaInterface $criteria): Data\\' . $entityName . 'SearchResultsInterface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     * @throws LocalizedException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getById($id): ' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . '' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function save(Data\\' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . '): ' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . PHP_EOL;
            $contents .= '     * @return bool true on success' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function delete(Data\\' . $entityName . 'Interface $' . $lowerCamelCaseEntityName . '): bool;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return bool true on success' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function deleteById($id): bool;' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;

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
    public function generateApiDataInterfaceFile($appFolderPath, $vendorNamespaceArr, $dbColumns, $entityName, $dbName)
    {
        $apiRepositoryFile = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Api/Data' . '/' . $entityName . 'Interface.php';
        $nms = explode('_', $this->helper->convertToSnakeCase($entityName));
        $snakeCaseName = $this->helper->convertToSnakeCase($entityName);
        if (!$this->filesystemIo->fileExists($apiRepositoryFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature($entityName . 'Interface.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'interface ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseName) . '_ID = "id";' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseName) . '_TABLE = "' . $dbName . '";' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseName) . '_EVENT_PREFIX = "' . $snakeCaseName . '_collection' . '";' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseName) . '_EVENT_OBJECT = "' . $nms[1] . '_collection' . '";' . PHP_EOL;
            $contents .= '    const STORE_ID = "store_id";' . PHP_EOL;
            //start iterating the columns to declare the constants
            foreach ($dbColumns as $column) {
                $contents .= '    const ' . strtoupper($column['name']) . ' = "' . $column['name'] . '";' . PHP_EOL;
            }
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getId();' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getStoreId();' . PHP_EOL;
            $contents .= PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                if (in_array($column['type'], ['int', 'smallint'])) {
                    $returnType = 'int';
                } elseif ($column['type'] === 'boolean') {
                    $returnType = 'bool';
                } elseif ($column['type'] === 'decimal') {
                    $returnType = 'float';
                } else {
                    $returnType = 'string';
                }
                if ($column['nullable'] !== 'false') {
                    $returnTypeSignature = '?' . $returnType;
                    $returnType = "null|" . $returnType;
                } else {
                    $returnTypeSignature = $returnType;
                }
                $contents .= '    /**' . PHP_EOL;
                $contents .= '     * @return ' . $returnType . PHP_EOL;
                $contents .= '     */' . PHP_EOL;
                $contents .= '    public function get' . $this->helper->convertToUpperCamelCase($column['name']) . '(): ' . $returnTypeSignature . ';' . PHP_EOL;
                $contents .= '' . PHP_EOL;
            }
            //defining setters
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param mixed $id' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setId($id);' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param mixed $storeId' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setStoreId($storeId);' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                if (in_array($column['type'], ['int', 'smallint'])) {
                    $returnType = 'int';
                } elseif ($column['type'] === 'boolean') {
                    $returnType = 'bool';
                } elseif ($column['type'] === 'decimal') {
                    $returnType = 'float';
                } else {
                    $returnType = 'string';
                }
                if ($column['nullable'] !== 'false') {
                    $returnTypeSignature = '?' . $returnType;
                    $returnType = "null|" . $returnType;
                } else {
                    $returnTypeSignature = $returnType;
                }
                $contents .= '    /**' . PHP_EOL;
                $contents .= '     * @param ' . $returnType . ' $' . $this->helper->convertToLowerCamelCase($column['name']) . PHP_EOL;
                $contents .= '     * @return void' . PHP_EOL;
                $contents .= '     */' . PHP_EOL;
                $contents .= '    public function set' . $this->helper->convertToUpperCamelCase($column['name']) . '(' . $returnTypeSignature . ' $' . $this->helper->convertToLowerCamelCase($column['name']) . '): void;' . PHP_EOL;
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
        $modelFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model';
        $modelFileName = $entityName . '.php';
        if (!$this->generateModelFile($modelFolder, $modelFileName, $dbColumns, $entityName, $dbName, $vendorNamespaceArr)) {
            return false;
        }
        $modelRepositoryFile = $entityName . 'Repository.php';
        if (!$this->generateModelRepositoryFile($modelFolder, $modelRepositoryFile, $entityName, $vendorNamespaceArr)) {
            return false;
        }
        $resourceModelFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/ResourceModel';
        $resourceModelFile = $entityName . '.php';
        if (!$this->generateResourceModelFile($resourceModelFolder, $resourceModelFile, $entityName, $dbName, $vendorNamespaceArr)) {
            return false;
        }
        $resourceModelCollectionFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' . '/ResourceModel'
            . '/' . $entityName;
        $resourceModelCollectionFile = 'Collection.php';
        if (!$this->generateResourceModelCollectionFile($resourceModelCollectionFolder, $resourceModelCollectionFile, $entityName, $vendorNamespaceArr)) {
            return false;
        }
        $resourceModelGridCollectionFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model' .
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
            $contents .= $this->helper->getSignature($file);
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'Grid;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\Framework\Api\ExtensibleDataInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Api\Search\AggregationInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Api\Search\SearchResultInterface as SearchResultInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Api\SearchCriteriaInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;' . PHP_EOL;
            $contents .= 'use Magento\Framework\DB\Adapter\AdapterInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\EntityManager\MetadataPool;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Event\ManagerInterface as EventManager;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Model\ResourceModel\Db\AbstractDb;' . PHP_EOL;
            $contents .= 'use Psr\Log\LoggerInterface as Logger;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'Collection as ' . $entityName . 'Collection;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class Collection extends ' . $entityName . 'Collection implements SearchResultInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    protected AggregationInterface $aggregations;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param EntityFactory $entityFactory' . PHP_EOL;
            $contents .= '     * @param Logger $logger' . PHP_EOL;
            $contents .= '     * @param FetchStrategy $fetchStrategy' . PHP_EOL;
            $contents .= '     * @param EventManager $eventManager' . PHP_EOL;
            $contents .= '     * @param MetadataPool $metadataPool' . PHP_EOL;
            $contents .= '     * @param string $mainTable' . PHP_EOL;
            $contents .= '     * @param string $eventPrefix' . PHP_EOL;
            $contents .= '     * @param string $eventObject' . PHP_EOL;
            $contents .= '     * @param string $resourceModel' . PHP_EOL;
            $contents .= '     * @param string $model' . PHP_EOL;
            $contents .= '     * @param AdapterInterface|null $connection' . PHP_EOL;
            $contents .= '     * @param AbstractDb|null $resource' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        EntityFactory $entityFactory,' . PHP_EOL;
            $contents .= '        Logger $logger,' . PHP_EOL;
            $contents .= '        FetchStrategy $fetchStrategy,' . PHP_EOL;
            $contents .= '        EventManager $eventManager,' . PHP_EOL;
            $contents .= '        MetadataPool $metadataPool,' . PHP_EOL;
            $contents .= '        $mainTable,' . PHP_EOL;
            $contents .= '        $eventPrefix,' . PHP_EOL;
            $contents .= '        $eventObject,' . PHP_EOL;
            $contents .= '        $resourceModel,' . PHP_EOL;
            $contents .= '        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,' . PHP_EOL;
            $contents .= '        AdapterInterface $connection = null,' . PHP_EOL;
            $contents .= '        AbstractDb $resource = null' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '      parent::__construct(' . PHP_EOL;
            $contents .= '        $entityFactory,' . PHP_EOL;
            $contents .= '        $logger,' . PHP_EOL;
            $contents .= '        $fetchStrategy,' . PHP_EOL;
            $contents .= '        $eventManager,' . PHP_EOL;
            $contents .= '        $connection,' . PHP_EOL;
            $contents .= '        $resource' . PHP_EOL;
            $contents .= '      );' . PHP_EOL;
            $contents .= '      $this->_eventPrefix = $eventPrefix;' . PHP_EOL;
            $contents .= '      $this->_eventObject = $eventObject;' . PHP_EOL;
            $contents .= '      $this->_init($model, $resourceModel);' . PHP_EOL;
            $contents .= '      $this->setMainTable($mainTable);' . PHP_EOL;
            $contents .= '  }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Set items list.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param ExtensibleDataInterface[] $items' . PHP_EOL;
            $contents .= '     * @return $this' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setItems(array $items = null): Collection' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Get aggregation interface instance' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return AggregationInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getAggregations(): AggregationInterface' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->aggregations;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Set aggregation interface instance' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param AggregationInterface $aggregations' . PHP_EOL;
            $contents .= '     * @return $this' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setAggregations($aggregations): Collection' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->aggregations = $aggregations;' . PHP_EOL;
            $contents .= '        return $this;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Get search criteria.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return SearchCriteriaInterface|null' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getSearchCriteria(): ?SearchCriteriaInterface' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return null;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Set search criteria.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param SearchCriteriaInterface|null $searchCriteria' . PHP_EOL;
            $contents .= '     * @return $this' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null): Collection' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Get total count.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return int' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getTotalCount(): int' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->getSize();' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Set total count.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param int $totalCount' . PHP_EOL;
            $contents .= '     * @return $this' . PHP_EOL;
            $contents .= '     * @SuppressWarnings(PHPMD.UnusedFormalParameter)' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setTotalCount($totalCount): Collection' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
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
        $snakeCaseName = $this->helper->convertToSnakeCase($entityName);
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature($file);
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . 'ResourceModel' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . 'Data' . '\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . $entityName . ';' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . 'ResourceModel' . '\\' . $entityName . ' as ' . $entityName . 'Resource;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class Collection extends AbstractCollection' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected $_idFieldName = ' . $entityName . 'Interface::' . strtoupper($snakeCaseName) . '_ID' . ';' . PHP_EOL;
            $contents .= '    protected $_eventPrefix = ' . $entityName . 'Interface::' . strtoupper($snakeCaseName) . '_EVENT_PREFIX' . ';' . PHP_EOL;
            $contents .= '    protected $_eventObject = ' . $entityName . 'Interface::' . strtoupper($snakeCaseName) . '_EVENT_OBJECT' . ';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(' . $entityName . '::class, ' . $entityName . 'Resource::class);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
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
    public function generateResourceModelFile($folder, $file, $entityName, $dbName, $vendorNamespaceArr)
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
            $contents .= $this->helper->getSignature($file);
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . 'ResourceModel;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\EntityManager\\EntityManager;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\AbstractModel;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\ResourceModel\\Db\\AbstractDb;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\ResourceModel\\Db\\Context;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . 'Data' . '\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class ' . $entityName . ' extends AbstractDb' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    private EntityManager $entityManager;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(Context $context, EntityManager $entityManager, $connectionName = null)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->entityManager = $entityManager;' . PHP_EOL;
            $contents .= '        parent::__construct($context, $connectionName);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(' . PHP_EOL;
            $contents .= '            ' . $entityName . 'Interface::' . strtoupper($this->helper->convertToSnakeCase($entityName)) . '_TABLE' . ',' . PHP_EOL ;
            $contents .= '            ' . $entityName . 'Interface::' . strtoupper($this->helper->convertToSnakeCase($entityName)) . '_ID' . PHP_EOL;
            $contents .= '        );' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function save(AbstractModel $object):' . $entityName . PHP_EOL;
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
            $contents .= $this->helper->getSignature($file);
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . ';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\SearchCriteria\\CollectionProcessorInterface;' . PHP_EOL;
            $contents .= 'use Magento\Framework\Api\SearchCriteriaBuilder;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\SearchCriteriaBuilderFactory;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\SearchCriteriaInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\CouldNotDeleteException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\CouldNotSaveException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\NoSuchEntityException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Stdlib\\DateTime\\TimezoneInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Store\Model\\StoreManagerInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . $entityName . 'RepositoryInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . 'ResourceModel' . '\\' . $entityName . ' as Resource' . $entityName . ';' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . 'ResourceModel' . '\\' . $entityName . '\\' . 'CollectionFactory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class ' . $entityName . 'Repository implements ' . $entityName . 'RepositoryInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected CollectionFactory $collectionFactory;' . PHP_EOL;
            $contents .= '    protected Resource' . $entityName . ' $resource;' . PHP_EOL;
            $contents .= '    protected ' . $entityName . 'Factory $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory;' . PHP_EOL;
            $contents .= '    protected StoreManagerInterface $storeManager;' . PHP_EOL;
            $contents .= '    private TimezoneInterface $timezone;' . PHP_EOL;
            $contents .= '    private CollectionProcessorInterface $collectionProcessor;' . PHP_EOL;
            $contents .= '    private Data\\' . $entityName . 'SearchResultsInterfaceFactory' . ' $searchResultsFactory;' . PHP_EOL;
            $contents .= '    private SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory;' . PHP_EOL;
            $contents .= '    private SearchCriteriaBuilder $searchCriteriaBuilder;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * ' . $entityName . 'Repository constructor.' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Factory $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory' . PHP_EOL;
            $contents .= '     * @param Resource' . $entityName . ' $resource' . PHP_EOL;
            $contents .= '     * @param StoreManagerInterface $storeManager' . PHP_EOL;
            $contents .= '     * @param CollectionFactory $collectionFactory' . PHP_EOL;
            $contents .= '     * @param TimezoneInterface $timezone' . PHP_EOL;
            $contents .= '     * @param Data\\' . $entityName . 'SearchResultsInterfaceFactory $searchResultsFactory' . PHP_EOL;
            $contents .= '     * @param CollectionProcessorInterface|null $collectionProcessor' . PHP_EOL;
            $contents .= '     * @param SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Factory $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory,' . PHP_EOL;
            $contents .= '        Resource' . $entityName . ' $resource,' . PHP_EOL;
            $contents .= '        StoreManagerInterface $storeManager,' . PHP_EOL;
            $contents .= '        CollectionFactory $collectionFactory,' . PHP_EOL;
            $contents .= '        TimezoneInterface $timezone,' . PHP_EOL;
            $contents .= '        Data\\' . $entityName . 'SearchResultsInterfaceFactory $searchResultsFactory,' . PHP_EOL;
            $contents .= '        CollectionProcessorInterface $collectionProcessor,' . PHP_EOL;
            $contents .= '        SearchCriteriaBuilderFactory $searchCriteriaBuilderFactory' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        $this->resource = $resource;' . PHP_EOL;
            $contents .= '        $this->' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory = $' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory;' . PHP_EOL;
            $contents .= '        $this->collectionFactory = $collectionFactory;' . PHP_EOL;
            $contents .= '        $this->storeManager = $storeManager;' . PHP_EOL;
            $contents .= '        $this->timezone = $timezone;' . PHP_EOL;
            $contents .= '        $this->collectionProcessor = $collectionProcessor;' . PHP_EOL;
            $contents .= '        $this->searchResultsFactory = $searchResultsFactory;' . PHP_EOL;
            $contents .= '        $this->searchCriteriaBuilder = $searchCriteriaBuilderFactory->create();' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param SearchCriteriaInterface $criteria' . PHP_EOL;
            $contents .= '     * @return Data\\' . $entityName . 'SearchResultsInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getList(SearchCriteriaInterface $criteria): Data\\' . $entityName . 'SearchResultsInterface' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        /** @var \\' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Model\\ResourceModel\\' . $entityName . '\\Collection $collection */' . PHP_EOL;
            $contents .= '        $collection = $this->collectionFactory->create();' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        $this->collectionProcessor->process($criteria, $collection);' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        /** @var Data\\' . $entityName . 'SearchResultsInterface $searchResults */' . PHP_EOL;
            $contents .= '        $searchResults = $this->searchResultsFactory->create();' . PHP_EOL;
            $contents .= '        $searchResults->setSearchCriteria($criteria);' . PHP_EOL;
            $contents .= '        $searchResults->setItems($collection->getItems());' . PHP_EOL;
            $contents .= '        $searchResults->setTotalCount($collection->getSize());' . PHP_EOL;
            $contents .= '        return $searchResults;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return ' . $entityName . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getById($id): ' . $entityName . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $' . $this->helper->convertToLowerCamelCase($entityName) . ' = $this->' . $this->helper->convertToLowerCamelCase($entityName) . 'Factory->create();' . PHP_EOL;
            $contents .= '        $this->resource->load($' . $this->helper->convertToLowerCamelCase($entityName) . ', $id);' . PHP_EOL;
            $contents .= '        if (!$' . $this->helper->convertToLowerCamelCase($entityName) . '->getId()) {' . PHP_EOL;
            $contents .= '            throw new NoSuchEntityException(__(\'The Entity with the "%1" ID doesn\\\'t exist.\', $id));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $' . $this->helper->convertToLowerCamelCase($entityName) . ';' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . PHP_EOL;
            $contents .= '     * @return Data\\' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '     * @throws CouldNotSaveException' . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function save(Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . '): Data\\' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        if (empty($' . $this->helper->convertToLowerCamelCase($entityName) . '->getStoreId()) && $' . $this->helper->convertToLowerCamelCase($entityName) . '->getStoreId() !== "0") {' . PHP_EOL;
            $contents .= '            $' . $this->helper->convertToLowerCamelCase($entityName) . '->setStoreId($this->storeManager->getStore()->getId());' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        try {' . PHP_EOL;
            $contents .= '            $this->resource->save($' . $this->helper->convertToLowerCamelCase($entityName) . ');' . PHP_EOL;
            $contents .= '        } catch (\Exception $exception) {' . PHP_EOL;
            $contents .= '            throw new CouldNotSaveException(__($exception->getMessage()));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $' . $this->helper->convertToLowerCamelCase($entityName) . ';' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . PHP_EOL;
            $contents .= '     * @return bool' . PHP_EOL;
            $contents .= '     * @throws CouldNotDeleteException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function delete(Data\\' . $entityName . 'Interface $' . $this->helper->convertToLowerCamelCase($entityName) . '): bool' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        try {' . PHP_EOL;
            $contents .= '            $this->resource->delete($' . $this->helper->convertToLowerCamelCase($entityName) . ');' . PHP_EOL;
            $contents .= '        } catch (\Exception $exception) {' . PHP_EOL;
            $contents .= '            throw new CouldNotDeleteException(__($exception->getMessage()));' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return true;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param $id' . PHP_EOL;
            $contents .= '     * @return bool' . PHP_EOL;
            $contents .= '     * @throws CouldNotDeleteException' . PHP_EOL;
            $contents .= '     * @throws NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function deleteById($id): bool' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->delete($this->getById($id));' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
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
    public function generateModelFile($folder, $file, $dbColumns, $entityName, $dbName, $vendorNamespaceArr)
    {
        try {
            $this->filesystemIo->checkAndCreateFolder($folder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $filePath = $folder . '/' . $file;
        $snakeCaseName = $this->helper->convertToSnakeCase($entityName);
        if (!$this->filesystemIo->fileExists($filePath)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature($file);
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . ';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\Model\\AbstractModel;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api\\Data\\' . $entityName . 'Interface;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class ' . $entityName . ' extends AbstractModel implements ' . $entityName . 'Interface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected $_eventPrefix = ' . $entityName . 'Interface::' . strtoupper($snakeCaseName) . '_EVENT_PREFIX' . ';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected function _construct()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->_init(\\' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model\\' . 'ResourceModel\\' . $entityName . '::class);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getStoreId()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->getData(self::STORE_ID);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            //defining getters
            foreach ($dbColumns as $column) {
                if (in_array($column['type'], ['int', 'smallint'])) {
                    $returnType = 'int';
                } elseif ($column['type'] === 'boolean') {
                    $returnType = 'bool';
                } elseif ($column['type'] === 'decimal') {
                    $returnType = 'float';
                } else {
                    $returnType = 'string';
                }
                if ($column['nullable'] !== 'false') {
                    $returnTypeSignature = '?' . $returnType;
                    $returnType = "null|" . $returnType;
                } else {
                    $returnTypeSignature = $returnType;
                }
                $contents .= '    /**' . PHP_EOL;
                $contents .= '     * @return ' . $returnType . PHP_EOL;
                $contents .= '     */' . PHP_EOL;
                $contents .= '    public function get' . $this->helper->convertToUpperCamelCase($column['name']) . '(): ' . $returnTypeSignature . PHP_EOL;
                $contents .= '    {' . PHP_EOL;
                if (in_array($column['type'], ['int', 'smallint']) && $column['nullable'] === 'false') {
                    $contents .= '        return (int) $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                } elseif (in_array($column['type'], ['int', 'smallint']) && $column['nullable'] === 'true') {
                    $contents .= '        $data =  $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                    $contents .= '        if ($data !== null) {' . PHP_EOL;
                    $contents .= '            $data = (int) $data;' . PHP_EOL;
                    $contents .= '        }' . PHP_EOL;
                    $contents .= '        return $data;' . PHP_EOL;
                } elseif ($column['type'] === 'decimal' && $column['nullable'] === 'false') {
                    $contents .= '        return floatval($this->getData(self::' . strtoupper($column['name']) . '));' . PHP_EOL;
                } elseif ($column['type'] === 'decimal' && $column['nullable'] === 'true') {
                    $contents .= '        $data = $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                    $contents .= '        if ($data !== null) {' . PHP_EOL;
                    $contents .= '            $data = floatval($data);' . PHP_EOL;
                    $contents .= '        }' . PHP_EOL;
                    $contents .= '        return $data;' . PHP_EOL;
                } elseif ($column['type'] === 'boolean' && $column['nullable'] === 'false') {
                    $contents .= '        return boolval($this->getData(self::' . strtoupper($column['name']) . '));' . PHP_EOL;
                } elseif ($column['type'] === 'boolean' && $column['nullable'] === 'true') {
                    $contents .= '        $data = $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                    $contents .= '        if ($data !== null) {' . PHP_EOL;
                    $contents .= '            $data = boolval($data);' . PHP_EOL;
                    $contents .= '        }' . PHP_EOL;
                    $contents .= '        return $data;' . PHP_EOL;
                } else {
                    $contents .= '        return $this->getData(self::' . strtoupper($column['name']) . ');' . PHP_EOL;
                }
                $contents .= '    }' . PHP_EOL;
                $contents .= PHP_EOL;
            }
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param mixed $storeId' . PHP_EOL;
            $contents .= '     * @return mixed' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function setStoreId($storeId)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->setData(self::STORE_ID, $storeId);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            //defining setters
            foreach ($dbColumns as $column) {
                if (in_array($column['type'], ['int', 'smallint'])) {
                    $returnType = 'int';
                } elseif ($column['type'] === 'boolean') {
                    $returnType = 'bool';
                } elseif ($column['type'] === 'decimal') {
                    $returnType = 'float';
                } else {
                    $returnType = 'string';
                }
                if ($column['nullable'] !== 'false') {
                    $returnTypeSignature = '?' . $returnType;
                    $returnType = "null|" . $returnType;
                } else {
                    $returnTypeSignature = $returnType;
                }
                $contents .= '    /**' . PHP_EOL;
                $contents .= '     * @param ' . $returnType . ' $' . $this->helper->convertToLowerCamelCase($column['name']) . PHP_EOL;
                $contents .= '     * @return void' . PHP_EOL;
                $contents .= '     */' . PHP_EOL;
                $contents .= '    public function set' . $this->helper->convertToUpperCamelCase($column['name']) . '(' . $returnTypeSignature . ' $' . $this->helper->convertToLowerCamelCase($column['name']) . '): void' . PHP_EOL;
                $contents .= '    {' . PHP_EOL;
                $contents .= '        $this->setData(self::' . strtoupper($column['name']) . ', $' . $this->helper->convertToLowerCamelCase($column['name']) . ');' . PHP_EOL;
                $contents .= '    }' . PHP_EOL;
                $contents .= PHP_EOL;
            }
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
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
