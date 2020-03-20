<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class DynamicRowType
{
    /**
     * @var CheckboxType
     */
    private $checkboxType;
    /**
     * @var ColorPickerType
     */
    private $colorPickerType;
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
        CheckboxType $checkboxType,
        ColorPickerType $colorPickerType,
        FileUploaderType $fileUploaderType,
        ImageUploaderType $imageUploaderType,
        MultiSelectType $multiSelectType,
        SelectType $selectType,
        TextAreaType $textAreaType,
        TextType $textType,
        WysiwygType $wysiwygType
    ) {
        $this->checkboxType = $checkboxType;
        $this->colorPickerType = $colorPickerType;
        $this->fileUploaderType = $fileUploaderType;
        $this->imageUploaderType = $imageUploaderType;
        $this->multiSelectType = $multiSelectType;
        $this->selectType = $selectType;
        $this->textAreaType = $textAreaType;
        $this->textType = $textType;
        $this->wysiwygType = $wysiwygType;
    }

    public function dynamicRowTypeField($name, $label, $dynamicRows)
    {
        $contents = '        <dynamicRows name="' . $name . '">' . PHP_EOL;
        $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                    <!-- <item name="component" xsi:type="string">Magento_Ui/js/dynamic-rows/dynamic-rows</item>-->' . PHP_EOL;
        $contents .= '                    <!-- <item name="template" xsi:type="string">Magento_Ui/dynamic-rows/templates/default</item>-->' . PHP_EOL;
        $contents .= '                </item>' . PHP_EOL;
        $contents .= '            </argument>' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '                <addButtonLabel translate="true">Add Row</addButtonLabel>' . PHP_EOL;
        $contents .= '                <dndConfig>' . PHP_EOL;
        $contents .= '                    <param name="enabled" xsi:type="boolean">true</param>' . PHP_EOL;
        $contents .= '                    <param name="draggableElementClass" xsi:type="string">_dragged</param>' . PHP_EOL;
        $contents .= '                    <param name="tableClass" xsi:type="string">table.admin__dynamic-rows</param>' . PHP_EOL;
        $contents .= '                </dndConfig>' . PHP_EOL;
        $contents .= '                <additionalClasses>' . PHP_EOL;
        $contents .= '                    <class name="admin__field-wide">true</class>' . PHP_EOL;
        $contents .= '                </additionalClasses>' . PHP_EOL;
        $contents .= '                <componentType>dynamicRows</componentType>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '            <container name="record" component="Magento_Ui/js/dynamic-rows/record">' . PHP_EOL;
        $contents .= '                <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                    <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                        <item name="isTemplate" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                        <item name="is_collection" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                        <item name="componentType" xsi:type="string">container</item>' . PHP_EOL;
        $contents .= '                    </item>' . PHP_EOL;
        $contents .= '                </argument>' . PHP_EOL;
        foreach ($dynamicRows as $row) {
            $name = str_replace(' ', '_', $row['label']);
            if ($row['type'] === 'text') {
                $contents .= $this->textType->textTypeField($name, $row['label']);
            }
            if ($row['type'] === 'select') {
                $contents .= $this->selectType->selectTypeField($name, $row['label'], $row['options']);
            }
            if ($row['type'] === 'multiselect') {
                $contents .= $this->multiSelectType->multiSelectTypeField($name, $row['label'], $row['options']);
            }
            if ($row['type'] === 'checkbox') {
                $contents .= $this->checkboxType->checkboxTypeField($name, $row['label']);
            }
            if ($row['type'] === 'imageUploader') {
                $contents .= $this->imageUploaderType->imageUploaderTypeField($name, $row['label']);
            }
            if ($row['type'] === 'textarea') {
                $contents .= $this->textAreaType->textAreaTypeField($name, $row['label']);
            }
            if ($row['type'] === 'color-picker') {
                $contents .= $this->colorPickerType->colorPickerTypeField($name, $row['label']);
            }
            if ($row['type'] === 'wysiwyg') {
                $contents .= $this->wysiwygType->wysiwygTypeField($name, $row['label']);
            }
            if ($row['type'] === 'fileUploader') {
                $contents .= $this->fileUploaderType->fileUploaderTypeField($name, $row['label']);
            }
            /*if ($row['type'] === 'dynamicRow') {
                $contents .= $this->dynamicRowType->dynamicRowTypeField($name, $row['label'], $row['dynamic_rows']);
            }*/
            if ($row['type'] === 'store_id') {
                $contents .= $row['store_structure'];
            }
        }
        $contents .= '                <actionDelete template="Magento_Backend/dynamic-rows/cells/action-delete">' . PHP_EOL;
        $contents .= '                    <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                        <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                            <item name="fit" xsi:type="boolean">false</item>' . PHP_EOL;
        $contents .= '                        </item>' . PHP_EOL;
        $contents .= '                    </argument>' . PHP_EOL;
        $contents .= '                    <settings>' . PHP_EOL;
        $contents .= '                        <!-- <additionalClasses>' . PHP_EOL;
        $contents .= '                            <class name="some-class">true</class>' . PHP_EOL;
        $contents .= '                        </additionalClasses>-->' . PHP_EOL;
        $contents .= '                        <dataType>text</dataType>' . PHP_EOL;
        $contents .= '                        <label>Actions</label>' . PHP_EOL;
        $contents .= '                        <componentType>actionDelete</componentType>' . PHP_EOL;
        $contents .= '                    </settings>' . PHP_EOL;
        $contents .= '                </actionDelete>' . PHP_EOL;
        $contents .= '            </container>' . PHP_EOL;
        $contents .= '        </dynamicRows>' . PHP_EOL;
        return $contents;
    }
}
