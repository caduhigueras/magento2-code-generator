<?php

namespace CodeBaby\CodeGenerator\Console\Command;

use CodeBaby\CodeGenerator\Console\Command\Generate\ApiAndModelStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\BackendBlocksStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\BackendControllersStructure;
//use Symfony\Component\Console\Input\InputArgument;
use CodeBaby\CodeGenerator\Console\Command\Generate\DbSchemaStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\DiXmlStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\InitialModuleStructure;
//use Symfony\Component\Console\Question\ConfirmationQuestion;
use CodeBaby\CodeGenerator\Console\Command\Generate\UiFolderStructure;
use CodeBaby\CodeGenerator\Console\Command\Generate\ViewAndLayoutStructure;
use CodeBaby\CodeGenerator\Helper\Data;
use Magento\Framework\Console\Cli;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class Generate extends Command
{
    /**
     * @var array
     */
    public $outputsArr = [];

    private InitialModuleStructure $initialModuleStructure;
    private DbSchemaStructure $dbSchemaStructure;
    private ApiAndModelStructure $apiAndModelStructure;
    private DiXmlStructure $diXmlStructure;
    private BackendControllersStructure $backendControllersStructure;
    private BackendBlocksStructure $backendBlocksStructure;
    private UiFolderStructure $uiFolderStructure;
    private ViewAndLayoutStructure $viewAndLayoutStructure;
    private Data $helper;

    /**
     * @param InitialModuleStructure $initialModuleStructure
     * @param DbSchemaStructure $dbSchemaStructure
     * @param ApiAndModelStructure $apiAndModelStructure
     * @param DiXmlStructure $diXmlStructure
     * @param BackendControllersStructure $backendControllersStructure
     * @param BackendBlocksStructure $backendBlocksStructure
     * @param UiFolderStructure $uiFolderStructure
     * @param ViewAndLayoutStructure $viewAndLayoutStructure
     * @param Data $helper
     * @param string|null $name
     */
    public function __construct(
        InitialModuleStructure $initialModuleStructure,
        DbSchemaStructure $dbSchemaStructure,
        ApiAndModelStructure $apiAndModelStructure,
        DiXmlStructure $diXmlStructure,
        BackendControllersStructure $backendControllersStructure,
        BackendBlocksStructure $backendBlocksStructure,
        UiFolderStructure $uiFolderStructure,
        ViewAndLayoutStructure $viewAndLayoutStructure,
        Data $helper,
        string $name = null
    ) {
        parent::__construct($name);
        $this->initialModuleStructure = $initialModuleStructure;
        $this->dbSchemaStructure = $dbSchemaStructure;
        $this->apiAndModelStructure = $apiAndModelStructure;
        $this->diXmlStructure = $diXmlStructure;
        $this->backendControllersStructure = $backendControllersStructure;
        $this->backendBlocksStructure = $backendBlocksStructure;
        $this->uiFolderStructure = $uiFolderStructure;
        $this->viewAndLayoutStructure = $viewAndLayoutStructure;
        $this->helper = $helper;
    }

    protected function configure()
    {
        $this->setName('codebaby:generator:generate')
            ->addOption('module-only', 'm', InputOption::VALUE_OPTIONAL, 'Generates only base module structure')
            ->addOption('db-only', 'd', InputOption::VALUE_OPTIONAL, 'Generates only db_schema.xml structure')
            ->addOption('api-and-model-only', 'a', InputOption::VALUE_OPTIONAL, 'Generates only Api Repository / Interface and Model related structure')
            ->addOption('block-buttons-only', 'b', InputOption::VALUE_OPTIONAL, 'Generates only Block Buttons (save / edit / duplicate / delete / back) structure')
            ->addOption('ui-folder-only', 'u', InputOption::VALUE_OPTIONAL, 'Generates only Listing Actions and Data Provider Structure')
            ->addOption('controllers-only', 'c', InputOption::VALUE_OPTIONAL, 'Generates only Controllers (Add / Index / Edit / Save / Duplicate) Structure')
            ->addOption('view-only', 'vo', InputOption::VALUE_OPTIONAL, 'Generates only View Layouts and UiComponents Structure')
            ->addOption('file-upload-controller', 'f', InputOption::VALUE_OPTIONAL, 'Soon!');
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
        $helper = $this->questionHelper();

        $output->writeln("Before start, we need to ask a couple questions.");

        $askName = new Question('Select a <fg=green>username</> to be used for the signatures' . PHP_EOL, '');
        $user = $helper->ask($input, $output, $askName);
        $this->helper->setUserName($user);

        $askTeam = new Question('Select a <fg=green>team name</> to be used for the signatures' . PHP_EOL, '');
        $team = $helper->ask($input, $output, $askTeam);
        $this->helper->setTeam($team);

        $this->helper->setDate(date("j/n/Y"));

        //install only initial module
        if ($input->getOption('module-only')) {
            $this->initialModuleStructure($input, $output);
            foreach ($this->outputsArr as $msg) {
                $output->writeln($msg);
            }
            return Cli::RETURN_SUCCESS;
        }
        //install only db_schema
        if ($input->getOption('db-only')) {
            $askModule = new Question('For which module will the db_schema.xml be generated?' . PHP_EOL, 'Vendor_Namespace');
            $module = $helper->ask($input, $output, $askModule);
            $this->dbSchemaStructure($input, $output, $module);
            foreach ($this->outputsArr as $msg) {
                $output->writeln($msg);
            }
            return Cli::RETURN_SUCCESS;
        }

        //install only api and repo
        if ($input->getOption('api-and-model-only')) {
            $output->writeln('You must define the columns of the database to generate those files');
            $askModule = new Question('For which module will the files be generated?' . PHP_EOL, 'Vendor_Namespace');
            $module = $helper->ask($input, $output, $askModule);
            $dbInfo = $this->dbSchemaStructure($input, $output, $module, false);
            $this->createApiAndModelFiles($input, $output, $module, $dbInfo);
            foreach ($this->outputsArr as $msg) {
                $output->writeln($msg);
            }
            return Cli::RETURN_SUCCESS;
        }

        //install only block buttons
        if ($input->getOption('block-buttons-only')) {
            $output->writeln('You must define the columns of the database to generate those files');
            $askModule = new Question('For which module will the buttons be generated?' . PHP_EOL, 'Vendor_Namespace');
            $module = $helper->ask($input, $output, $askModule);

            $askEntity = new Question('What is the entity? (Ex: MyEntity)' . PHP_EOL, 'MyEntity');
            $entityName = $helper->ask($input, $output, $askEntity);

            $dbInfo = $this->dbSchemaStructure($input, $output, $module, false);

            $askFrontName = new Question('What is the frontname for the controllers? (no dashes or spaces allowed)' . PHP_EOL, 'MyEntity');
            $frontName = $helper->ask($input, $output, $askFrontName);

            $this->createBackendBlocks($output, $module, $entityName, $dbInfo, $frontName);
            $dbInfo = $this->dbSchemaStructure($input, $output, $module, false);
            $this->createApiAndModelFiles($input, $output, $module, $dbInfo);
            foreach ($this->outputsArr as $msg) {
                $output->writeln($msg);
            }
            return Cli::RETURN_SUCCESS;
        }

        $module = $this->initialModuleStructure($input, $output);
        $dbInfo = $this->dbSchemaStructure($input, $output, $module);
        $entityName = $this->createApiAndModelFiles($input, $output, $module, $dbInfo);
        $this->createDiXml($output, $module, $dbInfo, $entityName);
        $frontName = $this->createBackendControllers($input, $output, $module, $entityName, $dbInfo);
        $this->createBackendBlocks($output, $module, $entityName, $dbInfo, $frontName);
        $this->createUiFiles($output, $module, $entityName, $dbInfo, $frontName);
        $this->generateLayoutAndComponentFiles($output, $module, $entityName, $dbInfo, $frontName, $input);

        foreach ($this->outputsArr as $msg) {
            $output->writeln($msg);
        }
        return Cli::RETURN_SUCCESS;
    }

    /**
     * @param $input
     * @param $output
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Exception
     */
    public function initialModuleStructure($input, $output)
    {
        $helper = $this->questionHelper();
        //First we ask if there is a need to create a new Module. If Yes, ask Vendor/Namespace - Yes is the default
        $moduleNecessary = new Question('Do you wish to create a new Module? (y/n):' . PHP_EOL, 'y');
        $moduleNecessaryAnswer = $helper->ask($input, $output, $moduleNecessary);
        $sequenceModulesAnswer = null;
        if ($moduleNecessaryAnswer[0] === 'y') {
            $moduleToCreate = new Question('Please enter the Vendor and Namespace of your module? example: Vendor_Namespace' . PHP_EOL, 'Test_Module');
            $moduleToCreateAnswer = $helper->ask($input, $output, $moduleToCreate);
            $sequenceModulesToCreate = new Question('Is there any modules to be loaded before yours (add comma separated)?' . PHP_EOL . 'example: Magento_Catalog,Magento_Customer :' . PHP_EOL, '');
            $sequenceModulesAnswer = $helper->ask($input, $output, $sequenceModulesToCreate);
        }
        if ($moduleToCreateAnswer) {
            $this->helper->setVendorNamespace($moduleToCreateAnswer);
            $output->writeln('Generating necessary Magento 2 initial files...');
            $resp = $this->initialModuleStructure->createInitialModuleStructure($moduleToCreateAnswer, $sequenceModulesAnswer);
            $moduleArr = explode('_', $moduleToCreateAnswer);
            if ($resp['success']) {
                array_push($this->outputsArr, '<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/registration.php');
                array_push($this->outputsArr, '<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/composer.json');
                array_push($this->outputsArr, '<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/etc/module.xml');
            } else {
                $output->writeln($resp['message']);
            }
        }
        return $moduleToCreateAnswer;
    }

    /**
     * @param $input
     * @param $output
     * @param $vendorNamespace
     * @param bool $createDb
     * @return mixed
     */
    public function dbSchemaStructure($input, $output, $vendorNamespace, $createDb = true)
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

            $multipleFieldsetsQuestion = new Question('Do you need more than one <fg=green>fieldset</> on the backend form component? (y/n)' . PHP_EOL, 'n');
            $multipleFieldsetsAnswer = $helper->ask($input, $output, $multipleFieldsetsQuestion);
            $multipleFieldsets = $multipleFieldsetsAnswer === 'y';

            //create columns
            $columns = [];
            for ($i = 0; $i<50; $i++) {
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
                if ($columnTypeAnswer === 'int' || $columnTypeAnswer === 'smallint') {
                    $columnPadding = new Question('Add <fg=green>padding</>:' . PHP_EOL, '');
                    $columnPaddingAnswer = $helper->ask($input, $output, $columnPadding);
                    $column['padding'] = $columnPaddingAnswer;
                } elseif ($columnTypeAnswer === 'varchar') {
                    $columnLength = new Question('Add <fg=green>length</>:' . PHP_EOL, '');
                    $columnLengthAnswer = $helper->ask($input, $output, $columnLength);
                    $column['length'] = $columnLengthAnswer;
                } elseif ($columnTypeAnswer === 'int' || $columnTypeAnswer === 'smallint' || $columnTypeAnswer === 'decimal') {
                    $columnUnsigned = new Question('<fg=green>Unsigned</> (true/false) - press enter to skip:' . PHP_EOL, 'n');
                    $columnUnsignedAnswer = $helper->ask($input, $output, $columnUnsigned);
                    if ($columnUnsignedAnswer !== 'n') {
                        $column['unsigned'] = $columnUnsignedAnswer;
                    }
                }
                $columnDefault = new Question('Add <fg=green>default value</> (press enter to skip):' . PHP_EOL, 'n');
                $columnDefaultAnswer = $helper->ask($input, $output, $columnDefault);
                if ($columnDefaultAnswer !== 'n') {
                    $column['default'] = $columnDefaultAnswer;
                }
                $columnNullable = new Question('Is column <fg=green>nullable</>? (true/false) or (press enter to skip):' . PHP_EOL, 'true');
                $columnNullableAnswer = $helper->ask($input, $output, $columnNullable);
                if ($columnNullableAnswer !== 'n') {
                    $column['nullable'] = $columnNullableAnswer;
                }

                //and finally lets ask for the ui component form type, label and option if there is
                $columnBackend = new Question('Define <fg=green>backend type</> for the form?'
                    . PHP_EOL . '(allowed types: checkbox | select | multiselect | text | imageUploader | textarea | color-picker | wysiwyg | fileUploader | dynamicRow)' . PHP_EOL, 'text');
                $columnBackendAnswer = $helper->ask($input, $output, $columnBackend);
                $column['backend_type'] = $columnBackendAnswer;

                if ($columnBackendAnswer === 'select' || $columnBackendAnswer === 'multiselect') {
                    $options = [];
                    for ($i = 0; $i<50; $i++) {
                        $optionCreate = new Question('Please add the <fg=green>value and label</> of the option (Format: value, label):'
                            . PHP_EOL . 'Press enter to stop adding options' . PHP_EOL, 'n');
                        $columnToCreateAnswer = $helper->ask($input, $output, $optionCreate);
                        if ($columnToCreateAnswer === 'n') {
                            break;
                        }
                        $optionsArr = [];
                        $option = explode(',', $columnToCreateAnswer);
                        $optionsArr['value'] = $option[0];
                        $optionsArr['label'] = $option[1];
                        array_push($options, $optionsArr);
                    }
                    $column['backend_options'] = $options;
                }

                if ($columnBackendAnswer === 'dynamicRow') {
                    $dynamicRows = [];
                    for ($i = 0; $i<50; $i++) {
                        $dynamicRowItemArr = [];
                        $dynamicRowItemCreate = new Question('Please add the <fg=green>dynamic item type</>' . PHP_EOL .
                            '(Allowed: checkbox | select | multiselect | text | imageUploader | textarea | color-picker | wysiwyg | fileUploader | dynamicRow):'
                            . PHP_EOL . 'Press enter to stop adding options' . PHP_EOL, 'n');
                        $dynamicRowItem = $helper->ask($input, $output, $dynamicRowItemCreate);
                        if ($dynamicRowItem === 'n') {
                            break;
                        }
                        $dynamicRowItemArr['type'] = $dynamicRowItem;

                        if ($dynamicRowItem === 'select' || $dynamicRowItem === 'multiselect') {
                            $options = [];
                            for ($i = 0; $i<50; $i++) {
                                $optionCreate = new Question('Please add the <fg=green>value and label</> of the option (Format: value, label):'
                                    . PHP_EOL . 'Press enter to stop adding options' . PHP_EOL, 'n');
                                $columnToCreateAnswer = $helper->ask($input, $output, $optionCreate);
                                if ($columnToCreateAnswer === 'n') {
                                    break;
                                }
                                $optionsArr = [];
                                $option = explode(',', $columnToCreateAnswer);
                                $optionsArr['value'] = $option[0];
                                $optionsArr['label'] = $option[1];
                                array_push($options, $optionsArr);
                            }
                            $dynamicRowItemArr['options'] = $options;
                        }

                        $dynamicRowItemLabelCreate = new Question('Please add the <fg=green>Label for the dynamic item</>' . PHP_EOL, 'Demo');
                        $dynamicRowItemLabel = $helper->ask($input, $output, $dynamicRowItemLabelCreate);
                        $dynamicRowItemArr['label'] = $dynamicRowItemLabel;

                        array_push($dynamicRows, $dynamicRowItemArr);
                    }
                    $column['backend_dynamic_rows'] = $dynamicRows;
                }

                $columnBackendLabel = new Question('Define <fg=green>backend label</>' . PHP_EOL, 'Label');
                $columnBackendLabelAnswer = $helper->ask($input, $output, $columnBackendLabel);
                $column['backend_label'] = $columnBackendLabelAnswer;

                if ($multipleFieldsets) {
                    $columnBackendFieldset = new Question('Define <fg=green>backend fieldset</>' . PHP_EOL, 'general');
                    $columnBackendFieldsetAnswer = $helper->ask($input, $output, $columnBackendFieldset);
                    $column['backend_fieldset'] = $columnBackendFieldsetAnswer;
                } else {
                    $column['backend_fieldset'] = 'general';
                }

                $columnBackendGridInclude = new Question('<fg=green>Display this column on grid?</> (y/n)' . PHP_EOL, 'y');
                $columnBackendGridIncludeAnswer = $helper->ask($input, $output, $columnBackendGridInclude);
                $column['backend_grid'] = $columnBackendGridIncludeAnswer;

                array_push($columns, $column);
            }
        }
        if ($createDb) {
            $resp = $this->dbSchemaStructure->generateDbSchemaXmlFile($vendorNamespace, $tableToCreateAnswer, $columns);
            $moduleArr = explode('_', $vendorNamespace);
            if ($resp['success']) {
                array_push($this->outputsArr, '<fg=green>Generated:</> ' . $moduleArr[0] . '/' . $moduleArr[1] . '/etc/db_schema.xml');
            } else {
                $output->writeln($resp['message']);
            }
        }
        $dbInfo['db_name'] = $tableToCreateAnswer;
        $dbInfo['columns'] = $columns;
        return $dbInfo;
    }

    /**
     * @param $input
     * @param $output
     * @param $module
     * @param $dbInfo
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
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
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . '.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . $entityName . 'Repository.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . 'ResourceModel' . '/' . $entityName . '.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . 'ResourceModel' . '/' . $entityName . '/' . 'Collection.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/' . 'ResourceModel' . '/' . $entityName . '/' . 'Grid' . '/' . 'Collection.php');
        } else {
            $output->writeln($resp['message']);
        }
        return $entityName;
    }

    /**
     * @param $output
     * @param $module
     * @param $dbInfo
     * @param $entityName
     */
    public function createDiXml($output, $module, $dbInfo, $entityName)
    {
        $vendorNamespaceArr = explode('_', $module);
        $dbColumns = $dbInfo['columns'];
        $dbName = $dbInfo['db_name'];
        $resp = $this->diXmlStructure->generateDiXmlFile($vendorNamespaceArr, $dbColumns, $entityName, $dbName);
        if ($resp['success']) {
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc/di.xml');
        } else {
            $output->writeln($resp['message']);
        }
    }

    /**
     * @param $input
     * @param $output
     * @param $module
     * @param $entityName
     * @param $dbInfo
     * @return mixed
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createBackendControllers($input, $output, $module, $entityName, $dbInfo)
    {
        $vendorNamespaceArr = explode('_', $module);
        $helper = $this->questionHelper();
        /*$frontNameQuestion = new Question('Define the backend <fg=green>router frontName</>: (no dashes or spaces allowed)' . PHP_EOL, 'demo-entity-frontend');
        $frontName = $helper->ask($input, $output, $frontNameQuestion);*/
        $frontName = $this->helper->convertToSnakeCase($entityName);;

        $menuPositionQuestion = new Question('Define where on the backend it should appear:' . PHP_EOL .
            'Examples: menu_root | Magento_Backend::content | Magento_Customer::customer | Magento_Catalog::catalog | Magento_Catalog::catalog_products | ' . PHP_EOL .
            'Magento_Catalog::catalog_categories | Magento_Sales::sales | Magento_Sales::sales_order | Magento_Sales::sales_shipment ' . PHP_EOL, 'menu_root');
        $menuPosition = $helper->ask($input, $output, $menuPositionQuestion);
        $dbColumns = $dbInfo['columns'];
        $dbName = $dbInfo['db_name'];
        $resp = $this->backendControllersStructure->generateBackendRoutesAndControllers($vendorNamespaceArr, $entityName, $dbColumns, $dbName, $frontName, $menuPosition);
        if ($resp['success']) {
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc/routes.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/etc/menu.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/Index/Index.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Add.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Delete.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Duplicate.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Edit.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Save.php');
//            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Upload.php');
        } else {
            $output->writeln($resp['message']);
        }
        return $frontName;
    }

    /**
     * @param $output
     * @param $module
     * @param $entityName
     * @param $dbInfo
     * @param $frontName
     */
    public function createBackendBlocks($output, $module, $entityName, $dbInfo, $frontName)
    {
        $vendorNamespaceArr = explode('_', $module);
        $dbColumns = $dbInfo['columns'];
        $dbName = $dbInfo['db_name'];
        $resp = $this->backendBlocksStructure->generateBlockFiles($vendorNamespaceArr, $entityName, $frontName);
        if ($resp['success']) {
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/BackButton.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/DeleteButton.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/DuplicateButton.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/GenericButton.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/SaveAndContinueButton.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Block/Adminhtml/' . $entityName . '/SaveButton.php');
//            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Controller/Adminthml/' . $entityName . '/Upload.php');
        } else {
            $output->writeln($resp['message']);
        }
    }

    /**
     * @param $input
     * @param $output
     * @param $module
     * @param $entityName
     * @param $dbInfo
     * @param $frontName
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function createUiFiles($output, $module, $entityName, $dbInfo, $frontName)
    {
        $vendorNamespaceArr = explode('_', $module);
        $dbColumns = $dbInfo['columns'];
        $resp = $this->uiFolderStructure->generateUiFolderFiles($vendorNamespaceArr, $entityName, $dbColumns, $frontName);
        if ($resp['success']) {
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component/Listing/Column/Actions.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Ui/Component/DataProvider.php');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/Model/Block/DataProvider.php');
        } else {
            $output->writeln($resp['message']);
        }
    }

    /**
     * @param $output
     * @param $module
     * @param $entityName
     * @param $dbInfo
     * @param $frontName
     * @param $input
     */
    public function generateLayoutAndComponentFiles($output, $module, $entityName, $dbInfo, $frontName, $input)
    {
        $helper = $this->questionHelper();
        $uiFormQuestion = new Question('Should the form have 1 or 2 Columns? (1 or 2)' . PHP_EOL);
        $uiFormStyle = $helper->ask($input, $output, $uiFormQuestion);

        $snakeCaseEntityName = $this->helper->convertToSnakeCase($entityName);
        $vendorNamespaceArr = explode('_', $module);
        $dbColumns = $dbInfo['columns'];
        $resp = $this->viewAndLayoutStructure->generateViewAndLayoutFiles($vendorNamespaceArr, $entityName, $dbColumns, $frontName, $uiFormStyle);
        if ($resp['success']) {
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/layout/' . $snakeCaseEntityName . '_' . strtolower($entityName) . '_add.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/layout/' . $snakeCaseEntityName . '_' . strtolower($entityName) . '_edit.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/layout/' . $snakeCaseEntityName . '_index_index.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/ui_component/' . $snakeCaseEntityName . '_grid.xml');
            array_push($this->outputsArr, '<fg=green>Generated:</> ' . $vendorNamespaceArr[0] . '/' . $vendorNamespaceArr[1] . '/view/adminhtml/ui_component/' . $snakeCaseEntityName . '_form.xml');
        } else {
            $output->writeln($resp['message']);
        }
    }

    /**
     * @return mixed|\Symfony\Component\Console\Helper\QuestionHelper
     */
    public function questionHelper()
    {
        return $this->getHelper('question');
    }
}
