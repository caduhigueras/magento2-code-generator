<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class TextType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function textTypeField($name, $label)
    {
        $contents = '<field name="' . $name . '">' . PHP_EOL;
        $contents .= '    <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '        <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '            <item name="label" xsi:type="string" translate="true">' . $label . '</item>' . PHP_EOL;
        $contents .= '            <item name="dataType" xsi:type="string">text</item>' . PHP_EOL;
        $contents .= '            <item name="formElement" xsi:type="string">input</item>' . PHP_EOL;
        $contents .= '            <item name="validation" xsi:type="array">' . PHP_EOL;
        $contents .= '                <!--<item name="required-entry" xsi:type="boolean">true</item>-->' . PHP_EOL;
        $contents .= '            </item>' . PHP_EOL;
        $contents .= '        </item>' . PHP_EOL;
        $contents .= '    </argument>' . PHP_EOL;
        $contents .= '</field>' . PHP_EOL;
        return $contents;
    }
}