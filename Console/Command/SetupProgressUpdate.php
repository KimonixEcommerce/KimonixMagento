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

use Kimonix\Kimonix\Model\Jobs;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Area;

/**
 * Kimonix - Manual setup progress update
 */
class SetupProgressUpdate extends Command
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('kimonix:setup-progress-update')
            ->setDescription('Manually check and update Kimonix setup progress.');
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
            $this->getObjectManager()->get(Jobs\SetupProgressUpdate::class)
                ->initConfig([
                    "output" => $output,
                ])->execute();
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
