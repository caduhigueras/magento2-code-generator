<?php

namespace CodeBaby\CodeGenerator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

class Data extends AbstractHelper
{
    private string $userName;
    private string $team;
    private string $date;
    private string $vendorNamespace;

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

    /**
     * @param $fileName
     * @return string
     */
    public function getSignature($fileName): string
    {
        $sig = "/**" . PHP_EOL;
        $sig .= " * " . $this->getVendorNamespace() . " | " . $fileName . PHP_EOL;
        $sig .= " * Created by " . $this->getTeam() . "." . PHP_EOL;
        $sig .= " * User: " . $this->getUserName() . PHP_EOL;
        $sig .= " * Date: " . $this->getDate() . PHP_EOL;
        $sig .= " **/" . PHP_EOL;
        $sig .= PHP_EOL;
        $sig .= "declare(strict_types=1);" . PHP_EOL . PHP_EOL;
        return $sig;
    }

    /**
     * @param $fileName
     * @return string
     */
    public function getXmlSignature($fileName): string
    {
        $sig = "<!--" . PHP_EOL;
        $sig .= "  * " . $this->getVendorNamespace() . " | " . $fileName . PHP_EOL;
        $sig .= "  * Created by " . $this->getTeam() . "." . PHP_EOL;
        $sig .= "  * User: " . $this->getUserName() . PHP_EOL;
        $sig .= "  * Date: " . $this->getDate() . PHP_EOL;
        $sig .= "-->" . PHP_EOL;
        $sig .= PHP_EOL;
        return $sig;
    }

    /**
     * @return string
     */
    public function getUserName(): string
    {
        return $this->userName;
    }

    /**
     * @param string $userName
     */
    public function setUserName(string $userName): void
    {
        $this->userName = $userName;
    }

    /**
     * @return string
     */
    public function getTeam(): string
    {
        return $this->team;
    }

    /**
     * @param string $team
     */
    public function setTeam(string $team): void
    {
        $this->team = $team;
    }

    /**
     * @return string
     */
    public function getDate(): string
    {
        return $this->date;
    }

    /**
     * @param string $date
     */
    public function setDate(string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getVendorNamespace(): string
    {
        return $this->vendorNamespace;
    }

    /**
     * @param string $vendorNamespace
     */
    public function setVendorNamespace(string $vendorNamespace): void
    {
        $this->vendorNamespace = $vendorNamespace;
    }
}