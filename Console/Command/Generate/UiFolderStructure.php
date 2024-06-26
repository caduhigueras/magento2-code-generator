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

class UiFolderStructure
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

    public function __construct
    (
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
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $dbColumns
     * @param $frontName
     * @param $uiFormStyle
     * @return mixed
     */
    public function generateUiFolderFiles($vendorNamespaceArr, $entityName, $dbColumns, $frontName, $uiFormStyle)
    {
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $uiListingFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component/Listing/Column';
        try {
            $this->filesystemIo->checkAndCreateFolder($uiListingFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        if (!$this->generateListingActionFile($vendorNamespaceArr, $uiListingFolder, $entityName, $frontName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Listing Actions.php file';
            return $result;
        }
        if (!$this->generateDataProviderFile($vendorNamespaceArr, $appFolderPath, $entityName, $dbColumns)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate DataProvider.php file';
            return $result;
        }
        if (!$this->generateModelDataProviderFile($vendorNamespaceArr, $appFolderPath, $entityName, $dbColumns, $uiFormStyle)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Model DataProvider.php file';
            return $result;
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * @param $vendorNamespaceArr
     * @param $appFolderPath
     * @param $entityName
     * @param $dbColumns
     * @return bool
     */
    public function generateDataProviderFile($vendorNamespaceArr, $appFolderPath, $entityName, $dbColumns)
    {
        //TODO: add serializers
        $componentFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component';
        $actionsFile = $componentFolder . '/' . 'DataProvider.php';
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($entityName);
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($actionsFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature('DataProvider.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Ui\\Component;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Cms\\Ui\\Component\\AddFilterInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\Filter;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\FilterBuilder;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Api\\Search\\SearchCriteriaBuilder;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\App\\RequestInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\Reporting;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class DataProvider extends \\Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\DataProvider' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var AddFilterInterface[]' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private array $additionalFilterPool;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param string $name' . PHP_EOL;
            $contents .= '     * @param string $primaryFieldName' . PHP_EOL;
            $contents .= '     * @param string $requestFieldName' . PHP_EOL;
            $contents .= '     * @param Reporting $reporting' . PHP_EOL;
            $contents .= '     * @param SearchCriteriaBuilder $searchCriteriaBuilder' . PHP_EOL;
            $contents .= '     * @param RequestInterface $request' . PHP_EOL;
            $contents .= '     * @param FilterBuilder $filterBuilder' . PHP_EOL;
            $contents .= '     * @param array $meta' . PHP_EOL;
            $contents .= '     * @param array $data' . PHP_EOL;
            $contents .= '     * @param array $additionalFilterPool' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        $name,' . PHP_EOL;
            $contents .= '        $primaryFieldName,' . PHP_EOL;
            $contents .= '        $requestFieldName,' . PHP_EOL;
            $contents .= '        Reporting $reporting,' . PHP_EOL;
            $contents .= '        SearchCriteriaBuilder $searchCriteriaBuilder,' . PHP_EOL;
            $contents .= '        RequestInterface $request,' . PHP_EOL;
            $contents .= '        FilterBuilder $filterBuilder,' . PHP_EOL;
            $contents .= '        array $meta = [],' . PHP_EOL;
            $contents .= '        array $data = [],' . PHP_EOL;
            $contents .= '        array $additionalFilterPool = []' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        parent::__construct(' . PHP_EOL;
            $contents .= '            $name,' . PHP_EOL;
            $contents .= '            $primaryFieldName,' . PHP_EOL;
            $contents .= '            $requestFieldName,' . PHP_EOL;
            $contents .= '            $reporting,' . PHP_EOL;
            $contents .= '            $searchCriteriaBuilder,' . PHP_EOL;
            $contents .= '            $request,' . PHP_EOL;
            $contents .= '            $filterBuilder,' . PHP_EOL;
            $contents .= '            $meta,' . PHP_EOL;
            $contents .= '            $data' . PHP_EOL;
            $contents .= '        );' . PHP_EOL;
            $contents .= '        $this->additionalFilterPool = $additionalFilterPool;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param array $meta' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function prepareMeta(array $meta): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $meta;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritdoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function addFilter(Filter $filter)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        if (!empty($this->additionalFilterPool[$filter->getField()])) {' . PHP_EOL;
            $contents .= '            $this->additionalFilterPool[$filter->getField()]->addFilter($this->searchCriteriaBuilder, $filter);' . PHP_EOL;
            $contents .= '        } else {' . PHP_EOL;
            $contents .= '            parent::addFilter($filter);' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
            if ($this->filesystemIo->write($actionsFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists

            return true;
        }
    }

    /**
     * @param $vendorNamespaceArr
     * @param $uiListingFolder
     * @param $entityName
     * @param $frontName
     * @return bool
     */
    public function generateListingActionFile($vendorNamespaceArr, $uiListingFolder, $entityName, $frontName)
    {
        $actionsFile = $uiListingFolder . '/' . 'Actions.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($actionsFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature('Actions.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Ui\\Component\\Listing\\Column;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\UrlInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\ContextInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponentFactory;' . PHP_EOL;
            $contents .= 'use Magento\\Ui\\Component\\Listing\\Columns\\Column;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class Actions extends Column' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /** Url path */' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseEntityName) . '_URL_PATH_EDIT = \''. $frontName . '/' . strtolower($entityName) . '/edit\';' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseEntityName) . '_URL_PATH_DELETE = \''. $frontName . '/' . strtolower($entityName) . '/delete\';' . PHP_EOL;
            $contents .= '    const ' . strtoupper($snakeCaseEntityName) . '_URL_PATH_DUPLICATE = \''. $frontName . '/' . strtolower($entityName) . '/duplicate\';' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    protected UrlInterface $urlBuilder;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param ContextInterface $context' . PHP_EOL;
            $contents .= '     * @param UiComponentFactory $uiComponentFactory' . PHP_EOL;
            $contents .= '     * @param UrlInterface $urlBuilder' . PHP_EOL;
            $contents .= '     * @param array $components' . PHP_EOL;
            $contents .= '     * @param array $data' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        ContextInterface $context,' . PHP_EOL;
            $contents .= '        UiComponentFactory $uiComponentFactory,' . PHP_EOL;
            $contents .= '        UrlInterface $urlBuilder,' . PHP_EOL;
            $contents .= '        array $components = [],' . PHP_EOL;
            $contents .= '        array $data = []' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        $this->urlBuilder = $urlBuilder;' . PHP_EOL;
            $contents .= '        parent::__construct($context, $uiComponentFactory, $components, $data);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Prepare Data Source' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param array $dataSource' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function prepareDataSource(array $dataSource): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        if (isset($dataSource[\'data\'][\'items\'])) {' . PHP_EOL;
            $contents .= '            foreach ($dataSource[\'data\'][\'items\'] as & $item) {' . PHP_EOL;
            $contents .= '                // here we can also use the data from $item to configure some parameters of an action URL' . PHP_EOL;
            $contents .= '                $item[$this->getData(\'name\')] = [' . PHP_EOL;
            $contents .= '                    \'edit\' => [' . PHP_EOL;
            $contents .= '                        \'href\' => $this->urlBuilder->getUrl(self::' . strtoupper($snakeCaseEntityName) . '_URL_PATH_EDIT, [\'id\' => $item[\'id\']]),' . PHP_EOL;
            $contents .= '                        \'label\' => __(\'Edit\')' . PHP_EOL;
            $contents .= '                    ],' . PHP_EOL;
            $contents .= '                    \'remove\' => [' . PHP_EOL;
            $contents .= '                        \'href\' => $this->urlBuilder->getUrl(self::' . strtoupper($snakeCaseEntityName) . '_URL_PATH_DELETE, [\'id\' => $item[\'id\']]),' . PHP_EOL;
            $contents .= '                        \'label\' => __(\'Delete\'),' . PHP_EOL;
            $contents .= '                        \'confirm\' => [' . PHP_EOL;
            $contents .= '                            \'title\' => __(\'Delete ' . $title . '\'),//"${ $.$data.attachment_name }"' . PHP_EOL;
            $contents .= '                            \'message\' => __(\'Are you sure you wan\\\'t to delete this?\')//a "${ $.$data.name }"' . PHP_EOL;
            $contents .= '                        ]' . PHP_EOL;
            $contents .= '                    ],' . PHP_EOL;
            $contents .= '                    \'duplicate\' => [' . PHP_EOL;
            $contents .= '                        \'href\' => $this->urlBuilder->getUrl(self::' . strtoupper($snakeCaseEntityName) . '_URL_PATH_DUPLICATE, [\'id\' => $item[\'id\']]),' . PHP_EOL;
            $contents .= '                        \'label\' => __(\'Duplicate\')' . PHP_EOL;
            $contents .= '                    ],' . PHP_EOL;
            $contents .= '                ];' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        return $dataSource;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
            if ($this->filesystemIo->write($actionsFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    public function generateModelDataProviderFile($vendorNamespaceArr, $appFolderPath, $entityName, $dbColumns, $uiFormStyle)
    {
        $componentFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/Block';
        $this->filesystemIo->checkAndCreateFolder($componentFolder);
        $actionsFile = $componentFolder . '/' . 'DataProvider.php';
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($entityName);
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($actionsFile)) {
            $contents = '<?php' . PHP_EOL;
            $contents .= $this->helper->getSignature('DataProvider.php');
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Model\\Block;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'use Magento\\Framework\\App\\Request\\DataPersistorInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Serialize\\SerializerInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Ui\\DataProvider\\Modifier\\PoolInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Model\\ResourceModel\\' . $entityName . '\\CollectionFactory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Model\\ResourceModel\\' . $entityName . '\\Collection;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= 'class DataProvider extends \\Magento\\Ui\\DataProvider\\ModifierPoolDataProvider' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var Collection' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $collection;' . PHP_EOL;
            $contents .= '    protected DataPersistorInterface $dataPersistor;' . PHP_EOL;
            $contents .= '    protected array $loadedData;' . PHP_EOL;
            $contents .= '    private SerializerInterface $json;' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Constructor' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param string $name' . PHP_EOL;
            $contents .= '     * @param string $primaryFieldName' . PHP_EOL;
            $contents .= '     * @param string $requestFieldName' . PHP_EOL;
            $contents .= '     * @param CollectionFactory $' . $lowerCamelCaseEntityName . 'CollectionFactory' . PHP_EOL;
            $contents .= '     * @param DataPersistorInterface $dataPersistor' . PHP_EOL;
            $contents .= '     * @param SerializerInterface $json' . PHP_EOL;
            $contents .= '     * @param array $meta' . PHP_EOL;
            $contents .= '     * @param array $data' . PHP_EOL;
            $contents .= '     * @param PoolInterface|null $pool' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        $name,' . PHP_EOL;
            $contents .= '        $primaryFieldName,' . PHP_EOL;
            $contents .= '        $requestFieldName,' . PHP_EOL;
            $contents .= '        CollectionFactory $' . $lowerCamelCaseEntityName . 'CollectionFactory,' . PHP_EOL;
            $contents .= '        DataPersistorInterface $dataPersistor,' . PHP_EOL;
            $contents .= '        SerializerInterface $json,' . PHP_EOL;
            $contents .= '        array $meta = [],' . PHP_EOL;
            $contents .= '        array $data = [],' . PHP_EOL;
            $contents .= '        PoolInterface $pool = null' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);' . PHP_EOL;
            $contents .= '        $this->collection = $' . $lowerCamelCaseEntityName . 'CollectionFactory->create();' . PHP_EOL;
            $contents .= '        $this->dataPersistor = $dataPersistor;' . PHP_EOL;
            $contents .= '        $this->json = $json;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getData(): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        if (isset($this->loadedData)) {' . PHP_EOL;
            $contents .= '            return $this->loadedData;' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        $this->loadedData = [];' . PHP_EOL;
            $contents .= '        $items = $this->collection->getItems();' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        foreach ($items as $item) {' . PHP_EOL;
            if ($uiFormStyle === '2') {
                $contents .= $this->getSeparateFieldsetProvider($dbColumns);
            } else {
                $contents .= $this->getSingleFieldsetProvider($dbColumns);
            }
            $contents .= '        }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '//        $data = $this->dataPersistor->get(\'to_be_added_soon\');' . PHP_EOL;
            $contents .= '//        if (!empty($data)) {' . PHP_EOL;
            $contents .= '//            $item = $this->collection->getNewEmptyItem();' . PHP_EOL;
            $contents .= '//            $item->setData($data);' . PHP_EOL;
            $contents .= '//            $this->loadedData[$item->getId()] = $item->getData();' . PHP_EOL;
            $contents .= '//            $this->dataPersistor->clear(\'to_be_added_soon\');' . PHP_EOL;
            $contents .= '//        }' . PHP_EOL;
            $contents .= PHP_EOL;
            $contents .= '        return $this->loadedData;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= PHP_EOL;
            if ($this->filesystemIo->write($actionsFile, $contents)) {
                return true;
            }
            return false;
        } else {
            return true;
        }
    }

    private function getSeparateFieldsetProvider($dbColumns)
    {
        $contents = '';
        //sort columns by fieldset, just to make it more organized
        usort($dbColumns, function ($a, $b) { return $a['backend_fieldset'] <=> $b['backend_fieldset']; });
        $firstKey = array_key_first($dbColumns);
        $contents .= '            $this->loadedData[$item->getId()][\'' . $dbColumns[$firstKey]['backend_fieldset'] . '\'][\'id\'] = $item->getId();' . PHP_EOL;
        $contents .= '            $this->loadedData[$item->getId()][\'' . $dbColumns[$firstKey]['backend_fieldset'] . '\'][\'store_id\'] = $item->getData()[\'store_id\'];' . PHP_EOL;
        foreach ($dbColumns as $column) {
            if ($column['backend_type'] === 'imageUploader' || $column['backend_type'] === 'fileUploader' || $column['backend_type'] === 'dynamicRow') {
                $contents .= '            $this->loadedData[$item->getId()][\'' . $column['backend_fieldset'] . '\'][\'' . $column['name'] . '\'] = $this->json->unserialize($item->getData()[\'' . $column['name'] . '\']);' . PHP_EOL;
            } else {
                $contents .= '            $this->loadedData[$item->getId()][\'' . $column['backend_fieldset'] . '\'][\'' . $column['name'] . '\'] = $item->getData()[\'' . $column['name'] . '\'];' . PHP_EOL;
            }
        }
        return $contents;
    }

    private function getSingleFieldsetProvider($dbColumns)
    {
        $serializedColumns = [];
        foreach ($dbColumns as $column) {
            if (in_array($column['backend_type'], ['imageUploader', 'fileUploader', 'dynamicRow'])) {
                $serializedColumns[] = "'" . $column['name'] . "'";
            }
        }
        $contents = '             foreach ($item->getData() as $index => $value) {' . PHP_EOL;
        if (sizeof($serializedColumns)) {
            if (sizeof($serializedColumns) > 1) {
                $contents .= '                 if (in_array($index, [' . implode(', ', $serializedColumns) . '])) {' . PHP_EOL;
            } else {
                $contents .= '                 if ($index === ' . $serializedColumns[0] . ') {' . PHP_EOL;
            }
            $contents .= '                    $this->loadedData[$item->getId()][$index] = $this->json->unserialize($value);' . PHP_EOL;
            $contents .= '                 } else {' . PHP_EOL;
            $contents .= '                    $this->loadedData[$item->getId()][$index] = $value;' . PHP_EOL;
            $contents .= '                 }' . PHP_EOL;
        } else {
            $contents .= '                 $this->loadedData[$item->getId()][$index] = $value;' . PHP_EOL;
        }
        $contents .= '            }' . PHP_EOL;
        return $contents;
    }
}
