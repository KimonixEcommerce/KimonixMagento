<?php
/**
 * Kimonix Module For Magento 2
 *
 * @category Kimonix
 * @package  Kimonix_Kimonix
 * @author   Developer: Pniel Cohen (Trus)
 * @author   Trus (https://www.trus.co.il/)
 */

declare (strict_types = 1);

namespace Kimonix\Kimonix\Setup\Patch\Data;

use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Catalog\Model\Category;

class CategoryAttributeKimonixControl implements DataPatchInterface
{

    /**
     * ModuleDataSetupInterface
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * EavSetupFactory
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param EavSetupFactory          $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }
    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        /** @var \Magento\Eav\Setup\EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

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
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
