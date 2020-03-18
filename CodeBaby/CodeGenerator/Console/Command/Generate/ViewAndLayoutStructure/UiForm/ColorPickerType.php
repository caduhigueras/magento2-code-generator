<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class ColorPickerType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function colorPickerTypeField($name, $label)
    {
        $contents = '        <colorPicker name="' . $name . '" class="Magento\Ui\Component\Form\Element\ColorPicker" component="Magento_Ui/js/form/element/color-picker">' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '                <elementTmpl>ui/form/element/color-picker</elementTmpl>' . PHP_EOL;
        $contents .= '                <colorFormat>rgb</colorFormat>' . PHP_EOL;
        $contents .= '                <colorPickerMode>full</colorPickerMode>' . PHP_EOL;
        $contents .= '                <dataScope>' . $name . '</dataScope>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '        </colorPicker>' . PHP_EOL;
        return $contents;
    }
}