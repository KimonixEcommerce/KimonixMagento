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

use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Area;

/**
 * Kimonix - Manual sync
 */
class Sync extends Command
{
    /**#@+
     * Keys and shortcuts for input arguments and options
     */
    const LIMIT = 'limit';
    const ENTITY = 'entity';
    /**#@- */

    /**
     * @var array
     */
    private $jobModelsMap = [
        'orders' => \Kimonix\Kimonix\Model\Jobs\OrdersSync::class,
        'products' => \Kimonix\Kimonix\Model\Jobs\ProductsSync::class,
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('kimonix:sync')
            ->setDescription('Manually sync orders/products to Kimonix')
            ->setDefinition([
                new InputOption(
                    self::ENTITY,
                    '-e',
                    InputOption::VALUE_OPTIONAL,
                    'Entity type (allowed options: orders/products) Default: all',
                    null
                ),
                new InputOption(
                    self::LIMIT,
                    '-l',
                    InputOption::VALUE_OPTIONAL,
                    'Max entity items to sync. WARNING: Setting this too high (or no limit) may result in a high server load (0 = no limit).',
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
            $output->writeln('<info>' . 'Working on it ...' . '</info>');
            $entities = $input->getOption(self::ENTITY) ? [$input->getOption(self::ENTITY)] : array_keys($this->jobModelsMap);
            foreach ($entities as $entity) {
                if (!isset($this->jobModelsMap[$entity])) {
                    throw new \Exception('Entity `' . (string) $entity . '` is not allowed.');
                }
                $this->getObjectManager()->get($this->jobModelsMap[$entity])
                    ->initConfig([
                        "output" => $output,
                        "limit" => $input->getOption(self::LIMIT),
                    ])->execute();
            }
            $output->writeln('<info>' . 'Done :)' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . $e->getMessage() . '</error>');
        }
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
