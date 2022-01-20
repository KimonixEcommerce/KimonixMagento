<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Console\Command;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Magento\Framework\App\Area;

/**
 * Kimonix - Reset sync flags or configurations
 */
class Reset extends Command
{
    const RESET_FLAGS_CONFIRM_MESSAGE = "<question>Reset Kimonix sync flags? (y/n)[n]</question>\n";
    const RESET_CONFIG_CONFIRM_MESSAGE = "<question>Reset Kimonix configurations (reset to default)? (y/n)[n]</question>\n";

    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const ENTITY = 'entity';
    /**#@- */

    /**
     * @var array
     */
    private $entityTypes = [
        'orders',
        'products',
    ];

    /**
     * @param ResourceConnection
     */
    private $resourceConnection;

    /**
     * @method __construct
     * @param  ResourceConnection     $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('kimonix:reset')
            ->setDescription('Reset Kimonix sync flags &/or configurations')
            ->setDefinition([
                new InputOption(
                    self::ENTITY,
                    '-e',
                    InputOption::VALUE_OPTIONAL,
                    'Entity type (allowed options: orders/products) Default: all',
                    null
                ),
            ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->setCrontabAreaCode();
            if ($this->confirmQuestion(self::RESET_FLAGS_CONFIRM_MESSAGE, $input, $output)) {
                $entities = $input->getOption(self::ENTITY) ? [$input->getOption(self::ENTITY)] : $this->entityTypes;
                foreach ($entities as $entity) {
                    if (!in_array($entity, $this->entityTypes)) {
                        throw new \Exception('Entity `' . (string) $entity . '` is not allowed.');
                    }
                    $output->writeln('<info>' . 'Resetting Kimonix sync flags for ' . $entity . ' ...' . '</info>');
                    $this->getObjectManager()->get(\Kimonix\Kimonix\Model\Jobs\ResetSyncFlags::class)
                        ->initConfig([
                            "output" => $output
                        ])->execute($entity);
                }
            }
            if ($this->confirmQuestion(self::RESET_CONFIG_CONFIRM_MESSAGE, $input, $output)) {
                $output->writeln('<info>' . 'Resetting Kimonix configurations ...' . '</info>');
                $this->resetConfig();
            }
            $output->writeln('<info>' . 'Done :)' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
    }

    /**
     * @method confirmQuestion
     * @param string $message
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return bool
     */
    private function confirmQuestion(string $message, InputInterface $input, OutputInterface $output)
    {
        $confirmationQuestion = new ConfirmationQuestion($message, false);
        return (bool)$this->getHelper('question')->ask($input, $output, $confirmationQuestion);
    }

    private function resetConfig()
    {
        $this->resourceConnection->getConnection()->delete(
            $this->resourceConnection->getTableName('core_config_data'),
            "path LIKE 'kimonix/%'"
        );
    }

    /**
     * @method getObjectManager
     * @return ObjectManagerInterface
     */
    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * @method setCrontabAreaCode
     * @return $this
     */
    public function setCrontabAreaCode()
    {
        try {
            $this->getObjectManager()->get(\Magento\Framework\App\State::class)
                ->setAreaCode(Area::AREA_CRONTAB);
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . "\n" . $e->getTraceAsString() . '</error>');
        }
        return $this;
    }
}
