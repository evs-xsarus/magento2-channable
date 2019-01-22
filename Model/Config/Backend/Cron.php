<?php
/**
 * Copyright © 2019 Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magmodules\Channable\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Config\ReinitableConfigInterface;

/**
 * Class Cron
 *
 * @package Magmodules\Channable\Model\Config\Backend
 */
class Cron extends Value
{

    const CRON_STRING_PATH = 'crontab/default/jobs/channable_item_update/schedule/cron_expr';
    /**
     * @var ValueFactory
     */
    private $configValueFactory;
    /**
     * @var ReinitableConfigInterface
     */
    private $reinitConfig;

    /**
     * Cron constructor.
     *
     * @param Context                   $context
     * @param Registry                  $registry
     * @param ScopeConfigInterface      $config
     * @param ReinitableConfigInterface $reinitConfig
     * @param TypeListInterface         $cacheTypeList
     * @param ValueFactory              $configValueFactory
     * @param AbstractResource|null     $resource
     * @param AbstractDb|null           $resourceCollection
     * @param string                    $runModelPath
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        ReinitableConfigInterface $reinitConfig,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->reinitConfig = $reinitConfig;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return \Magento\Framework\App\Config\Value
     * @throws LocalizedException
     */
    public function afterSave()
    {
        $expression = $this->getData('groups/item/fields/cron_frequency/value');

        if ($expression == 'custom') {
            $expression = trim($this->getData('groups/item/fields/custom_frequency/value'));
        }

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $expression
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the cron expression.'));
        }

        $this->reinitConfig->reinit();
        return parent::afterSave();
    }
}
