<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace ShipperHQ\Shipper\Setup;

//use Magento\Framework\Setup\InstallDataInterface;

//use Magento\Framework\Setup\InstallSchemaInterface;
//use Magento\Framework\Setup\ModuleContextInterface;
//use Magento\Framework\Setup\SchemaSetupInterface;

use Magento\Catalog\Setup\CategorySetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Quote\Setup\QuoteSetupFactory;
use Magento\Sales\Setup\SalesSetupFactory;

/**
 * @codeCoverageIgnore
 */
//class InstallSchema implements InstallSchemaInterface
class InstallData implements InstallDataInterface

{

    /**
     * Category setup factory
     *
     * @var CategorySetupFactory
     */
    protected $categorySetupFactory;

    /**
     * Quote setup factory
     *
     * @var QuoteSetupFactory
     */
    protected $quoteSetupFactory;

    /**
     * Sales setup factory
     *
     * @var SalesSetupFactory
     */
    protected $salesSetupFactory;


    /**
     * Init
     *
     * @param CategorySetupFactory $categorySetupFactory
     * @param QuoteSetupFactory $quoteSetupFactory
     * @param SalesSetupFactory $salesSetupFactory
     */
    public function __construct(
        CategorySetupFactory $categorySetupFactory,
        QuoteSetupFactory $quoteSetupFactory,
        SalesSetupFactory $salesSetupFactory
    ) {
        $this->categorySetupFactory = $categorySetupFactory;
        $this->quoteSetupFactory = $quoteSetupFactory;
        $this->salesSetupFactory = $salesSetupFactory;
    }
    
    
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;

        $installer->startSetup();

        $catalogSetup = $this->categorySetupFactory->create(['setup' => $setup]);

        /* ------ shipperhq_shipping_group -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_shipping_group', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Shipping Group',
            'global' => \Magento\Catalog\Model\Resource\Eav\Attribute::SCOPE_STORE,
            'visible'                  => true,
            'required'                 => false,
            'visible_on_front'         => false,
            'is_html_allowed_on_front' => false,
            'searchable'               => false,
            'filterable'               => false,
            'comparable'               => false,
            'is_configurable'          => false,
            'unique'                   => false,
            'user_defined'			   => false,
            'used_in_product_listing'  => false
        ]);

        /* ------ shipperhq_warehouse -------- */
        $catalogSetup->addAttribute(\Magento\Catalog\Model\Product::ENTITY, 'shipperhq_warehouse', [
            'type'                     => 'text',
            'backend'                  => 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend',
            'input'                    => 'multiselect',
            'label'                    => 'Origin',
            'global'                   => false,
            'visible'                  => 1,
            'required'                 => 0,
            'visible_on_front'         => 0,
            'is_html_allowed_on_front' => 0,
            'searchable'               => 0,
            'filterable'               => 0,
            'comparable'               => 0,
            'is_configurable'          => 0,
            'unique'                   => false,
            'user_defined'			   => false,
            'used_in_product_listing'  => false
        ]);


        $entityTypeId = $catalogSetup->getEntityTypeId(\Magento\Catalog\Model\Product::ENTITY);

        $attributeSetArr = $catalogSetup->getAllAttributeSetIds($entityTypeId);


        $stdAttributeCodes = ['shipperhq_shipping_group' => '1',  'shipperhq_warehouse' => '10'];


        foreach ($attributeSetArr as $attributeSetId) {

            $catalogSetup->addAttributeGroup($entityTypeId, $attributeSetId, 'Shipping', '99');

            $attributeGroupId = $catalogSetup->getAttributeGroupId($entityTypeId, $attributeSetId, 'Shipping');

            foreach($stdAttributeCodes as $code => $sort) {
                $attributeId = $catalogSetup->getAttributeId($entityTypeId, $code);
                $catalogSetup->addAttributeToGroup($entityTypeId, $attributeSetId, $attributeGroupId, $attributeId, $sort);
            }

        };

        /** @var \Magento\Quote\Setup\QuoteSetup $quoteSetup */
        $quoteSetup = $this->quoteSetupFactory->create(['setup' => $setup]);
        $salesSetup = $this->salesSetupFactory->create(['setup' => $setup]);


        $carrier_type = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier Type'];
        $entities = ['quote_address', 'quote_address_rate'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carrier_type', $carrier_type);
        }
        $salesSetup->addAttribute('order', 'carrier_type', $carrier_type);

        $carrier_id = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier ID'];
        $entities = ['quote_address_rate', 'quote_address'];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carrier_id', $carrier_id);
        }
        $salesSetup->addAttribute('order', 'carrier_id', $carrier_id);

        $carrier_group_id = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'Carrier Group ID'];
        $entities = ['quote_address_rate', 'quote_item', 'quote_address_item' ];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_id', $carrier_group_id);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup_id', $carrier_group_id);

        $carrier_group = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier Group'];
        $entities = ['quote_address_rate', 'quote_item', 'quote_address_item' ];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup', $carrier_group);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup', $carrier_group);

        $carrierGroupDetails = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier Group Details'];
        $entities = ['quote_address_rate','quote_address' ];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_shipping_details', $carrierGroupDetails);
        }
        $salesSetup->addAttribute('order', 'carriergroup_shipping_details', $carrierGroupDetails);

        $isCheckout = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 'visible' => false, 'required' => true, 'default' => 0, 'comment' => 'ShipperHQ Checkout Flag'];
        $quoteSetup->addAttribute('quote_address', 'is_checkout', $isCheckout);

        $splitRates = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 'visible' => false, 'required' => true, 'default' => 0, 'comment' => 'ShipperHQ Split Rates Flag'];
        $quoteSetup->addAttribute('quote_address', 'split_rates', $splitRates);

        $displayMerged = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 'visible' => false, 'required' => true, 'default' => 1, 'comment' => 'ShipperHQ Checkout Display Type'];
        $quoteSetup->addAttribute('quote_address', 'checkout_display_merged', $displayMerged);

        $carriergroupHtml = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Carrier Group HTML'];
        $quoteSetup->addAttribute('quote_address' , 'carriergroup_shipping_html', $carriergroupHtml);
        $salesSetup->addAttribute('order', 'carriergroup_shipping_html', $carriergroupHtml);

        $carriergroupShipping = ['type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 'visible' => false, 'required' => false, 'comment' => 'ShipperHQ Shipping Description'];
        $entities = ['quote_item', 'quote_address_item' ];
        foreach ($entities as $entity) {
            $quoteSetup->addAttribute($entity, 'carriergroup_shipping', $carriergroupShipping);
        }
        $salesSetup->addAttribute('order_item', 'carriergroup_shipping', $carriergroupShipping);


    }
}