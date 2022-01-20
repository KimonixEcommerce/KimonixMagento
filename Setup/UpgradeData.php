<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

namespace Kimonix\Kimonix\Setup;

use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Upgrade Data script
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     * @method __construct
     * @param  EavSetupFactory   $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        if (!$eavSetup->getAttributeId(Category::ENTITY, 'kimonix_control')) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'kimonix_control',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Kimonix Control',
                    'comment' => 'Kimonix Control',
                    'input' => 'int',
                    'class' => '',
                    'source' => Boolean::class,
                    'sort_order' => 50,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'nullable' => true,
                    'visible_on_front' => false,
                    'group' => 'General Information',
                ]
            );
        }

        if (!$eavSetup->getAttributeId(Category::ENTITY, 'kimonix_dynamic_fetch')) {
            $eavSetup->addAttribute(
                Category::ENTITY,
                'kimonix_dynamic_fetch',
                [
                    'type' => 'int',
                    'backend' => '',
                    'frontend' => '',
                    'label' => 'Kimonix Dynamic Fetch',
                    'comment' => 'Kimonix Dynamic Fetch',
                    'input' => 'int',
                    'class' => '',
                    'source' => Boolean::class,
                    'sort_order' => 50,
                    'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                    'visible' => true,
                    'required' => false,
                    'user_defined' => false,
                    'default' => '0',
                    'nullable' => true,
                    'visible_on_front' => false,
                    'group' => 'General Information',
                ]
            );
        }

        $setup->endSetup();
    }
}
