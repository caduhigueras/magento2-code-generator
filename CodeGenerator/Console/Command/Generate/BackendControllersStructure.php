<?php
/**
 * TODO: Add data persistor to files
 */
namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use CodeBaby\CodeGenerator\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class BackendControllersStructure
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
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $dbColumns
     * @param $dbName
     * @param $frontName
     * @param $menuPosition
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function generateBackendRoutesAndControllers($vendorNamespaceArr, $entityName, $dbColumns, $dbName, $frontName, $menuPosition)
    {
        $result = [];
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        if (!$this->generateRouteXml($appFolderPath, $vendorNamespaceArr, $entityName, $frontName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create routes.xml file';
            return $result;
        }
        if (!$this->generateMenuXml($appFolderPath, $vendorNamespaceArr, $entityName, $frontName, $menuPosition)) {
            $result['success'] = false;
            $result['message'] = 'Could not create menu.xml file';
            return $result;
        }
        if (!$this->generateIndexIndexController($appFolderPath, $vendorNamespaceArr)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Index/Index.php file';
            return $result;
        }
        if (!$this->generateEntityAddController($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/Add.php file';
            return $result;
        }
        if (!$this->generateEntityDeleteController($appFolderPath, $vendorNamespaceArr, $entityName, $frontName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/Delete.php file';
            return $result;
        }
        if (!$this->generateEntityDuplicateController($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/Duplicate.php file';
            return $result;
        }
        if (!$this->generateEntityEditController($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/Edit.php file';
            return $result;
        }
        if (!$this->generateEntitySaveController($appFolderPath, $vendorNamespaceArr, $entityName, $frontName, $dbColumns)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/Save.php file';
            return $result;
        }
        if (!$this->generateEntityMassDeleteController($appFolderPath, $vendorNamespaceArr, $entityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not create Entity/MassDelete.php file';
            return $result;
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @param $dbColumns
     * @return bool
     */
    public function generateEntityMassDeleteController($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        $controllerFile = $entityControllerFolder . '/' . 'Save.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\App\\Action;' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\Model\\View\\Result\\Redirect;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\Result\\JsonFactory;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\CouldNotDeleteException;' . PHP_EOL;
            $contents .= 'use Magento\\Ui\\Component\\MassAction\\Filter;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . $entityName . 'RepositoryInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Factory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class MassDelete extends Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'RepositoryInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Factory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $' . $lowerCamelCaseEntityName . ';' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var Filter' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $filter;' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var JsonFactory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $resultJsonFactory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        Action\Context $context,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'RepositoryInterface $' . $lowerCamelCaseEntityName . 'Repository,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . ',' . PHP_EOL;
            $contents .= '        Filter $filter,' . PHP_EOL;
            $contents .= '        JsonFactory $resultJsonFactory' . PHP_EOL;
            $contents .= '    )' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        parent::__construct($context);' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . ' = $' . $lowerCamelCaseEntityName . ';' . PHP_EOL;
            $contents .= '        $this->filter = $filter;' . PHP_EOL;
            $contents .= '        $this->resultJsonFactory = $resultJsonFactory;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @inheritDoc' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function execute(): Json' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $resultJson = $this->resultJsonFactory->create();' . PHP_EOL;
            $contents .= '        $selectedToDelete = $this->getRequest()->getPost()[\'selected\'];' . PHP_EOL;
            $contents .= '        foreach ($selectedToDelete as $id) {' . PHP_EOL;
            $contents .= '            try {' . PHP_EOL;
            $contents .= '                $this->' . $lowerCamelCaseEntityName . 'Repository->deleteById($id);' . PHP_EOL;
            $contents .= '            } catch (CouldNotDeleteException $e) {' . PHP_EOL;
            $contents .= '                $resultJson->setData(' . PHP_EOL;
            $contents .= '                    [' . PHP_EOL;
            $contents .= '                        \'message\' => __($e->getMessage()),' . PHP_EOL;
            $contents .= '                        \'error\' => true,' . PHP_EOL;
            $contents .= '                        \'codebaby_grid_submit_delete\' => true' . PHP_EOL;
            $contents .= '                    ]' . PHP_EOL;
            $contents .= '                );' . PHP_EOL;
            $contents .= '                return $resultJson;' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        $resultJson->setData(' . PHP_EOL;
            $contents .= '            [' . PHP_EOL;
            $contents .= '                \'message\' => __(\'Prices deleted successfully\'),' . PHP_EOL;
            $contents .= '                \'error\' => false,' . PHP_EOL;
            $contents .= '                \'codebaby_grid_submit_delete\' => true' . PHP_EOL;
            $contents .= '            ]' . PHP_EOL;
            $contents .= '        );' . PHP_EOL;
            $contents .= '        return $resultJson;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .='' . PHP_EOL;
            if ($this->filesystemIo->write($controllerFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @param $dbColumns
     * @return bool
     */
    public function generateEntitySaveController($appFolderPath, $vendorNamespaceArr, $entityName, $frontName, $dbColumns)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        $controllerFile = $entityControllerFolder . '/' . 'Save.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\App\\Request\\DataPersistorInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\LocalizedException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Serialize\\Serializer\\JsonFactory as JsonSerializer;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName .'\\' . $entityName . 'Factory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName .'\\' . $entityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'class Save extends \\Magento\\Backend\\App\\Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ADMIN_RESOURCE = \'' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '\';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Factory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var FileProcessor' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $fileProcessor;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var JsonSerializer' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $json;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var DataPersistorInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $dataPersistor;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var \Magento\Backend\App\Action\Context' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $context;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        FileProcessor $fileProcessor,' . PHP_EOL;
            $contents .= '        \Magento\Backend\App\Action\Context $context,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . 'Factory,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository,' . PHP_EOL;
            $contents .= '        DataPersistorInterface $dataPersistor,' . PHP_EOL;
            $contents .= '        JsonSerializer $json' . PHP_EOL;
            $contents .= '    )' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->context = $context;' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Factory = $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .= '        $this->fileProcessor = $fileProcessor;' . PHP_EOL;
            $contents .= '        $this->json = $json->create();' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '        $this->dataPersistor = $dataPersistor;' . PHP_EOL;
            $contents .= '        parent::__construct($context);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */' . PHP_EOL;
            $contents .= '        $resultRedirect = $this->resultRedirectFactory->create();' . PHP_EOL;
            $contents .= '        $data = $this->getRequest()->getPostValue();' . PHP_EOL;
            $contents .= '        if ($data) {' . PHP_EOL;
            $contents .= '            $' . $lowerCamelCaseEntityName . 'Data = [];' . PHP_EOL;
            $contents .= '            if (empty($data[\'' . $dbColumns[0]['backend_fieldset'] . '\'][\'id\'])) {' . PHP_EOL;
            $contents .= '                $' . $lowerCamelCaseEntityName . 'Data[\'id\'] = null;' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '            //iterate through fieldSets and assign them to the $' . $lowerCamelCaseEntityName . 'Data[]' . PHP_EOL;
            $contents .= '            $fieldSets = [';
            foreach ($dbColumns as $column) {
                $contents .= '              \'' . $column['backend_fieldset'] . '\',';
            }
            $contents .= '            ];' . PHP_EOL;
            $contents .= '            foreach ($fieldSets as $fieldset) {' . PHP_EOL;
            $contents .= '                foreach ($data[$fieldset] as $field => $value) {' . PHP_EOL;
            $contents .= '                    $' . $lowerCamelCaseEntityName . '[$field] = $value;' . PHP_EOL;
            $contents .= '                }' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            //TODO: add serializer for dynamic rows / files
            $contents .= '            $' . $lowerCamelCaseEntityName . 'Model = $this->' . $lowerCamelCaseEntityName . 'Factory->create();' . PHP_EOL;
            $contents .= '            if ($' . $lowerCamelCaseEntityName . 'Data[\'id\']) {' . PHP_EOL;
            $contents .= '                try {' . PHP_EOL;
            $contents .= '                    //if form already exists, use it' . PHP_EOL;
            $contents .= '                    $' . $lowerCamelCaseEntityName . 'Model = $this->' . $lowerCamelCaseEntityName . 'Repository->getById($' . $lowerCamelCaseEntityName . 'Data[\'id\']);' . PHP_EOL;
            $contents .= '                } catch (LocalizedException $e) {' . PHP_EOL;
            $contents .= '                    $this->messageManager->addErrorMessage(__(\'This ' . $title . ' no longer exists.\'));' . PHP_EOL;
            $contents .= '                    return $resultRedirect->setPath(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '                }' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .= '            $' . $lowerCamelCaseEntityName . 'Model->setData($' . $lowerCamelCaseEntityName . 'Data);' . PHP_EOL;
            $contents .= '            try {' . PHP_EOL;
            $contents .= '                $this->' . $lowerCamelCaseEntityName . 'Repository->save($' . $lowerCamelCaseEntityName . 'Model);' . PHP_EOL;
            $contents .= '                $this->messageManager->addSuccessMessage(__(\'' . $title . ' successfully saved.\'));' . PHP_EOL;
            $contents .= '                return $this->process' . $entityName . 'Return($' . $lowerCamelCaseEntityName . 'Model, $' . $lowerCamelCaseEntityName . 'Data, $resultRedirect);' . PHP_EOL;
            $contents .= '            } catch (LocalizedException $e) {' . PHP_EOL;
            $contents .= '                $this->messageManager->addErrorMessage($e->getMessage());' . PHP_EOL;
            $contents .= '            } catch (\Exception $e) {' . PHP_EOL;
            $contents .= '                $this->messageManager->addExceptionMessage($e, __(\'Something went wrong while saving the ' . $title . '.\'));' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '            return $resultRedirect->setPath(\'*/*/edit\', [\'id\' => $' . $lowerCamelCaseEntityName . 'Data[\'id\']]);' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $resultRedirect->setPath(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    public function process' . $entityName . 'Return($model, $data, $resultRedirect)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $redirect = $this->getRequest()->getParam(\'back\');' . PHP_EOL;
            $contents .= '        if ($redirect === \'edit\') {' . PHP_EOL;
            $contents .= '            $resultRedirect->setPath(\'*/*/edit\', [\'id\' => $model->getId()]);' . PHP_EOL;
            $contents .= '        } else {' . PHP_EOL;
            $contents .= '            $resultRedirect->setPath(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $resultRedirect;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .='' . PHP_EOL;
            if ($this->filesystemIo->write($controllerFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @return bool
     */
    public function generateEntityEditController($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        $controllerFile = $entityControllerFolder . '/' . 'Edit.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\App\\Action;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultFactory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'class Edit extends \\Magento\\Backend\\App\\Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ADMIN_RESOURCE = \'' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '\';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Edit constructor.' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '     * @param Action\Context $context' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository,' . PHP_EOL;
            $contents .= '        Action\Context $context' . PHP_EOL;
            $contents .= '    )' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '        parent::__construct($context);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $id = $this->getRequest()->getParam(\'id\');' . PHP_EOL;
            $contents .= '        ($id) ? $' . $lowerCamelCaseEntityName . ' = $this->' . $lowerCamelCaseEntityName . 'Repository->getById($id) : $' . $lowerCamelCaseEntityName . ' = null;' . PHP_EOL;
            $contents .= '        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);' . PHP_EOL;
            $contents .= '        $resultPage->getConfig()->getTitle()->prepend($' . $lowerCamelCaseEntityName . ' ? $' . $lowerCamelCaseEntityName . '->getTitle() : __(\'Edit ' . $entityName . '\'));' . PHP_EOL;
            $contents .= '        return $resultPage;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .='' . PHP_EOL;
            if ($this->filesystemIo->write($controllerFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @return bool
     */
    public function generateEntityDuplicateController($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        $controllerFile = $entityControllerFolder . '/' . 'Duplicate.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\App\\Action;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultFactory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Factory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= 'class Duplicate extends \\Magento\\Backend\\App\\Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ADMIN_RESOURCE = \'' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '\';' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Factory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Edit constructor.' . PHP_EOL;
            $contents .= '     * @param Action\Context $context' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . 'Factory' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        Action\Context $context,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . 'Factory,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '    )' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Factory = $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '        parent::__construct($context);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface' . PHP_EOL;
            $contents .= '     * @throws \Magento\Framework\Exception\CouldNotSaveException' . PHP_EOL;
            $contents .= '     * @throws \Magento\Framework\Exception\NoSuchEntityException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $resultRedirect = $this->resultRedirectFactory->create();' . PHP_EOL;
            $contents .= '        $duplicateId = $this->getRequest()->getParam(\'id\');' . PHP_EOL;
            $contents .= '        $' . $lowerCamelCaseEntityName . 'ToDuplicate = $this->' . $lowerCamelCaseEntityName . 'Repository->getById($duplicateId);' . PHP_EOL;
            $contents .= '        $data = $customFormToDuplicate->getData();' . PHP_EOL;
            $contents .= '        $' . $lowerCamelCaseEntityName . 'Model = $this->' . $lowerCamelCaseEntityName . 'Factory->create([\'data\' => $data]);' . PHP_EOL;
            $contents .= '        $' . $lowerCamelCaseEntityName . 'Model->setId(null);' . PHP_EOL;
            $contents .= '        $' . $lowerCamelCaseEntityName . 'Model->setKey($data[\'key\'] . \'-new-\' . uniqid());' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository->save($' . $lowerCamelCaseEntityName . 'Model);' . PHP_EOL;
            $contents .= '        $id = $c' . $lowerCamelCaseEntityName . 'Model->getId();' . PHP_EOL;
            $contents .= '        $this->messageManager->addSuccessMessage(__(\'You have duplicated the ' . $title . '.\'));' . PHP_EOL;
            $contents .= '        return $resultRedirect->setPath(\'*/*/edit\', [\'id\' => $id]);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .='' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .='' . PHP_EOL;
            if ($this->filesystemIo->write($controllerFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @return bool
     */
    public function generateEntityDeleteController($appFolderPath, $vendorNamespaceArr, $entityName, $frontName)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        $controllerFile = $entityControllerFolder . '/' . 'Delete.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\App\\Action;' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\App\\Action\\Context;' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\Model\\View\\Result\\Redirect;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Factory;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Model' . '\\' . $entityName . '\\' . $entityName . 'Repository;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class Delete extends Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ADMIN_RESOURCE = \'' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '\';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Factory' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    protected $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @var ' . $entityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    private $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Delete constructor.' . PHP_EOL;
            $contents .= '     * @param Context $context' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . 'Factory' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(Context $context, ' . $entityName . 'Factory $' . $lowerCamelCaseEntityName . 'Factory, ' . $entityName . 'Repository $' . $lowerCamelCaseEntityName . 'Repository)' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Factory = $' . $lowerCamelCaseEntityName . 'Factory;' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '        parent::__construct($context);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Delete action' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return ResultInterface' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $id = $this->getRequest()->getParam(\'id\');' . PHP_EOL;
            $contents .= '        /** @var Redirect $resultRedirect */' . PHP_EOL;
            $contents .= '        $resultRedirect = $this->resultRedirectFactory->create();' . PHP_EOL;
            $contents .= '        if ($id) {' . PHP_EOL;
            $contents .= '            try {' . PHP_EOL;
            $contents .= '                $this->' . $lowerCamelCaseEntityName . 'Repository->deleteById($id);' . PHP_EOL;
            $contents .= '                $this->messageManager->addSuccessMessage(__(\'The ' . $title . ' has been deleted.\'));' . PHP_EOL;
            $contents .= '                return $resultRedirect->setPath(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '            } catch (\Exception $e) {' . PHP_EOL;
            $contents .= '                $this->messageManager->addErrorMessage($e->getMessage());' . PHP_EOL;
            $contents .= '                return $resultRedirect->setPath(\'*/*/edit\', [\'id\' => $id]);' . PHP_EOL;
            $contents .= '            }' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        $this->messageManager->addErrorMessage(__(\'We can\\\'t find this ' . $title . ' to delete.\'));' . PHP_EOL;
            $contents .= '        return $resultRedirect->setPath(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
        if ($this->filesystemIo->write($controllerFile, $contents)) {
            return true;
        }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @return bool
     */
    public function generateEntityAddController($appFolderPath, $vendorNamespaceArr, $entityName)
    {
        $entityControllerFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/' . $entityName;
        try {
            $this->filesystemIo->checkAndCreateFolder($entityControllerFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $controllerFile = $entityControllerFolder . '/' . 'Add.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($controllerFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller' . '\\' . 'Adminhtml' . '\\' . $entityName . ';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultFactory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class Add extends \\Magento\\Backend\\App\\Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    const ADMIN_RESOURCE = \'' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '\';' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);' . PHP_EOL;
            $contents .= '        $resultPage->getConfig()->getTitle()->prepend(__(\'' . $title . ' - Add New ' . $title . '\'));' . PHP_EOL;
            $contents .= '        return $resultPage;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($controllerFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @return bool
     */
    public function generateIndexIndexController($appFolderPath, $vendorNamespaceArr)
    {
        $controllerIndexFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminhtml/Index';
        try {
            $this->filesystemIo->checkAndCreateFolder($controllerIndexFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $indexFile = $controllerIndexFolder . '/' . 'Index.php';
        if (!$this->filesystemIo->fileExists($indexFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Controller\\Adminhtml\\Index;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Controller\\ResultFactory;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class Index extends \\Magento\\Backend\\App\\Action' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    public function execute()' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            if ($this->filesystemIo->write($indexFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }


    public function generateEntityUploadController()
    {

    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @param $menuPosition
     * @return bool
     */
    public function generateMenuXml($appFolderPath, $vendorNamespaceArr, $entityName, $frontName, $menuPosition)
    {
        $etcFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc/adminhtml';
        $menuFile = $etcFolder . '/' . 'menu.xml';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($menuFile)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Backend:etc/menu.xsd">' . PHP_EOL;
            $contents .= '    <menu>' . PHP_EOL;
            /**
             * if menu item is menu_root - goes to new backend menu
             * if Magento_Backend::content | Magento_Catalog::catalog - add subtitle section
             * Others just add to the parent
             */
            if ($menuPosition === 'menu_root') {
                $contents .= '<add id="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" title="' . $title . '" 
                module="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '" resource="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" 
                translate="title" sortOrder="900" action="' . $frontName . '"/>';
            } elseif ($menuPosition === 'Magento_Backend::content' || $menuPosition === 'Magento_Catalog::catalog') {
                $contents .= '        <add id="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" title="' . $title . ' Menu' . '" 
                translate="title" module="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '" sortOrder="100" parent="'. $menuPosition . '" 
                resource="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" />' . PHP_EOL;
                $contents .= '        <add id="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '_item" title="Custom Forms" translate="' . $title . '" 
                module="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '" sortOrder="0" parent="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" 
                action="' . $frontName . '" resource="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" />' . PHP_EOL;
            } else {

                $contents .= '        <add id="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" title="' . $title . '" 
                translate="title" module="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '" sortOrder="200" parent="' . $menuPosition . '" action="' . $frontName . '" 
                resource="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '::' . $snakeCaseEntityName . '" />' . PHP_EOL;
            }
            $contents .= '    </menu>' . PHP_EOL;
            $contents .= '</config>' . PHP_EOL;
            if ($this->filesystemIo->write($menuFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $appFolderPath
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $frontName
     * @return bool
     */
    public function generateRouteXml($appFolderPath, $vendorNamespaceArr, $entityName, $frontName)
    {
        $etcFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc/adminhtml';
        try {
            $this->filesystemIo->checkAndCreateFolder($etcFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $routesFile = $etcFolder . '/' . 'routes.xml';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        if (!$this->filesystemIo->fileExists($routesFile)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<config xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:App/etc/routes.xsd">' . PHP_EOL;
            $contents .= '    <router id="admin">' . PHP_EOL;
            $contents .= '        <route id="' . $snakeCaseEntityName . '" frontName="' . $frontName . '">' . PHP_EOL;
            $contents .= '            <module name="' . $vendorNamespaceArr[0] . '_' . $vendorNamespaceArr[1] . '"/>' . PHP_EOL;
            $contents .= '        </route>' . PHP_EOL;
            $contents .= '    </router>' . PHP_EOL;
            $contents .= '</config>' . PHP_EOL;
            if ($this->filesystemIo->write($routesFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}