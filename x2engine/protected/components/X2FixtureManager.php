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




Yii::import('system.test.CDbFixtureManager');

/**
 * @package application.components
 */
class X2FixtureManager extends CDbFixtureManager {

    /**
     * @var bool $loadFixture
     */
    public $loadFixtures; 
    public $loadFixturesForClassOnly; 

    private $_referenceFixtures;
    public function setReferenceFixtures ($referenceFixtures) {
        $this->_referenceFixtures = $referenceFixtures;
    }

    public function __construct () {
        $this->loadFixtures = X2_LOAD_FIXTURES && !X2_LOAD_FIXTURES_FOR_CLASS_ONLY;
        $this->loadFixturesForClassOnly = X2_LOAD_FIXTURES_FOR_CLASS_ONLY;
    }

    public function prepare () {
        if ($this->loadFixtures) parent::prepare ();
    }

	/**
	 * Override of {@link CDbFixtureManager}'s resetTable 
	 * 
	 * Permits array-style definition of init scripts much like fixture files
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function resetTable($tableName) {
        /* x2modstart */ 
        if (!$this->loadFixtures && !$this->loadFixturesForClassOnly) return;
        /* x2modend */ 
		$initFile = $this->basePath . DIRECTORY_SEPARATOR . $tableName . $this->initScriptSuffix;
		if (is_file($initFile)) {
			$tbl_data = require($initFile);
			if (is_array($tbl_data)) {
				Yii::app()->db->createCommand()->truncateTable($tableName);
				foreach ($tbl_data as $rec)
					Yii::app()->db->createCommand()->insert($tableName, $rec);
			}
		} else {
			$this->truncateTable($tableName);
        }
	}

    /**
	 * Override of {@link CDbFixtureManager}'s loadFixture 
     *
     * Modified to enable fixture file suffixing. A fixture file suffix can be specified by 
     *  setting a value in the fixtures array to an array with two properties:
     *      array (<tableName|modelClass>, <file suffix>)
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function loadFixture($tableName,/* x2modstart */ $suffix=''/* x2modend */)
    {
            $fileName=$this->basePath.DIRECTORY_SEPARATOR.$tableName/* x2modstart */.$suffix.
                /* x2modend */'.php';
            if(!is_file($fileName))
                    return false;

            $rows=array();
            $schema=$this->getDbConnection()->getSchema();
            $builder=$schema->getCommandBuilder();
            $table=$schema->getTable($tableName);

            foreach(require($fileName) as $alias=>$row)
            {
                    /* x2modstart */ 
                    if ($this->loadFixtures || $this->loadFixturesForClassOnly) {
                    /* x2modend */ 
                        $builder->createInsertCommand($table,$row)->execute();
                        $primaryKey=$table->primaryKey;
                        if($table->sequenceName!==null)
                        {
                                if(is_string($primaryKey) && !isset($row[$primaryKey]))
                                        $row[$primaryKey]=$builder->getLastInsertID($table);
                                elseif(is_array($primaryKey))
                                {
                                        foreach($primaryKey as $pk)
                                        {
                                                if(!isset($row[$pk]))
                                                {
                                                        $row[$pk]=$builder->getLastInsertID($table);
                                                        break;
                                                }
                                        }
                                }
                        }
                    /* x2modstart */ 
                    }
                    /* x2modend */ 
                    $rows[$alias]=$row;
            }
            return $rows;
    }

    /**
	 * Override of {@link CDbFixtureManager}'s load 
     *
     * Modified to enable fixture file suffixing. A fixture file suffix can be specified by 
     *  setting a value in the fixtures array to an array with two properties:
     *      array (<tableName|modelClass>, <file suffix>)
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function load($fixtures)
    {
            $schema=$this->getDbConnection()->getSchema();
            $schema->checkIntegrity(false);

            $this->_rows=array();
            $this->_records=array();
            foreach($fixtures as $fixtureName=>$tableName)
            {
                    /* x2modstart */  
                    $suffix = null;
                    if (is_array ($tableName))
                    {
                        $suffix = $tableName[1];
                        $tableName = $tableName[0];
                    }
                    /* x2modend */  

                    if($tableName[0]===':')
                    {
                            $tableName=substr($tableName,1);
                            unset($modelClass);
                    }
                    else
                    {
                            $modelClass=Yii::import($tableName,true);
                            $tableName=CActiveRecord::model($modelClass)->tableName();
                    }
                    if(($prefix=$this->getDbConnection()->tablePrefix)!==null)
                            $tableName=preg_replace('/{{(.*?)}}/',$prefix.'\1',$tableName);
                    $this->resetTable($tableName);
                    $rows=$this->loadFixture($tableName/* x2modstart */,$suffix/* x2modend */);
                    if(is_array($rows) && is_string($fixtureName))
                    {
                            $this->_rows[$fixtureName]=$rows;
                            if(isset($modelClass))
                            {
                                    foreach(array_keys($rows) as $alias)
                                            $this->_records[$fixtureName][$alias]=$modelClass;
                            }
                    }
            }

            $schema->checkIntegrity(true);
    }

	/**
	 * Returns the specified ActiveRecord instance in the fixture data.
	 * @param string $name the fixture name
	 * @param string $alias the alias for the fixture data row
	 * @return CActiveRecord the ActiveRecord instance. False is returned if there is no such fixture row.
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
	 */
	public function getRecord($name,$alias)
	{
		if(isset($this->_records[$name][$alias]))
		{
			if(is_string($this->_records[$name][$alias]))
			{
				$row=$this->_rows[$name][$alias];

                /* x2modstart */ 
                if ($this->loadFixtures) {
                /* x2modend */ 
                    $model=CActiveRecord::model($this->_records[$name][$alias]);
                    $key=$model->getTableSchema()->primaryKey;
                    if(is_string($key))
                        $pk=$row[$key];
                    else
                    {
                        foreach($key as $k)
                            $pk[$k]=$row[$k];
                    }
                    $this->_records[$name][$alias]=$model->findByPk($pk);
                /* x2modstart */ 
                } else {
                    $model = CActiveRecord::model ($this->_records[$name][$alias]);
                    if (isset ($row['id'])) {
                        $this->_records[$name][$alias]=$model->findByPk($row['id']);
                    } else {
                        $this->_records[$name][$alias]=$model->findByAttributes($row);
                    }
                }
                /* x2modend */ 
			}
			return $this->_records[$name][$alias];
		}
		else
			return false;
	}

}

?>
