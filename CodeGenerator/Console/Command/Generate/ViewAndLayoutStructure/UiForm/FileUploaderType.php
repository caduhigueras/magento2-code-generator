<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class FileUploaderType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function fileUploaderTypeField($name, $label)
    {
        $contents = '<field name="' . $name . '">' . PHP_EOL;
        $contents .= '    <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '        <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '            <item name="label" xsi:type="string">' . $label . '</item>' . PHP_EOL;
        $contents .= '            <item name="visible" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '            <item name="formElement" xsi:type="string">fileUploader</item>' . PHP_EOL;
        $contents .= '            <item name="uploaderConfig" xsi:type="array">' . PHP_EOL;
        $contents .= '                <item name="url" xsi:type="url" path="path/to/controller"/>' . PHP_EOL;
        $contents .= '            </item>' . PHP_EOL;
        $contents .= '        </item>' . PHP_EOL;
        $contents .= '    </argument>' . PHP_EOL;
        $contents .= '</field>' . PHP_EOL;
        return $contents;
    }
}