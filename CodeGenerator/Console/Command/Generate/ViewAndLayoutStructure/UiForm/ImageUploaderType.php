<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class ImageUploaderType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function imageUploaderTypeField($name, $label)
    {
        $contents = '<field name="' . $name . '" formElement="imageUploader">' . PHP_EOL;
        $contents .= '    <settings>' . PHP_EOL;
        $contents .= '        <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '        <componentType>imageUploader</componentType>' . PHP_EOL;
        $contents .= '    </settings>' . PHP_EOL;
        $contents .= '    <formElements>' . PHP_EOL;
        $contents .= '        <imageUploader>' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <allowedExtensions>jpg jpeg gif png</allowedExtensions>' . PHP_EOL;
        $contents .= '                <maxFileSize>2097152</maxFileSize>' . PHP_EOL;
        $contents .= '                <uploaderConfig>' . PHP_EOL;
        $contents .= '                    <param xsi:type="string" name="url">path/to/saveimage</param>' . PHP_EOL;
        $contents .= '                </uploaderConfig>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '        </imageUploader>' . PHP_EOL;
        $contents .= '    </formElements>' . PHP_EOL;
        $contents .= '</field>' . PHP_EOL;
        return $contents;
    }
}