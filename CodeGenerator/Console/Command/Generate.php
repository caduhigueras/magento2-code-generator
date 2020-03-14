<?php

namespace CodeBaby\CodeGenerator\Console\Command;

use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
//use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use CodeBaby\CodeGenerator\Console\Command\Generate\InitialModuleStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\DbSchemaStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\ApiAndModelStructure;

class Generate extends Command
{
//    const INPUT_KEY_VENDOR = 'vendor';
//    const INPUT_KEY_MODULE = 'module';

    /**
     * @var InitialModuleStructure
     */
    private $initialModuleStructure;
    /**
     * @var DbSchemaStructure
     */
    private $dbSchemaStructure;
    /**
     * @var ApiAndModelStructure
     */
    private $apiAndModelStructure;

    public function __construct(
        InitialModuleStructure $initialModuleStructure,
        DbSchemaStructure $dbSchemaStructure,
        ApiAndModelStructure $apiAndModelStructure,
        string $name = null
    ) {
        parent::__construct($name);
        $this->initialModuleStructure = $initialModuleStructure;
        $this->dbSchemaStructure = $dbSchemaStructure;
        $this->apiAndModelStructure = $apiAndModelStructure;
    }

    protected function configure()
    {
        $this->setName('codebaby:generator:generate');
//            ->addArgument(self::INPUT_KEY_VENDOR, InputArgument::REQUIRED, 'Vendor name')
//            ->addArgument(self::INPUT_KEY_MODULE, InputArgument::REQUIRED, 'Module name');

        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $module = $this->initialModuleStructure($input,$output);
        $dbInfo = $this->dbSchemaStructure($input, $output, $module);
        $this->createApiAndModelFiles($input, $output, $module, $dbInfo);/*
        $this->createModelFiles($input, $output, $module);
        $this->createDiXml($input, $output, $module);
        $this->createBackendControllers($input, $output, $module);
        $this->createBackendBlocks($input, $output, $module);
        $this->createUiComponents($input, $output, $module);
        $this->createViewFiles($input, $output, $module);*/
//        $output->writeln($bundleName);

//        $vendor = $input->getArgument(self::INPUT_KEY_VENDOR);
//        $module = $input->getArgument(self::INPUT_KEY_MODULE);

/*        //creating main folder to receive the module
        $this->createMainFolder($vendor, $module);
        //create registration.php
        $this->createFile("registration.php", $this->getMainPath($vendor, $module), $this->registrationStructure($vendor, $module));
        //create etc/module.xml
        $this->createFile("module.xml", $this->getMainPath($vendor, $module) . "etc/", $this->moduleStructure($vendor, $module));
        //create composer.json file
        $this->createFile("composer.json", $this->getMainPath($vendor, $module), $this->composerJsonStructure($vendor, $module));*/
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param $input
     * @param $output
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function initialModuleStructure($input,$output)
    {
        $helper = $this->questionHelper();
        //First we ask if there is a need to create a new Module. If Yes, ask Vendor/Namespace - Yes is the default
        $moduleNecessary = new Question('Do you wish to create a new Module? (y/n):' . PHP_EOL, 'y');
        $moduleNecessaryAnswer = $helper->ask($input, $output, $moduleNecessary);
        $sequenceModulesAnswer = null;
        if ($moduleNecessaryAnswer[0] === 'y') {
            $moduleToCreate = new Question('Please enter the Vendor and Namespace of your module? example: Vendor_Namespace' . PHP_EOL, 'Test_Module');
            $moduleToCreateAnswer = $helper->ask($input, $output, $moduleToCreate);
            $sequenceModulesToCreate = new Question('Is there any modules to be loaded before yours (add comma separated)?'. PHP_EOL . 'example: Magento_Catalog,Magento_Customer :' . PHP_EOL, '');
            $sequenceModulesAnswer = $helper->ask($input, $output, $sequenceModulesToCreate);
        }
        if ($moduleToCreateAnswer) {
            $output->writeln('Generating necessary Magento 2 initial files...');
            $resp = $this->initialModuleStructure->createInitialModuleStructure($moduleToCreateAnswer, $sequenceModulesAnswer);
            $moduleArr = explode('_', $moduleToCreateAnswer);
            if ($resp['success']) {
                $output->writeln('<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/registration.php');
                $output->writeln('<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/composer.json');
                $output->writeln('<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/etc/module.xml');
            } else {
                $output->writeln($resp['message']);
            }
        }
        return $moduleToCreateAnswer;
    }

    public function dbSchemaStructure($input,$output, $vendorNamespace)
    {
        $helper = $this->questionHelper();
        //First we ask if there is a need to create a new Module. If Yes, ask Vendor/Namespace - Yes is the default
        $installDb = new Question('Do you wish to <fg=green>create a new database table</>? (y/n): ' . PHP_EOL, 'y');
        $installDbAnswer = $helper->ask($input, $output, $installDb);
        if ($installDbAnswer[0] === 'y') {
            //create table
            $tableToCreate = new Question('Please enter <fg=green>table name</>' . PHP_EOL, 'example_table');
            $tableToCreateAnswer = $helper->ask($input, $output, $tableToCreate);
            $output->writeln('<fg=green>To know how to fill this, please refer to :</> ' . PHP_EOL . 'https://devdocs.magento.com/guides/v2.3/extension-dev-guide/declarative-schema/db-schema.html');

            //create columns
            $columns = [];
            for( $i = 0; $i<50; $i++ ) {
                $column = [];
                $columnToCreate = new Question('Please add the <fg=green>column name</> (just press enter to stop creating columns):' . PHP_EOL, 'n');
                $columnToCreateAnswer = $helper->ask($input, $output, $columnToCreate);
                if ($columnToCreateAnswer === 'n') {
                    break;
                }
                $column['name'] = $columnToCreateAnswer;
                $columnType = new Question('Add the <fg=green>column type</>' . PHP_EOL . '(Allowed: int | smallint | datetime | boolean | decimal | text | varchar ):' . PHP_EOL, 'varchar');
                $columnTypeAnswer = $helper->ask($input, $output, $columnType);
                $column['type'] = $columnTypeAnswer;
                if ($columnTypeAnswer === 'int' || $columnTypeAnswer === 'smallint' ) {
                    $columnPadding = new Question('Add <fg=green>padding</>:' . PHP_EOL, '');
                    $columnPaddingAnswer = $helper->ask($input, $output, $columnPadding);
                    $column['padding'] = $columnPaddingAnswer;
                } elseif ($columnTypeAnswer === 'varchar' || $columnTypeAnswer === 'text' ) {
                    $columnLength = new Question('Add <fg=green>length</>:' . PHP_EOL, '');
                    $columnLengthAnswer = $helper->ask($input, $output, $columnLength);
                    $column['length'] = $columnLengthAnswer;
                } else if ($columnTypeAnswer === 'int' || $columnTypeAnswer === 'smallint' || $columnTypeAnswer === 'decimal') {
                    $columnUnsigned = new Question('<fg=green>Unsigned</> (true/false) - press enter to skip:' . PHP_EOL, 'n');
                    $columnUnsignedAnswer = $helper->ask($input, $output, $columnUnsigned);
                    if ($columnUnsignedAnswer !== 'n') {
                        $column['unsigned'] = $columnUnsignedAnswer;
                    }
                }
                $columnDefault = new Question('Add <fg=green>default value</> (press enter to skip):' . PHP_EOL, 'n');
                $columnDefaultAnswer = $helper->ask($input, $output, $columnDefault);
                if($columnDefaultAnswer !== 'n') {
                    $column['default'] = $columnDefaultAnswer;
                }
                $columnNullable = new Question('Is column <fg=green>nullable</>? (true/false) or (press enter to skip):' . PHP_EOL, 'n');
                $columnNullableAnswer = $helper->ask($input, $output, $columnNullable);
                if($columnNullableAnswer !== 'n') {
                    $column['nullable'] = $columnNullableAnswer;
                }

                //and finally lets ask for the ui component form type, label and option if there is
                $columnBackend = new Question('Define <fg=green>backend type</> for the 
                form?'. PHP_EOL .'(allowed types: checkbox | select | multiselect | text | imageUploader | textarea | color-picker | wysiwyg | fileUploader)' . PHP_EOL, 'text');
                $columnBackendAnswer = $helper->ask($input, $output, $columnBackend);
                $column['backend_type'] = $columnBackendAnswer;

                $columnBackendLabel = new Question('Define <fg=green>backend label</>' . PHP_EOL, 'Label');
                $columnBackendLabelAnswer = $helper->ask($input, $output, $columnBackendLabel);
                $column['backend_label'] = $columnBackendLabelAnswer;
                array_push($columns, $column);
            }
        }
        $resp = $this->dbSchemaStructure->generateDbSchemaXmlFile($vendorNamespace, $tableToCreateAnswer, $columns);
        $moduleArr = explode('_', $vendorNamespace);
        if ($resp['success']) {
            $output->writeln('<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/etc/db_schema.xml');
        } else {
            $output->writeln($resp['message']);
        }
        $dbInfo['db_name'] = $tableToCreateAnswer;
        $dbInfo['columns'] = $columns;
        return $dbInfo;
    }

    public function createApiAndModelFiles($input, $output, $module, $dbInfo)
    {
        $vendorNamespaceArr = explode('_', $module);
        $dbColumns = $dbInfo['columns'];
        $dbName = $dbInfo['db_name'];
        $helper = $this->questionHelper();
        $installDb = new Question('Define the name of <fg=green>your entity</>? (Example: MyNewEntity): ' . PHP_EOL, 'DemoEntity');
        $entityName = $helper->ask($input, $output, $installDb);
        $resp = $this->apiAndModelStructure->generateApiAndModelFiles($module, $dbColumns, $entityName, $dbName);
        if ($resp['success']) {
            $output->writeln('<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '/' . $entityName . '.php');
            $output->writeln('<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '/' . $entityName . 'Repository.php');
            $output->writeln('<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '/' . 'ResourceModel' . '/' . $entityName . '.php');
            $output->writeln('<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '/' . 'ResourceModel' . '/' . $entityName . '/' . 'Collection.php');
            $output->writeln('<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '/' . 'ResourceModel' . '/' . $entityName . '/' . 'Grid' . '/' . 'Collection.php');
        } else {
            $output->writeln($resp['message']);
        }
        $output->writeln(print_r($resp));
    }

    public function questionHelper()
    {
        return $this->getHelper('question');
    }
}