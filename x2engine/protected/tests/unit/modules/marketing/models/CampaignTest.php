<?php

Yii::import("application.modules.marketing.models.*");
Yii::import("application.components.permissions.*");

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Test the Campaign Class
 *
 * @author raymond
 */
class CampaignTest extends X2TestCase {
    public function testGetAccessCriteria() {
        $campaign = new Campaign;
        $criteria = $campaign->getAccessCriteria();
        $this->assertEquals(true, $criteria instanceof CDbCriteria);
        
    }
}
