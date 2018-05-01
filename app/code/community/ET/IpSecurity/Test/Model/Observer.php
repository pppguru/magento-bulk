<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Test_Model_Observer
 */
class ET_IpSecurity_Test_Model_Observer extends EcomDev_PHPUnit_Test_Case
{
    /**
     * Search IP in Settings (IP Rules Set) test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testIsIpInList($searchIp, $ipRulesList, $expectedResult)
    {
        /* @var $testModel ET_IpSecurity_Model_Observer*/
        $testModel = Mage::getModel('etipsecurity/observer');
        $searchResult = $testModel->isIpInList($searchIp, $ipRulesList);

        $this->assertEquals($expectedResult, $searchResult);
    }

    /**
     * Allow/Deny logic test
     *
     * @test
     * @doNotIndexAll
     * @dataProvider dataProvider
     */
    public function testIsIpAllowed($searchIp, $allowIps, $blockIps, $expectedResult)
    {
        /* @var $testModel ET_IpSecurity_Model_Observer*/
        $testModel = Mage::getModel('etipsecurity/observer');
        $searchResult = $testModel->IsIpAllowed($searchIp, $allowIps, $blockIps);

        $this->assertEquals($expectedResult, $searchResult);
    }
}