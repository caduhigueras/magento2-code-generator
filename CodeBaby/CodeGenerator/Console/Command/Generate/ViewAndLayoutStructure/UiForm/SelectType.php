<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class SelectType
{
    public function selectTypeField($name, $label, $options)
    {
        $contents = '        <!-- in case options need to be generated dynamically -->' . PHP_EOL;
        $contents .= '        <!--<field name="' . $name . '">' . PHP_EOL;
        $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                <item name="options" xsi:type="object">Onedirect\Forms\Model\Config\Source\StyleTypes</item>' . PHP_EOL;
        $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                    <item name="label" xsi:type="string" translate="true">' . $label . '</item>' . PHP_EOL;
        $contents .= '                    <item name="visible" xsi:type="boolean">true</item>' . PHP_EOL;
        $contents .= '                    <item name="dataType" xsi:type="string">number</item>' . PHP_EOL;
        $contents .= '                    <item name="formElement" xsi:type="string">select</item>' . PHP_EOL;
        $contents .= '                    <item name="source" xsi:type="string">item</item>' . PHP_EOL;
        $contents .= '                    <item name="dataScope" xsi:type="string">' . $name . '</item>' . PHP_EOL;
        $contents .= '                    <item name="validation" xsi:type="array">' . PHP_EOL;
        $contents .= '                        <item name="required-entry" xsi:type="boolean">false</item>' . PHP_EOL;
        $contents .= '                    </item>' . PHP_EOL;
        $contents .= '                </item>' . PHP_EOL;
        $contents .= '            </argument>' . PHP_EOL;
        $contents .= '        </field>-->' . PHP_EOL;
        $contents .= '        <field name="' . $name . '" formElement="select">' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <dataType>text</dataType>' . PHP_EOL;
        $contents .= '                <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '                <dataScope>' . $name . '</dataScope>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '            <formElements>' . PHP_EOL;
        $contents .= '                <select>' . PHP_EOL;
        $contents .= '                    <settings>' . PHP_EOL;
        $contents .= '                        <options>' . PHP_EOL;
        foreach ($options as $key => $option) {
            $contents .= '                            <option name="' . $key . '" xsi:type="array">' . PHP_EOL;
            $contents .= '                                <item name="value" xsi:type="string">' . $option['value'] . '</item>' . PHP_EOL;
            $contents .= '                                <item name="label" xsi:type="string">' . $option['label'] . '</item>' . PHP_EOL;
            $contents .= '                            </option>' . PHP_EOL;
        }
        $contents .= '                        </options>' . PHP_EOL;
        $contents .= '                        <caption translate="true">-- Please Select --</caption>' . PHP_EOL;
        $contents .= '                    </settings>' . PHP_EOL;
        $contents .= '                </select>' . PHP_EOL;
        $contents .= '            </formElements>' . PHP_EOL;
        $contents .= '        </field>' . PHP_EOL;
        return $contents;
    }
}