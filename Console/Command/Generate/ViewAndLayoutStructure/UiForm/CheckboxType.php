<?php


namespace CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure\UiForm;


class CheckboxType
{
    /**
     * @param $name
     * @param $label
     * @return string
     */
    public function checkboxTypeField($name, $label)
    {
        $contents = '        <field name="' . $name . '" component="Magento_Ui/js/form/element/single-checkbox-toggle-notice" formElement="checkbox">' . PHP_EOL;
        $contents .= '            <argument name="data" xsi:type="array">' . PHP_EOL;
        $contents .= '                <item name="config" xsi:type="array">' . PHP_EOL;
        $contents .= '                    <item name="default" xsi:type="number">0</item>' . PHP_EOL;
        $contents .= '                    <!--                    This will generate a message below the toggle -->' . PHP_EOL;
        $contents .= '                    <item name="notices" xsi:type="array">' . PHP_EOL;
        $contents .= '                        <item name="0" xsi:type="string" translate="true">Notice #1</item>' . PHP_EOL;
        $contents .= '                        <item name="1" xsi:type="string" translate="true">Notice #2</item>' . PHP_EOL;
        $contents .= '                    </item>' . PHP_EOL;
        $contents .= '                </item>' . PHP_EOL;
        $contents .= '            </argument>' . PHP_EOL;
        $contents .= '            <settings>' . PHP_EOL;
        $contents .= '                <dataType>boolean</dataType>' . PHP_EOL;
        $contents .= '                <label translate="true">' . $label . '</label>' . PHP_EOL;
        $contents .= '            </settings>' . PHP_EOL;
        $contents .= '            <formElements>' . PHP_EOL;
        $contents .= '                <checkbox>' . PHP_EOL;
        $contents .= '                    <settings>' . PHP_EOL;
        $contents .= '                        <valueMap>' . PHP_EOL;
        $contents .= '                            <map name="false" xsi:type="number">0</map>' . PHP_EOL;
        $contents .= '                            <map name="true" xsi:type="number">1</map>' . PHP_EOL;
        $contents .= '                        </valueMap>' . PHP_EOL;
        $contents .= '                        <prefer>toggle</prefer>' . PHP_EOL;
        $contents .= '                    </settings>' . PHP_EOL;
        $contents .= '                </checkbox>' . PHP_EOL;
        $contents .= '            </formElements>' . PHP_EOL;
        $contents .= '        </field>' . PHP_EOL;
        return $contents;
    }
}