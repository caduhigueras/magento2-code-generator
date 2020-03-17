<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class TextAreaType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function textAreaTypeField($name, $label)
    {
        $contents = '<textarea name="'. $name . '">' . PHP_EOL;
        $contents .= '    <settings>' . PHP_EOL;
        $contents .= '        <cols>15</cols>' . PHP_EOL;
        $contents .= '        <rows>5</rows>' . PHP_EOL;
        $contents .= '        <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '    </settings>' . PHP_EOL;
        $contents .= '</textarea>' . PHP_EOL;
        return $contents;
    }
}