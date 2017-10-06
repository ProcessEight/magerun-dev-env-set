<?php

namespace ProjectEight\Magento\Command\Developer\Environment;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use N98\Magento\Command\AbstractMagentoCommand;

class SetCommand extends AbstractMagentoCommand
{
    protected function configure()
    {
        $this
            ->setName('dev:env:set')
            ->addArgument('env', InputArgument::OPTIONAL, 'An environment to configure.')
            ->setDescription('Updates the config to match values set in ~/.n98-magerun.yaml [ProjectEight]')
        ;

        $help = <<<HELP
   $ n98-magerun.phar dev:env:set [env]

Updates the config to match values set in ~/.n98-magerun.yaml. See https://github.com/ProjectEight/magerun-dev-env-set for detailed instructions

HELP;
        $this->setHelp($help);
    }

    /**
     * Ask user for environment and update it according to values in .n98-magerun.yaml
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->detectMagento($output, true);
        if (!$this->initMagento()) {
            return;
        }

        /*
         * If no environment passed, ask user for one
         */
        $environment = $input->getArgument('env');
        if ($environment === NULL) {
            $this->writeSection($output, 'Available environments');
            $environmentList = $this->getEnvironmentsList();
            $question        = array();
            foreach ($environmentList as $key => $environment) {
                $question[] = '<comment>' . str_pad('[' . ($key + 1) . ']', 4, ' ', STR_PAD_RIGHT) . '</comment> ' .
                              str_pad($environment, 40, ' ', STR_PAD_RIGHT) . "\n";
            }
            $question[] = '<question>Please select an environment to update: </question>';

            /** @var DialogHelper $dialog */
            $dialog      = $this->getHelper('dialog');
            $environment = $dialog->askAndValidate($output, $question, function ($typeInput) use ($environmentList) {
                $typeInputs = array($typeInput);

                $returnCode = NULL;
                foreach ($typeInputs as $typeInput) {
                    if (!isset($environmentList[ $typeInput - 1 ])) {
                        throw new InvalidArgumentException('Environment not found');
                    }

                    $returnCode = $environmentList[ $typeInput - 1 ];
                }

                return $returnCode;
            });
        }

        try {

            /*
             * Get config settings for environment
             */
            $this->writeSection($output, 'Updating config');

            $configSettings = $this->getEnvironmentConfig($environment);

            // ensure that n98-magerun doesn't stop after first command
            $this->getApplication()->setAutoExit(false);

            foreach ($configSettings as $configScopeCode => $configScopes) {

                foreach ($configScopes as $configScopeId => $configOptions) {

                    /*
                     * Use existing config:set command
                     */
                    foreach ($configOptions as $configPath => $configValue) {

                        $commandOptions = " --scope {$configScopeCode} ";
                        $commandOptions .= " --scope-id {$configScopeId} ";
                        $commandOptions .= $configPath . " ";
                        $commandOptions .= '"' . $configValue . '"';

                        $input = new StringInput("config:set {$commandOptions}");

                        // with output
                        $this->getApplication()->run($input, $output);
                    }
                }
            }

            // reactivate auto-exit
            $this->getApplication()->setAutoExit(true);

        } catch (Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * Get a list of the environments configured in the .n98-magerun.yaml
     *
     * @return array
     */
    protected function getEnvironmentsList()
    {
        $environmentNames = [];
        $config           = $this->getCommandConfig();
        if (isset($config['environments']) && is_array($config['environments'])) {
            foreach ($config['environments'] as $environmentName => $environmentOptions) {
                $environmentNames[] = $environmentName;
            }
        }

        return $environmentNames;
    }

    /**
     * Get the config settings for the given environment
     *
     * @param string $environment
     *
     * @return array
     */
    protected function getEnvironmentConfig($environment)
    {
        $configSettings = [];
        $config         = $this->getCommandConfig();
        if (isset($config['environments'][ $environment ]['config']) && is_array($config['environments'][ $environment ]['config'])) {
            $configSettings = $config['environments'][ $environment ]['config'];
        }

        return $configSettings;
    }

}
