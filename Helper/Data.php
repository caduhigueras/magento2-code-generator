<?php

namespace CodeBaby\CodeGenerator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    /**
     * @param $string
     * @return string|string[]
     */
    public function convertToUpperCamelCase($string)
    {
        return str_replace('_', '', ucwords($string, '_'));
    }

    /**
     * @param $string
     * @return string
     */
    public function convertToLowerCamelCase($string)
    {
        $n = str_replace('_', '', ucwords($string, '_'));
        return lcfirst($n);
    }

    /**
     * @param $string
     * @return string
     */
    public function convertToSnakeCase($string)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $string));
    }
}