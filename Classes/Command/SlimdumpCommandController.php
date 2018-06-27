<?php

namespace Nemo64\Slimdump\Command;


use Helhum\Typo3Console\Mvc\Controller\CommandController;
use Symfony\Component\Console\Input\ArrayInput;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Webfactory\Slimdump\SlimdumpApplication;

class SlimdumpCommandController extends CommandController
{
    /**
     * Create a slimdump export.
     *
     * Prepares a slimdump command with connection credentials from typo3 configuration.
     * This command will also search for configurations inside of extensions.
     * Those configurations must be in Resources/Private/Slimdump/{name}.xml
     * You can specify which preset to use by using --config minimal (it uses "default" by default).
     *
     * @param string $connection Which typo3 connection to use
     * @param array $config A list of config files and/or presets to use.
     *
     * @throws \Exception
     */
    public function runCommand(string $connection = 'Default', array $config = ['default'])
    {
        if (!$GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][$connection]) {
            $this->error("Connection <info>$connection</info> does not exist.");
            return;
        }
        $connection = $GLOBALS['TYPO3_CONF_VARS']['DB']['Connections'][$connection];

        $finalConfigFiles = [];
        foreach ($config as $configFile) {
            if (file_exists($configFile)) {
                $finalConfigFiles[] = $configFile;
                continue;
            }

            $configFileFound = false;
            foreach (ExtensionManagementUtility::getLoadedExtensionListArray() as $extKey) {
                $extConfigFile = ExtensionManagementUtility::extPath($extKey, 'Resources/Private/Slimdump/' . $configFile . '.xml');
                if (file_exists($extConfigFile)) {
                    $finalConfigFiles[] = $extConfigFile;
                    $configFileFound = true;
                }
            }

            if (!$configFileFound) {
                $this->error("Config file <info>$configFile</info> not found.");
                return;
            }
        }

        $databaseDsn = http_build_url([
            'scheme' => 'mysql',
            'user' => $connection['user'],
            'pass' => $connection['password'],
            'host' => $connection['host'],
            'port' => $connection['port'],
            'path' => '/' . $connection['dbname'],
        ]);

        $application = new SlimdumpApplication();
        $input = new ArrayInput([
            'dsn' => $databaseDsn,
            'config' => $finalConfigFiles,
        ]);

        $exitCode = $application->run($input, $this->output->getSymfonyConsoleOutput());
        $this->response->setExitCode($exitCode);
    }

    protected function error(string $message)
    {
        $this->output->getSymfonyConsoleOutput()->getErrorOutput()->writeln($message);
        $this->response->setExitCode(1);
    }
}