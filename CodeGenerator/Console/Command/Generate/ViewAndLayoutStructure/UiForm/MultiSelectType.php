<?php

namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;

class MultiSelectType
{
    /**
     * @param $name
     * @param $label
     * @param $options
     * @return string
     */
    public function multiSelectTypeField($name, $label, $options)
    {
        $contents = '        <field name="' . $name . '" formElement="multiselect">' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <dataType>text</dataType>' . PHP_EOL;
        $contents .= '                <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '                <dataScope>' . $name . '</dataScope>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '            <formElements>' . PHP_EOL;
        $contents .= '                <multiselect>' . PHP_EOL;
        $contents .= '                    <settings>' . PHP_EOL;
        $contents .= '                        <options>' . PHP_EOL;
        foreach ($options as $key => $option) {
            $contents .= '                            <option name="' . $key . '" xsi:type="array">' . PHP_EOL;
            $contents .= '                                <item name="value" xsi:type="string">' . $option['value'] . '</item>' . PHP_EOL;
            $contents .= '                                <item name="label" xsi:type="string">' . $option['label'] . '</item>' . PHP_EOL;
            $contents .= '                            </option>' . PHP_EOL;
        }
        $contents .= '                        </options>' . PHP_EOL;
        $contents .= '                    </settings>' . PHP_EOL;
        $contents .= '                </multiselect>' . PHP_EOL;
        $contents .= '            </formElements>' . PHP_EOL;
        $contents .= '        </field>' . PHP_EOL;
        return $contents;
    }
}