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

class BackendBlocksStructure
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

    public function generateBlockFiles($vendorNamespaceArr, $entityName, $frontName)
    {
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $blockFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/Edit';
        try {
            $this->filesystemIo->checkAndCreateFolder($blockFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        if (!$this->generateGenericButtonFile($vendorNamespaceArr, $entityName, $blockFolder)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate GenericButton.php file';
            return $result;
        }
        if (!$this->generateBackButtonFile($vendorNamespaceArr, $entityName, $blockFolder, $frontName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate BackButton.php file';
            return $result;
        }
        if (!$this->generateDeleteButtonFile($vendorNamespaceArr, $entityName, $blockFolder)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate DeleteButton.php file';
            return $result;
        }
        if (!$this->generateSaveButtonFile($vendorNamespaceArr, $entityName, $blockFolder)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate SaveButton.php file';
            return $result;
        }
        if (!$this->generateSaveAndContinueButtonFile($vendorNamespaceArr, $entityName, $blockFolder)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate SaveAndContinueButton.php file';
            return $result;
        }
        $result['success'] = true;
        return $result;
    }

    /**
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $blockFolder
     * @return bool
     */
    public function generateSaveAndContinueButtonFile($vendorNamespaceArr, $entityName, $blockFolder)
    {
        $saveAndContinueButtonFile = $blockFolder . '/' . 'SaveAndContinueButton.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($saveAndContinueButtonFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\Control\\ButtonProviderInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Ui\\Component\\Control\\Container;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class SaveAndContinueButton extends GenericButton implements ButtonProviderInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getButtonData(): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return [' . PHP_EOL;
            $contents .= '            \'label\' => __(\'Save And Continue Edit\'),' . PHP_EOL;
            $contents .= '            \'class\' => \'save\',' . PHP_EOL;
            $contents .= '            \'data_attribute\' => [' . PHP_EOL;
            $contents .= '                \'button\' => [\'event\' => \'saveAndContinueEdit\'],' . PHP_EOL;
            $contents .= '            ],' . PHP_EOL;
            $contents .= '        ];' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($saveAndContinueButtonFile, $contents)) {
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
     * @param $entityName
     * @param $blockFolder
     * @return bool
     */
    public function generateSaveButtonFile($vendorNamespaceArr, $entityName, $blockFolder)
    {
        $saveButtonFile = $blockFolder . '/' . 'SaveButton.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($saveButtonFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\Control\\ButtonProviderInterface;' . PHP_EOL;
            $contents .= 'use Magento\\Ui\\Component\\Control\\Container;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class SaveButton extends GenericButton implements ButtonProviderInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getButtonData(): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return [' . PHP_EOL;
            $contents .= '            \'label\' => __(\'Save ' . $title . '\'),' . PHP_EOL;
            $contents .= '            \'class\' => \'save primary\',' . PHP_EOL;
            $contents .= '            \'data_attribute\' => [' . PHP_EOL;
            $contents .= '                \'mage-init\' => [\'button\' => [\'event\' => \'save\']],' . PHP_EOL;
            $contents .= '                \'form-role\' => \'save\',' . PHP_EOL;
            $contents .= '            ]' . PHP_EOL;
            $contents .= '        ];' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($saveButtonFile, $contents)) {
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
     * @param $entityName
     * @param $blockFolder
     * @return bool
     */
    public function generateDeleteButtonFile($vendorNamespaceArr, $entityName, $blockFolder)
    {
        $deleteButtonFile = $blockFolder . '/' . 'DeleteButton.php';
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));
        if (!$this->filesystemIo->fileExists($deleteButtonFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\LocalizedException' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\Control\\ButtonProviderInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit' . '\\' . 'GenericButton as CustomGenericButton;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class DeleteButton extends CustomGenericButton implements ButtonProviderInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     * @throws LocalizedException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getButtonData(): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        $data = [];' . PHP_EOL;
            $contents .= '        if ($this->get' . $entityName . 'Id()) {' . PHP_EOL;
            $contents .= '            $data = [' . PHP_EOL;
            $contents .= '                \'label\' => __(\'Delete ' . $title . '\'),' . PHP_EOL;
            $contents .= '                \'class\' => \'delete\',' . PHP_EOL;
            $contents .= '                \'on_click\' => \'deleteConfirm(\\\'\' . __(' . PHP_EOL;
            $contents .= '                  \'Are you sure you want to do this?\'' . PHP_EOL;
            $contents .= '                ) . \'\\\', \\\'\' . $this->getDeleteUrl() . \'\\\', {"data": {}})\',' . PHP_EOL;
            $contents .= '                \'sort_order\' => 20,' . PHP_EOL;
            $contents .= '            ];' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return $data;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * URL to send delete requests to.' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return string' . PHP_EOL;
            $contents .= '     * @throws LocalizedException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getDeleteUrl(): string' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->getUrl(\'*/*/delete\', [\'id\' => $this->get' . $entityName . 'Id()]);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($deleteButtonFile, $contents)) {
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
     * @param $entityName
     * @param $blockFolder
     * @param $frontName
     * @return bool
     */
    public function generateBackButtonFile($vendorNamespaceArr, $entityName, $blockFolder, $frontName)
    {
        $backButtonFile = $blockFolder . '/' . 'BackButton.php';
        if (!$this->filesystemIo->fileExists($backButtonFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\View\\Element\\UiComponent\\Control\\ButtonProviderInterface;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit' . '\\' . 'GenericButton as CustomGenericButton;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '/**' . PHP_EOL;
            $contents .= ' * Class BackButton' . PHP_EOL;
            $contents .= ' */' . PHP_EOL;
            $contents .= 'class BackButton extends CustomGenericButton implements ButtonProviderInterface' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @return array' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getButtonData(): array' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return [' . PHP_EOL;
            $contents .= '            \'label\' => __(\'Back\'),' . PHP_EOL;
            $contents .= '            \'on_click\' => sprintf("location.href = \'%s\';", $this->getBackUrl()),' . PHP_EOL;
            $contents .= '            \'class\' => \'back\',' . PHP_EOL;
            $contents .= '            \'sort_order\' => 10' . PHP_EOL;
            $contents .= '        ];' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Get URL for back button' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return string' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getBackUrl(): string' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->getUrl(\'' . $frontName . '/index/index\');' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($backButtonFile, $contents)) {
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
     * @param $entityName
     * @param $blockFolder
     * @return bool
     */
    public function generateGenericButtonFile($vendorNamespaceArr, $entityName, $blockFolder)
    {
        $genericButtonFile = $blockFolder . '/' . 'GenericButton.php';
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($entityName);
        if (!$this->filesystemIo->fileExists($genericButtonFile)){
            $contents = '<?php' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'namespace ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Block' . '\\' . 'Adminhtml' . '\\' . $entityName . '\\' . 'Edit;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'use Magento\\Backend\\Block\\Widget\\Context;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\LocalizedException;' . PHP_EOL;
            $contents .= 'use Magento\\Framework\\Exception\\NoSuchEntityException;' . PHP_EOL;
            $contents .= 'use ' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\' . 'Api' . '\\' . $entityName . 'RepositoryInterface;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= 'class GenericButton' . PHP_EOL;
            $contents .= '{' . PHP_EOL;
            $contents .= '    protected Context $context;' . PHP_EOL;
            $contents .= '    protected ' . $entityName . 'RepositoryInterface $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * @param Context $context' . PHP_EOL;
            $contents .= '     * @param ' . $entityName . 'RepositoryInterface $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function __construct(' . PHP_EOL;
            $contents .= '        Context $context,' . PHP_EOL;
            $contents .= '        ' . $entityName . 'RepositoryInterface $' . $lowerCamelCaseEntityName . 'Repository' . PHP_EOL;
            $contents .= '    ) {' . PHP_EOL;
            $contents .= '        $this->context = $context;' . PHP_EOL;
            $contents .= '        $this->' . $lowerCamelCaseEntityName . 'Repository = $' . $lowerCamelCaseEntityName . 'Repository;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @return int|null' . PHP_EOL;
            $contents .= '     * @throws LocalizedException' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function get' . $entityName . 'Id(): ?int' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        try {' . PHP_EOL;
            $contents .= '            return (int) $this->' . $lowerCamelCaseEntityName . 'Repository->getById(' . PHP_EOL;
            $contents .= '                $this->context->getRequest()->getParam(\'id\')' . PHP_EOL;
            $contents .= '            )->getId();' . PHP_EOL;
            $contents .= '        } catch (NoSuchEntityException $e) {' . PHP_EOL;
            $contents .= '        }' . PHP_EOL;
            $contents .= '        return null;' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            $contents .= '    /**' . PHP_EOL;
            $contents .= '     * Generate url by route and parameters' . PHP_EOL;
            $contents .= '     *' . PHP_EOL;
            $contents .= '     * @param   string $route' . PHP_EOL;
            $contents .= '     * @param   array $params' . PHP_EOL;
            $contents .= '     * @return  string' . PHP_EOL;
            $contents .= '     */' . PHP_EOL;
            $contents .= '    public function getUrl($route = \'\', $params = []): string' . PHP_EOL;
            $contents .= '    {' . PHP_EOL;
            $contents .= '        return $this->context->getUrlBuilder()->getUrl($route, $params);' . PHP_EOL;
            $contents .= '    }' . PHP_EOL;
            $contents .= '}' . PHP_EOL;
            $contents .= '' . PHP_EOL;
            if ($this->filesystemIo->write($genericButtonFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}
