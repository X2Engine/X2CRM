<?php
/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::import ('application.modules.contacts.models.*');
Yii::import ('application.modules.accounts.models.*');

class TagBehaviorTest extends X2DbTestCase {

    public static function referenceFixtures () {
        return array (
            'contacts' => 'Contacts',
            'tags' => 'Tags',
        );
    }

    public function testAddTags () {
        $contact = $this->contacts ('testAnyone');
        $tags = array ('test', 'test2', 'test3');
        $contact->addTags ($tags);
        $contactTags = $contact->getTags (true);
        $this->assertEquals (
            Tags::normalizeTags ($tags), ArrayUtil::sort ($contactTags));
        // disallow duplicates
        $contact->addTags ($tags);
        $contactTags = $contact->getTags (true);
        $this->assertEquals (
            Tags::normalizeTags ($tags), ArrayUtil::sort ($contactTags));
        // disallow duplicates after normalization
        $tags = array ('te,st', 'test2,', '#test3');
        $contact->addTags ($tags);
        $contactTags = $contact->getTags (true);
        $this->assertEquals (
            Tags::normalizeTags ($tags), ArrayUtil::sort ($contactTags));
    }

    public function testRemoveTags () {
        $contact = $this->contacts ('testAnyone');
        Yii::app()->db->createCommand ("
            delete from x2_tags where type='Contacts' and itemId=:id
        ")->execute (array (':id' => $contact->id));
        $tags = array ('test', 'test2', 'test3');
        $contact->addTags ($tags);
        $contact->removeTags ($tags);
        $this->assertEquals (array (), $contact->getTags (true));

        $tags = array ('test', 'test2', 'test3');
        $contact->addTags ($tags);
        $tags = array ('t,est', 'test2,');
        $contact->removeTags ($tags);
        $this->assertEquals (array ('#test3'), $contact->getTags (true));
    }

    public function testClearTags () {
        $contact = $this->contacts ('testAnyone');
        Yii::app()->db->createCommand ("
            delete from x2_tags where type='Contacts' and itemId=:id
        ")->execute (array (':id' => $contact->id));
        $tags = array ('test', 'test2', 'test3');
        $contact->addTags ($tags);
        $contact->clearTags ();
        $this->assertEquals (array (), $contact->getTags (true));
    }

    public function testHasTag () {
        $contact = $this->contacts ('testAnyone');
        $contact->clearTags ();
        $tags = array ('test', 'test2', 'test3');
        foreach ($tags as $tag) { 
            $this->assertFalse ($contact->hasTag ($tag, null, true));
        }
        $contact->addTags ($tags);
        $contactTags = $contact->getTags (true);
        $this->assertEquals (
            Tags::normalizeTags ($tags), ArrayUtil::sort ($contactTags));
        foreach ($tags as $tag) { 
            $this->assertTrue ($contact->hasTag ($tag, null, true));
        }
    }

    public function testMatchTags () {
        $contact = $this->contacts ('testAnyone');
        $tags = array ('#test', '#test2', '#test3', '#test-test4');
        $this->assertEquals ($tags, $contact->matchTags (implode (' , ', $tags)));
    }

    public function testScanForTags () {
        $contact = $this->contacts ('testAnyone');
        $contact->clearTags ();
        $expectedTags = array ('#test', '#test2', '#test3', '#test4', '#test-test');
        $badTags = array ('#test5', '#test6', '#test7', '#test-');
        $bgInfo = 
'#test  #test2 #test3
#test-test
#test4
#test-
<style> 
#test5 {
    background: #test6
}
</style> <span style=" #test7 "></span>';
        foreach (array_merge ($badTags, $expectedTags) as $tag) { 
            $this->assertFalse ($contact->hasTag ($tag, null, true));
        }
        $contact->backgroundInfo = $bgInfo;
        $contactTags = $contact->scanForTags ();
        $this->assertEquals (ArrayUtil::sort ($expectedTags), ArrayUtil::sort ($contactTags));
    }

    public function testAfterSave () {
        $contact = $this->contacts ('testAnyone');
        $contact->clearTags ();
        $tags = array ('#test', '#test2', '#test3');
        foreach ($tags as $tag) { 
            $this->assertFalse ($contact->hasTag ($tag, null, true));
        }
        $contact->backgroundInfo = implode (' , ', $tags);
        $this->assertSaves ($contact);
        foreach ($tags as $tag) { 
            $this->assertTrue ($contact->hasTag ($tag, null, true));
        }
    }
    
}

?>
