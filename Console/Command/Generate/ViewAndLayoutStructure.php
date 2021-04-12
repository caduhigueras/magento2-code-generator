<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate;

use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\CheckboxType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\ColorPickerType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\DynamicRowType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\FileUploaderType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\ImageUploaderType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\MultiSelectType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\SelectType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\TextAreaType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\TextType;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm\WysiwygType;
use CodeBaby\CodeGenerator\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Filesystem\Io\File as FileIo;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;

class ViewAndLayoutStructure
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
    /**
     * @var CheckboxType
     */
    private $checkboxType;
    /**
     * @var ColorPickerType
     */
    private $colorPickerType;
    /**
     * @var DynamicRowType
     */
    private $dynamicRowType;
    /**
     * @var FileUploaderType
     */
    private $fileUploaderType;
    /**
     * @var ImageUploaderType
     */
    private $imageUploaderType;
    /**
     * @var MultiSelectType
     */
    private $multiSelectType;
    /**
     * @var SelectType
     */
    private $selectType;
    /**
     * @var TextAreaType
     */
    private $textAreaType;
    /**
     * @var TextType
     */
    private $textType;
    /**
     * @var WysiwygType
     */
    private $wysiwygType;

    public function __construct
    (
        ManagerInterface $messageManager,
        Filesystem $filesystem,
        UploaderFactory $fileUploader,
        ResourceConnection $resource,
        File $file,
        FileIo $filesystemIo,
        UrlInterface $urlBuilder,
        Data $helper,
        CheckboxType $checkboxType,
        ColorPickerType $colorPickerType,
        DynamicRowType $dynamicRowType,
        FileUploaderType $fileUploaderType,
        ImageUploaderType $imageUploaderType,
        MultiSelectType $multiSelectType,
        SelectType $selectType,
        TextAreaType $textAreaType,
        TextType $textType,
        WysiwygType $wysiwygType
    ) {
        $this->messageManager = $messageManager;
        $this->filesystem = $filesystem;
        $this->fileUploader = $fileUploader;
        $this->resource = $resource;
        $this->file = $file;
        $this->filesystemIo = $filesystemIo;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->checkboxType = $checkboxType;
        $this->colorPickerType = $colorPickerType;
        $this->dynamicRowType = $dynamicRowType;
        $this->fileUploaderType = $fileUploaderType;
        $this->imageUploaderType = $imageUploaderType;
        $this->multiSelectType = $multiSelectType;
        $this->selectType = $selectType;
        $this->textAreaType = $textAreaType;
        $this->textType = $textType;
        $this->wysiwygType = $wysiwygType;
    }

    /**
     * @param $vendorNamespaceArr
     * @param $entityName
     * @param $dbColumns
     * @param $frontName
     * @param $uiFormStyle
     * @return array
     */
    public function generateViewAndLayoutFiles($vendorNamespaceArr, $entityName, $dbColumns, $frontName, $uiFormStyle)
    {
        $result = [];
        $appFolder = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::APP);
        $appFolderPath = $appFolder->getAbsolutePath();
        $viewLayoutFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/layout';
        $viewUiComponentFolder = $appFolderPath . 'code' . '/' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/ui_component';
        try {
            $this->filesystemIo->checkAndCreateFolder($viewLayoutFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        try {
            $this->filesystemIo->checkAndCreateFolder($viewUiComponentFolder);
        } catch (\Exception $e) {
            $result['success'] = false;
            $result['message'] = $e->getMessage();
            return $result;
        }
        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $lowerCamelCaseEntityName = $this->helper->convertToLowerCamelCase($snakeCaseEntityName);
        $title = ucwords(str_replace('_', ' ', $snakeCaseEntityName));

        $addFile = $viewLayoutFolder . '/' . $snakeCaseEntityName . '_' . strtolower($entityName) . '_add.xml';
        $editFile = $viewLayoutFolder . '/' . $snakeCaseEntityName . '_' . strtolower($entityName) . '_edit.xml';
        $indexFile = $viewLayoutFolder . '/' . $snakeCaseEntityName . '_index_index.xml';

        if (!$this->generateLayoutAddFile($addFile, $snakeCaseEntityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Layout Add File';
            return $result;
        }
        if (!$this->generateLayoutEditFile($editFile, $snakeCaseEntityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Layout Edit File';
            return $result;
        }
        if (!$this->generateLayoutIndexFile($indexFile, $snakeCaseEntityName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Layout Index File';
            return $result;
        }
        $gridFile = $viewUiComponentFolder . '/' . $snakeCaseEntityName . '_grid.xml';
        $formFile = $viewUiComponentFolder . '/' . $snakeCaseEntityName . '_form.xml';
        if (!$this->generateUiGridFile($gridFile, $snakeCaseEntityName, $title, $entityName, $vendorNamespaceArr, $dbColumns, $frontName)) {
            $result['success'] = false;
            $result['message'] = 'Could not generate Ui Grid Xml File';
            return $result;
        }
        if ($uiFormStyle == 2) {
            if (!$this->generateUiFormFile($formFile, $snakeCaseEntityName, $title, $entityName, $vendorNamespaceArr, $dbColumns, $frontName)) {
                $result['success'] = false;
                $result['message'] = 'Could not generate Ui Form Xml File';
                return $result;
            }
         }else {
            /** Generate UI Form with 1 columns */
            if (!$this->generateUiFormFile($formFile, $snakeCaseEntityName, $title, $entityName, $vendorNamespaceArr, $dbColumns, $frontName, true)) {
                $result['success'] = false;
                $result['message'] = 'Could not generate Ui Form Xml File';
                return $result;
            }
        }
        $result['success'] = true;
//        $result['message'] = 'Could not generate Api Repository File';
        return $result;
    }

    /**
     * @param $formFile
     * @param $snakeCaseEntityName
     * @param $title
     * @param $entityName
     * @param $vendorNamespaceArr
     * @param $dbColumns
     * @param $frontName
     * @param bool $oneColumn
     * @return bool
     */
    public function generateUiFormFile($formFile, $snakeCaseEntityName, $title, $entityName, $vendorNamespaceArr, $dbColumns, $frontName, $oneColumn = false)
    {
        if (!$this->filesystemIo->fileExists($formFile)){
            $contents = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $contents .= '<form xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">' . PHP_EOL;
            $contents .= '    <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '        <item name="js_config" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="provider" xsi:type="string">' . $snakeCaseEntityName . '_form.' . $snakeCaseEntityName . '_form_data_source</item>' . PHP_EOL;
            $contents .= '            <item name="deps" xsi:type="string">' . $snakeCaseEntityName . '_form.' . $snakeCaseEntityName . '_form_data_source</item>' . PHP_EOL;
            $contents .= '        </item>' . PHP_EOL;
            $contents .= '        <item name="label" xsi:type="string" translate="true">' . $title . '</item>' . PHP_EOL;
            $contents .= '        <item name="config" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="dataScope" xsi:type="string">data</item>' . PHP_EOL;
            $contents .= '            <item name="namespace" xsi:type="string">' . $snakeCaseEntityName . '_form</item>' . PHP_EOL;
            $contents .= '        </item>' . PHP_EOL;
            if (!$oneColumn) {
                $contents .= '        <item name="layout" xsi:type="array">' . PHP_EOL;
                $contents .= '            <item name="type" xsi:type="string">tabs</item>' . PHP_EOL;
                $contents .= '            <item name="navContainerName" xsi:type="string">left</item>' . PHP_EOL;
                $contents .= '        </item>' . PHP_EOL;
            }
            $contents .= '        <item name="buttons" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="back" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Block\\Adminhtml\\' . $entityName . '\\Edit\\BackButton</item>' . PHP_EOL;
            $contents .= '            <item name="delete" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Block\\Adminhtml\\' . $entityName . '\\Edit\\DeleteButton</item>' . PHP_EOL;
            $contents .= '            <item name="save_and_continue" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Block\\Adminhtml\\' . $entityName . '\\Edit\\SaveAndContinueButton</item>' . PHP_EOL;
            $contents .= '            <item name="save" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Block\\Adminhtml\\' . $entityName . '\\Edit\\SaveButton</item>' . PHP_EOL;
            $contents .= '        </item>' . PHP_EOL;
            $contents .= '    </argument>' . PHP_EOL;
            $contents .= '    <dataSource name="' . $snakeCaseEntityName . '_form_data_source">' . PHP_EOL;
            $contents .= '        <argument name="dataProvider" xsi:type="configurableObject">' . PHP_EOL;
            $contents .= '            <argument name="class" xsi:type="string">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Ui\\Component\\DataProvider</argument>' . PHP_EOL;
            $contents .= '            <argument name="name" xsi:type="string">' . $snakeCaseEntityName . '_form_data_source</argument>' . PHP_EOL;
            $contents .= '            <argument name="primaryFieldName" xsi:type="string">id</argument>' . PHP_EOL;
            $contents .= '            <argument name="requestFieldName" xsi:type="string">id</argument>' . PHP_EOL;
            $contents .= '            <!--            <argument name="collectionFactory" xsi:type="object">' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Model\\' . $entityName . '\\ResourceModel\\' . $entityName . '\\CollectionFactory</argument>-->' . PHP_EOL;
            $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
            $contents .= '                    <item name="submit_url" xsi:type="url" path="' . $frontName . '/' . strtolower($entityName) . '/save"/>' . PHP_EOL;
            $contents .= '                </item>' . PHP_EOL;
            $contents .= '            </argument>' . PHP_EOL;
            $contents .= '        </argument>' . PHP_EOL;
            $contents .= '        <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="js_config" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="component" xsi:type="string">Magento_Ui/js/form/provider</item>' . PHP_EOL;
            $contents .= '            </item>' . PHP_EOL;
            $contents .= '        </argument>' . PHP_EOL;
            $contents .= '    </dataSource>' . PHP_EOL;
            $fieldSets = $this->formatFieldSets($dbColumns);
            if (!in_array('general', $fieldSets)) {
                $contents .= '    <fieldset name="general"><!--New fieldsets must be added at: ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component/DataProvider.php-->' . PHP_EOL;
                $contents .= '        <argument name="data" xsi:type="array">' . PHP_EOL;
                $contents .= '            <item name="config" xsi:type="array">' . PHP_EOL;
                $contents .= '                <item name="label" xsi:type="string" translate="true">General</item>' . PHP_EOL;
                $contents .= '            </item>' . PHP_EOL;
                $contents .= '        </argument>' . PHP_EOL;
                $contents .= '        <field name="store_id" formElement="select">' . PHP_EOL;
                $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
                $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
                $contents .= '                    <item name="source" xsi:type="string">block</item>' . PHP_EOL;
                $contents .= '                    <item name="default" xsi:type="number">0</item>' . PHP_EOL;
                $contents .= '                </item>' . PHP_EOL;
                $contents .= '            </argument>' . PHP_EOL;
                $contents .= '            <settings>' . PHP_EOL;
                $contents .= '                <validation>' . PHP_EOL;
                $contents .= '                    <rule name="required-entry" xsi:type="boolean">true</rule>' . PHP_EOL;
                $contents .= '                </validation>' . PHP_EOL;
                $contents .= '                <dataType>int</dataType>' . PHP_EOL;
                $contents .= '                <tooltip>' . PHP_EOL;
                $contents .= '                    <link>https://docs.magento.com/m2/ce/user_guide/configuration/scope.html</link>' . PHP_EOL;
                $contents .= '                    <description>What is this?</description>' . PHP_EOL;
                $contents .= '                </tooltip>' . PHP_EOL;
                $contents .= '                <label translate="true">Store View</label>' . PHP_EOL;
                $contents .= '                <dataScope>store_id</dataScope>' . PHP_EOL;
                $contents .= '            </settings>' . PHP_EOL;
                $contents .= '            <formElements>' . PHP_EOL;
                $contents .= '                <select>' . PHP_EOL;
                $contents .= '                    <settings>' . PHP_EOL;
                $contents .= '                        <options class="Magento\\Cms\\Ui\\Component\\Listing\\Column\\Cms\\Options"/>' . PHP_EOL;
                $contents .= '                    </settings>' . PHP_EOL;
                $contents .= '                </select>' . PHP_EOL;
                $contents .= '            </formElements>' . PHP_EOL;
                $contents .= '        </field>' . PHP_EOL;
                $contents .= '    </fieldset>' . PHP_EOL;
            } else {
                $storeContent = '        <field name="store_id" formElement="select">' . PHP_EOL;
                $storeContent .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
                $storeContent .= '                <item name="config" xsi:type="array">' . PHP_EOL;
                $storeContent .= '                    <item name="source" xsi:type="string">block</item>' . PHP_EOL;
                $storeContent .= '                    <item name="default" xsi:type="number">0</item>' . PHP_EOL;
                $storeContent .= '                </item>' . PHP_EOL;
                $storeContent .= '            </argument>' . PHP_EOL;
                $storeContent .= '            <settings>' . PHP_EOL;
                $storeContent .= '                <validation>' . PHP_EOL;
                $storeContent .= '                    <rule name="required-entry" xsi:type="boolean">true</rule>' . PHP_EOL;
                $storeContent .= '                </validation>' . PHP_EOL;
                $storeContent .= '                <dataType>int</dataType>' . PHP_EOL;
                $storeContent .= '                <tooltip>' . PHP_EOL;
                $storeContent .= '                    <link>https://docs.magento.com/m2/ce/user_guide/configuration/scope.html</link>' . PHP_EOL;
                $storeContent .= '                    <description>What is this?</description>' . PHP_EOL;
                $storeContent .= '                </tooltip>' . PHP_EOL;
                $storeContent .= '                <label translate="true">Store View</label>' . PHP_EOL;
                $storeContent .= '                <dataScope>store_id</dataScope>' . PHP_EOL;
                $storeContent .= '            </settings>' . PHP_EOL;
                $storeContent .= '            <formElements>' . PHP_EOL;
                $storeContent .= '                <select>' . PHP_EOL;
                $storeContent .= '                    <settings>' . PHP_EOL;
                $storeContent .= '                        <options class="Magento\\Cms\\Ui\\Component\\Listing\\Column\\Cms\\Options"/>' . PHP_EOL;
                $storeContent .= '                    </settings>' . PHP_EOL;
                $storeContent .= '                </select>' . PHP_EOL;
                $storeContent .= '            </formElements>' . PHP_EOL;
                $storeContent .= '        </field>' . PHP_EOL;
                $storeColumn = [];
                $storeColumn['backend_type'] = 'store_id';
                $storeColumn['backend_fieldset'] = 'general';
                $storeColumn['store_structure'] = $storeContent;
                array_push($dbColumns, $storeColumn);
            }
            foreach ($fieldSets as $fieldSet) {
                $contents .= '    <fieldset name="' . $fieldSet . '"><!--New fieldsets must be added at: ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component/DataProvider.php-->' . PHP_EOL;
                $contents .= '        <argument name="data" xsi:type="array">' . PHP_EOL;
                $contents .= '            <item name="config" xsi:type="array">' . PHP_EOL;
                $contents .= '                <item name="label" xsi:type="string" translate="true">' . $fieldSet . '</item>' . PHP_EOL;
                $contents .= '            </item>' . PHP_EOL;
                $contents .= '        </argument>' . PHP_EOL;
                foreach ($dbColumns as $column) {
                    if ($column['backend_fieldset'] === $fieldSet) {
                        if ($column['backend_type'] === 'text') {
                            $contents .= $this->textType->textTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'select') {
                            $contents .= $this->selectType->selectTypeField($column['name'], $column['backend_label'], $column['backend_options']);
                        }
                        if ($column['backend_type'] === 'multiselect') {
                            $contents .= $this->multiSelectType->multiSelectTypeField($column['name'], $column['backend_label'], $column['backend_options']);
                        }
                        if ($column['backend_type'] === 'checkbox') {
                            $contents .= $this->checkboxType->checkboxTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'imageUploader') {
                            $contents .= $this->imageUploaderType->imageUploaderTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'textarea') {
                            $contents .= $this->textAreaType->textAreaTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'color-picker') {
                            $contents .= $this->colorPickerType->colorPickerTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'wysiwyg') {
                            $contents .= $this->wysiwygType->wysiwygTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'fileUploader') {
                            $contents .= $this->fileUploaderType->fileUploaderTypeField($column['name'], $column['backend_label']);
                        }
                        if ($column['backend_type'] === 'dynamicRow') {
                            $contents .= $this->dynamicRowType->dynamicRowTypeField($column['name'], $column['backend_label'], $column['backend_dynamic_rows']);
                        }
                        if ($column['backend_type'] === 'store_id') {
                            $contents .= $column['store_structure'];
                        }
                    }
                }
                $contents .= '    </fieldset>' . PHP_EOL;
            }
            $contents .= '</form>' . PHP_EOL;
            if ($this->filesystemIo->write($formFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $columns
     * @return array
     */
    public function formatFieldSets($columns)
    {
        $fieldSets = [];
        foreach ($columns as $column) {
            if (!in_array($column['backend_fieldset'], $fieldSets)) {
                array_push($fieldSets, $column['backend_fieldset']);
            }
        }
        return $fieldSets;
    }

    /**
     * @param $gridFile
     * @param $snakeCaseEntityName
     * @param $title
     * @param $entityName
     * @param $vendorNamespaceArr
     * @param $dbColumns
     * @param $frontName
     * @return bool
     */
    public function generateUiGridFile($gridFile, $snakeCaseEntityName, $title, $entityName, $vendorNamespaceArr, $dbColumns, $frontName)
    {
        if (!$this->filesystemIo->fileExists($gridFile)){
            $contents = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
            $contents .= '<listing xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:module:Magento_Ui:etc/ui_configuration.xsd">' . PHP_EOL;
            $contents .= '    <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '        <item name="js_config" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="provider" xsi:type="string">' . $snakeCaseEntityName . '_grid.' . $snakeCaseEntityName . '_grid_data_source</item>' . PHP_EOL;
            $contents .= '            <item name="deps" xsi:type="string">' . $snakeCaseEntityName . '_grid.' . $snakeCaseEntityName . '_grid_data_source</item>' . PHP_EOL;
            $contents .= '        </item>' . PHP_EOL;
            $contents .= '        <item name="spinner" xsi:type="string">' . $snakeCaseEntityName . '_columns</item>' . PHP_EOL;
            $contents .= '        <item name="buttons" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="add" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="name" xsi:type="string">add</item>' . PHP_EOL;
            $contents .= '                <item name="label" xsi:type="string" translate="true">Add ' . $title . '</item>' . PHP_EOL;
            $contents .= '                <item name="class" xsi:type="string">primary</item>' . PHP_EOL;
            $contents .= '                <item name="url" xsi:type="string">' . $frontName . '/' . strtolower($entityName) . '/add</item>' . PHP_EOL;
            $contents .= '            </item>' . PHP_EOL;
            $contents .= '        </item>' . PHP_EOL;
            $contents .= '    </argument>' . PHP_EOL;
            $contents .= '    <dataSource name="' . $snakeCaseEntityName . '_grid_data_source">' . PHP_EOL;
            $contents .= '        <argument name="dataProvider" xsi:type="configurableObject">' . PHP_EOL;
            $contents .= '            <argument name="class" xsi:type="string">Magento\\Framework\\View\\Element\\UiComponent\\DataProvider\\DataProvider</argument>' . PHP_EOL;
            $contents .= '            <argument name="name" xsi:type="string">' . $snakeCaseEntityName . '_grid_data_source</argument>' . PHP_EOL;
            $contents .= '            <argument name="primaryFieldName" xsi:type="string">id</argument>' . PHP_EOL;
            $contents .= '            <argument name="requestFieldName" xsi:type="string">id</argument>' . PHP_EOL;
            $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
            $contents .= '                    <item name="update_url" xsi:type="url" path="mui/index/render"/>' . PHP_EOL;
            $contents .= '                    <item name="component" xsi:type="string">Magento_Ui/js/grid/provider</item>' . PHP_EOL;
            $contents .= '                    <item name="storageConfig" xsi:type="array">' . PHP_EOL;
            $contents .= '                      <item name="indexField" xsi:type="string">id</item>' . PHP_EOL;
            $contents .= '                    </item>' . PHP_EOL;
            $contents .= '                </item>' . PHP_EOL;
            $contents .= '            </argument>' . PHP_EOL;
            $contents .= '        </argument>' . PHP_EOL;
            $contents .= '    </dataSource>' . PHP_EOL;
            $contents .= '    <listingToolbar name="listing_top">' . PHP_EOL;
            $contents .= '        <bookmark name="bookmarks"/>' . PHP_EOL;
            $contents .= '        <columnsControls name="columns_controls"/>' . PHP_EOL;
            $contents .= '        <exportButton name="export_button"/>' . PHP_EOL;
            $contents .= '        <!--<filterSearch name="fulltext"/>-->' . PHP_EOL;
            $contents .= '        <filters name="listing_filters"/>' . PHP_EOL;
            $contents .= '        <paging name="listing_paging"/>' . PHP_EOL;
            $contents .= '    </listingToolbar>' . PHP_EOL;
            $contents .= '    <columns name="' . $snakeCaseEntityName . '_columns">' . PHP_EOL;
            $contents .= '        <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '            <item name="config" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="childDefaults" xsi:type="array">' . PHP_EOL;
            $contents .= '                    <item name="fieldAction" xsi:type="array">' . PHP_EOL;
            $contents .= '                        <item name="provider" xsi:type="string">' . $snakeCaseEntityName . '_grid.' . $snakeCaseEntityName . '_grid.' . $snakeCaseEntityName . '_columns.actions</item>' . PHP_EOL;
            $contents .= '                        <item name="target" xsi:type="string">applyAction</item>' . PHP_EOL;
            $contents .= '                        <item name="params" xsi:type="array">' . PHP_EOL;
            $contents .= '                            <item name="0" xsi:type="string">view</item>' . PHP_EOL;
            $contents .= '                            <item name="1" xsi:type="string">${ $.$data.rowIndex }</item>' . PHP_EOL;
            $contents .= '                        </item>' . PHP_EOL;
            $contents .= '                    </item>' . PHP_EOL;
            $contents .= '                </item>' . PHP_EOL;
            $contents .= '            </item>' . PHP_EOL;
            $contents .= '        </argument>' . PHP_EOL;
            $contents .= '        <selectionsColumn name="ids">' . PHP_EOL;
            $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
            $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
            $contents .= '                    <item name="indexField" xsi:type="string">id</item>' . PHP_EOL;
            $contents .= '                </item>' . PHP_EOL;
            $contents .= '            </argument>' . PHP_EOL;
            $contents .= '        </selectionsColumn>' . PHP_EOL;
            foreach ($dbColumns as $column) {
                if ($column['backend_grid'] === 'y') {
                    $contents .= '        <column name="' . $column['name'] . '">' . PHP_EOL;
                    $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
                    $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
                    $contents .= '                    <item name="filter" xsi:type="string">text</item>' . PHP_EOL;
                    $contents .= '                    <item name="label" xsi:type="string" translate="true">' . $column['backend_label'] . '</item>' . PHP_EOL;
                    $contents .= '                </item>' . PHP_EOL;
                    $contents .= '            </argument>' . PHP_EOL;
                    $contents .= '        </column>' . PHP_EOL;
                }
            }
            $contents .= '        <column name="store_id" class="Magento\\Store\\Ui\\Component\\Listing\\Column\\Store">' . PHP_EOL;
            $contents .= '            <settings>' . PHP_EOL;
            $contents .= '                <label translate="true">Store View</label>' . PHP_EOL;
            $contents .= '                <bodyTmpl>ui/grid/cells/html</bodyTmpl>' . PHP_EOL;
            $contents .= '                <sortable>false</sortable>' . PHP_EOL;
            $contents .= '            </settings>' . PHP_EOL;
            $contents .= '        </column>' . PHP_EOL;
            $contents .= '        <actionsColumn name="actions" class="' . $vendorNamespaceArr[0] . '\\' . $vendorNamespaceArr[1] . '\\Ui\\Component\\Listing\\Column\\Actions">' . PHP_EOL;
            $contents .= '            <settings>' . PHP_EOL;
            $contents .= '                <label translate="true">Actions</label>' . PHP_EOL;
            $contents .= '            </settings>' . PHP_EOL;
            $contents .= '        </actionsColumn>' . PHP_EOL;
            $contents .= '    </columns>' . PHP_EOL;
            $contents .= '</listing>' . PHP_EOL;
        if ($this->filesystemIo->write($gridFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $indexFile
     * @param $snakeCaseEntityName
     * @return bool
     */
    public function generateLayoutIndexFile($indexFile, $snakeCaseEntityName)
    {
        if (!$this->filesystemIo->fileExists($indexFile)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<page xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">' . PHP_EOL;
            $contents .= '    <body>' . PHP_EOL;
            $contents .= '        <referenceContainer name="content">' . PHP_EOL;
            $contents .= '            <uiComponent name="' . $snakeCaseEntityName . '_grid"/>' . PHP_EOL;
            $contents .= '        </referenceContainer>' . PHP_EOL;
            $contents .= '    </body>' . PHP_EOL;
            $contents .= '</page>' . PHP_EOL;
            if ($this->filesystemIo->write($indexFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $addFile
     * @param $snakeCaseEntityName
     * @return bool
     */
    public function generateLayoutAddFile($addFile, $snakeCaseEntityName)
    {
        if (!$this->filesystemIo->fileExists($addFile)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<page layout="admin-2columns-left" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">' . PHP_EOL;
            $contents .= '    <body>' . PHP_EOL;
            $contents .= '        <referenceContainer name="content">' . PHP_EOL;
            $contents .= '            <uiComponent name="' . $snakeCaseEntityName . '_form"/>' . PHP_EOL;
            $contents .= '        </referenceContainer>' . PHP_EOL;
            $contents .= '    </body>' . PHP_EOL;
            $contents .= '</page>' . PHP_EOL;
            if ($this->filesystemIo->write($addFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }

    /**
     * @param $editFile
     * @param $snakeCaseEntityName
     * @return bool
     */
    public function generateLayoutEditFile($editFile, $snakeCaseEntityName)
    {
        if (!$this->filesystemIo->fileExists($editFile)){
            $contents = '<?xml version="1.0"?>' . PHP_EOL;
            $contents .= '<page layout="admin-2columns-left" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="urn:magento:framework:View/Layout/etc/page_configuration.xsd">' . PHP_EOL;
            $contents .= '    <update handle="styles"/>' . PHP_EOL;
            $contents .= '    <update handle="editor"/>' . PHP_EOL;
            $contents .= '    <body>' . PHP_EOL;
            $contents .= '        <referenceContainer name="content">' . PHP_EOL;
            $contents .= '            <uiComponent name="' . $snakeCaseEntityName . '_form"/>' . PHP_EOL;
            $contents .= '        </referenceContainer>' . PHP_EOL;
            $contents .= '    </body>' . PHP_EOL;
            $contents .= '</page>' . PHP_EOL;
            if ($this->filesystemIo->write($editFile, $contents)) {
                return true;
            }
            return false;
        } else {
            //TODO: define action when file already exists
            return true;
        }
    }
}
