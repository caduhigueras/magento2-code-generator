<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class WysiwygType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function wysiwygTypeField($name, $label)
    {
        $contents = '        <field name="' . $name . '" sortOrder="50" formElement="wysiwyg">' . PHP_EOL;
        $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                    <item name="wysiwygConfigData" xsi:type="array">' . PHP_EOL;
        $contents .= '                        <item name="height" xsi:type="string">300px</item>' . PHP_EOL;
        $contents .= '                        <item name="add_variables" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                        <item name="add_widgets" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                        <item name="add_images" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                        <item name="add_directives" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                    </item>' . PHP_EOL;
        $contents .= '                </item>' . PHP_EOL;
        $contents .= '            </argument>' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <label>' . $label . '</label>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '            <formElements>' . PHP_EOL;
        $contents .= '                <wysiwyg>' . PHP_EOL;
        $contents .= '                    <settings>' . PHP_EOL;
        $contents .= '                        <rows>8</rows>' . PHP_EOL;
        $contents .= '                        <wysiwyg>true</wysiwyg>' . PHP_EOL;
        $contents .= '                    </settings>' . PHP_EOL;
        $contents .= '                </wysiwyg>' . PHP_EOL;
        $contents .= '            </formElements>' . PHP_EOL;
        $contents .= '        </field>' . PHP_EOL;
        return $contents;
    }
}