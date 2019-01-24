<?php

/**
 * @package     Codilar
 * @author      Codilar Technologies
 * @license     https://opensource.org/licenses/OSL-3.0 Open Software License v. 3.0 (OSL-3.0)
 * @link        http://www.codilar.com/
 */

namespace Codilar\Directory\Setup;

use Magento\Directory\Helper\Data;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;


class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var Data
     */
    private $data;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * InstallSchema constructor.
     * @param Data $data
     * @param ResourceConnection $resourceConnection
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        Data $data,
        ResourceConnection $resourceConnection,
        ModuleDataSetupInterface $moduleDataSetup
    )
    {
        $this->data = $data;
        $this->resourceConnection = $resourceConnection;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $this->addCountryRegions($this->moduleDataSetup->getConnection(), $this->getCountryStatesData());
        $setup->endSetup();
    }

    /**
     * @return array
     */
    protected function getCountryStatesData()
    {
        return [
            ['IN', 'AN', 'Andaman and Nicobar Islands'],
            ['IN', 'AP', 'Andhra Pradesh'],
            ['IN', 'AR', 'Arunachal Pradesh'],
            ['IN', 'AS', 'Assam'],
            ['IN', 'BR', 'Bihar'],
            ['IN', 'CH', 'Chandigarh'],
            ['IN', 'CT', 'Chhattisgarh'],
            ['IN', 'DN', 'Dadra and Nagar Haveli'],
            ['IN', 'DD', 'Daman and Diu'],
            ['IN', 'DL', 'Delhi'],
            ['IN', 'GA', 'Goa'],
            ['IN', 'GJ', 'Gujarat'],
            ['IN', 'HR', 'Haryana'],
            ['IN', 'HP', 'Himachal Pradesh'],
            ['IN', 'JK', 'Jammu and Kashmir'],
            ['IN', 'JH', 'Jharkhand'],
            ['IN', 'KA', 'Karnataka'],
            ['IN', 'KL', 'Kerala'],
            ['IN', 'LD', 'Lakshadweep'],
            ['IN', 'MP', 'Madhya Pradesh'],
            ['IN', 'MH', 'Maharashtra'],
            ['IN', 'MN', 'Manipur'],
            ['IN', 'ML', 'Meghalaya'],
            ['IN', 'MZ', 'Mizoram'],
            ['IN', 'NL', 'Nagaland'],
            ['IN', 'OR', 'Odisha'],
            ['IN', 'PY', 'Puducherry'],
            ['IN', 'PB', 'Punjab'],
            ['IN', 'RJ', 'Rajasthan'],
            ['IN', 'SK', 'Sikkim'],
            ['IN', 'TN', 'Tamil Nadu'],
            ['IN', 'TG', 'Telangana'],
            ['IN', 'TR', 'Tripura'],
            ['IN', 'UP', 'Uttar Pradesh'],
            ['IN', 'UT', 'Uttarakhand'],
            ['IN', 'WB', 'West Bengal']
        ];
    }

    /**
     * @param AdapterInterface $adapter
     * @param array $data
     */
    public function addCountryRegions(AdapterInterface $adapter, array $data)
    {
        /**
         * Fill table directory/country_region
         * Fill table directory/country_region_name for en_US locale
         */
        foreach ($data as $row) {
            $bind = ['country_id' => $row[0], 'code' => $row[1], 'default_name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region'), $bind);
            $regionId = $adapter->lastInsertId($this->resourceConnection->getTableName('directory_country_region'));
            $bind = ['locale' => 'en_US', 'region_id' => $regionId, 'name' => $row[2]];
            $adapter->insert($this->resourceConnection->getTableName('directory_country_region_name'), $bind);
        }
        /**
         * Upgrade core_config_data general/region/state_required field.
         */
        $countries = $this->data->getCountryCollection()->getCountriesWithRequiredStates();
        $adapter->update(
            $this->resourceConnection->getTableName('core_config_data'),
            [
                'value' => implode(',', array_keys($countries))
            ],
            [
                'scope="default"',
                'scope_id=0',
                'path=?' => Data::XML_PATH_STATES_REQUIRED
            ]
        );
    }
}