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



Yii::app()->clientScript->registerCss('diagramCss',"

#app-diagram-container {
	height: 470px;
	width: 930px;
	background-image: url(".Yii::app()->theme->getBaseUrl ().'/images/map3.png'.");
}

/*
links
*/
#app-diagram-container .heading-link {
	font-size: 16px;
}

#app-diagram-container .more-link {
	font-size: 12px;
}

#app-diagram-container a {
	display: block;
	height: 20px;
	width: 205px;
}

#app-diagram-container .tmp-non-link {
	font-size: 16px;
	display: block;
	height: 20px;
	width: 215px;
}

/*
columns
*/
#app-diagram-container .col-one,
#app-diagram-container .col-two,
#app-diagram-container .col-four,
#app-diagram-container .col-three {
	height: 450px;
	float: left;
}

#app-diagram-container .col-one {
	width: 210px;
}

#app-diagram-container .col-two {
	width: 250px;
}

#app-diagram-container .col-three {
	width: 250px;
}

#app-diagram-container .col-four {
	width: 150px;
}

/*
sections
*/
/* market */
#app-diagram-container .diagram-section.market {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.market .heading-link {
	margin-top: 28px;
	margin-left: 112px;
}

#app-diagram-container .diagram-section.market .more-link {
	margin-top: 74px;
	margin-left: 118px;
}

/* sales */
#app-diagram-container .diagram-section.sales {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.sales .heading-link {
	margin-top: 13px;
	margin-left: 79px;
}

#app-diagram-container .diagram-section.sales .more-link {
	margin-top: 70px;
	margin-left: 118px;
}

/* social */
#app-diagram-container .diagram-section.social {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.social .heading-link {
	margin-top: 8px;
	margin-left: 91px;
}

#app-diagram-container .diagram-section.social .more-link {
	margin-top: 68px;
	margin-left: 118px;
}

/* admin */
#app-diagram-container .diagram-section.admin {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.admin .heading-link {
	margin-top: 42px;
	margin-left: 115px;
}

#app-diagram-container .diagram-section.admin .more-link {
	margin-top: 355px;
	margin-left: 156px;
}

/* cloud */
#app-diagram-container .diagram-section.cloud {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.cloud .heading-link {
	margin-top: 101px;
	margin-left: 49px;
}

#app-diagram-container .diagram-section.cloud .more-link {
	margin-top: 84px;
	margin-left: 168px;
}

/* download */
#app-diagram-container .diagram-section.download {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.download .heading-link {
	margin-top: 36px;
	margin-left: 70px;
}

#app-diagram-container .diagram-section.download .more-link {
	margin-top: 120px;
	margin-left: 165px;
}

/* app */
#app-diagram-container .diagram-section.app {
	width: 103px;
	height: 56px;
}

#app-diagram-container .diagram-section.app .heading-link {
	margin-top: 103px;
	margin-left: 58px;
}

#app-diagram-container .diagram-section.app .more-link {
	margin-top: 118px;
	margin-left: 127px;
}

/* conn */
#app-diagram-container .diagram-section.conn {
	width: 220px;
	height: 140px;
}

#app-diagram-container .diagram-section.conn .heading-link {
	margin-top: 144px;
	margin-left: 77px;
}

/* press */
#press-release-quote {
	font-weight: bold;
	color: #2E64FE;
	font-size: 14px;
}

.diagram-section.app .more-link {
    width: 92px !important;
}
.diagram-section.app .heading-link {
    width: 161px !important;
}
.diagram-section.conn .heading-link {
    width: auto !important;
}

");

$this->layout = '//layouts/column3';
$this->pageTitle=Yii::app()->settings->appName . ' - ' . Yii::t('admin','System Diagram');
echo "<div class='page-title'><h2>".Yii::t('admin','X2Engine 3.0 System Diagram')."</h2></div>";
echo "<div class='form'>";
?>
<div id="app-diagram-container">
<div class="col-one">
<div class="diagram-section market"><a class="heading-link" href="http://www.x2engine.com/marketing/" >Marketing</a><br> <a class="more-link" href="http://www.x2engine.com/marketing/" >Learn More</a></div>
<div class="diagram-section sales"><a class="heading-link" href="http://www.x2engine.com/sales-and-service/" >Sales &amp; Services</a><br> <a class="more-link" href="http://www.x2engine.com/sales-and-service/" >Learn More</a></div>
<div class="diagram-section social"><a class="heading-link" href="http://www.x2engine.com/social-intranet/" >Social Intranet</a><br> <a class="more-link" href="http://www.x2engine.com/social-intranet/" >Learn More</a></div>
</div>
<div class="col-two">
<div class="diagram-section admin"><a class="heading-link" href="http://www.x2engine.com/administration/" >Administrator</a><br> <a class="more-link" href="http://www.x2engine.com/administration/" >Learn More</a></div>
</div>
<div class="col-three">
<div class="diagram-section cloud"><span class="heading-link tmp-non-link">Cloud/Virtual Private Server</span></div>
<div class="diagram-section download"><a class="heading-link" href="http://www.x2engine.com/pricing-plans/" >Download/Private Cloud</a><br> <a class="more-link" href="http://www.x2engine.com/pricing-plans/" >Learn More</a></div>
</div>
<div class="col-four">
<div class="diagram-section  app"><a class="heading-link" href="http://www.x2engine.com/marketing/x2flow/" >Application Workflow</a><br> <a class="more-link" href="http://www.x2engine.com/marketing/x2flow" >Learn More</a></div>
<div class="diagram-section conn"><a class="heading-link" href="http://www.x2engine.com/data-import/" >Cloud Connectors</a></div>
</div>
</div>
<?php
echo "</div>";
