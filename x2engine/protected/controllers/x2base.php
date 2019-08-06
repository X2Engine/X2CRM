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




/**
 * Base controller for all application controllers with CRUD operations
 *
 * @package application.controllers
 */
abstract class x2base extends X2Controller {
    /*
     * Class design:
     * Basic create method (mostly overridden, but should have basic functionality to avoid using Gii
     * Index method: Ability to pass a data provider to filter properly
     * Delete method -> unviersal.
     * Basic user permissions (access rules)
     * Update method -> Similar to create
     * View method -> Similar to index
     */

    /**
     * @var string the default layout for the controller view. Defaults to '//layouts/column1',
     * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
     */
    public $layout = '//layouts/column3';

    /**
     * @var bool $noBackdrop If true, then the content will not have a backdrop
     */
    public $noBackdrop = false;
    
    /**
     * @var array context menu items. This property will be assigned to {@link CMenu::items}.
     */
    public $menu = array();

    /**
     * @var array the breadcrumbs of the current page. The value of this property will
     * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
     * for more details on how to specify this property.
     */
    public $breadcrumbs = array();
    public $portlets = array(); // This is the array of widgets on the sidebar.
    public $leftPortlets = array(); // additional menu blocks on the left mneu
    public $modelClass;
    public $actionMenu = array();
    public $leftWidgets = array();


    private $_pageTitle;

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            array(
                'application.components.filters.X2AjaxHandlerFilter',
            ),
            array(
                'application.components.filters.FileUploadsFilter'
            ),
            'setPortlets', // performs widget ordering and show/hide on each page
        );
    }

    public function behaviors() {
        return array(
            'CommonControllerBehavior' => array(
                'class' => 'application.components.behaviors.CommonControllerBehavior'),
            'X2PermissionsBehavior' => array(
                'class' => 'application.components.permissions.'.Yii::app()->params->controllerPermissions),
            'UserMailerBehavior' => array(
                'class' => 'application.components.behaviors.UserMailerBehavior'),
        );
    }

    protected function beforeAction($action = null) {
        return $this->X2PermissionsBehavior->beforeAction($action) && parent::beforeAction ($action);
    }

    public function appLockout() {
        header("HTTP/1.1 503 Unavailable");
        header("Content-type: text/plain; charset=utf-8");
        echo Yii::t('app','X2Engine is undergoing maintenance; it has been locked by an administrator. Please try again later.');
        Yii::app()->end();
    }

    public function denied() {
        throw new CHttpException(
            403, Yii::t('app','You are not authorized to perform this action.'));
    }

    /**
     * @param string $status 'success'|'failure'|'error'|'warning' 
     * @param string $message 
     */
    public function ajaxResponse ($status, $message=null) {
        $response = array ();
        $response['status'] = $status;
        if ($message !== null) $response['message'] = $message;
        return CJSON::encode ($response);
    }

    public function getModuleObj () {
        return Modules::model ()->findByAttributes (array ('name' => $this->module->name));
    }

    public function actions() {
        $actions = array_merge (parent::actions (), array(
            'ajaxGetModelAutocomplete' => array(
                'class' => 'application.components.actions.AjaxGetModelAutocompleteAction',
            ),
            'x2GridViewMassAction' => array(
                'class' => 'X2GridViewMassActionAction',
            ),
            'inlineEmail' => array(
                'class' => 'InlineEmailAction',
            ),
        ));
        if ($this->module) {
            $module = Modules::model ()->findByAttributes (array ('name' => $this->module->name));
            if ($module->enableRecordAliasing) {
                $actions = array_merge ($actions, RecordAliases::getActions ());
            }
        }
        if ($this->modelClass !== '') {
            $modelClass = $this->modelClass;
            if ($modelClass::model ()->asa ('ModelConversionBehavior')) {
                $actions = array_merge ($actions, ModelConversionBehavior::getActions ());
            }
        }
        return $actions;
    }

    /**
     * Returns rendered detail view for given model 
     * @param object $model
     */
    public function getDetailView ($model) {
        if (!is_subclass_of ($model, 'X2Model'))
            throw new CException (Yii::t ('app', '$model is not a subclass of X2Model'));

        return $this->widget ('DetailView', array(
            'model' => $model
        ), true, true);
    }

    /**
     * Renders a view with any attached scripts, WITHOUT the core scripts.
     *
     * This method fixes the problem with {@link renderPartial()} where an AJAX request with
     * $processOutput=true includes the core scripts, breaking everything on the page
     * in rendering a partial view, or an AJAX response.
     *
     * @param string $view name of the view to be rendered. See {@link getViewFile} for details
     * about how the view script is resolved.
     * @param array $data data to be extracted into PHP variables and made available to the view 
     *  script
     * @param boolean $return whether the rendering result should be returned instead of being 
     *  displayed to end users
     * @return string the rendering result. Null if the rendering result is not required.
     * @throws CException if the view does not exist
     */
    public function renderPartialAjax(
        $view, $data = null, $return = false, $includeScriptFiles = false) {

        if (($viewFile = $this->getViewFile($view)) !== false) {

            $output = $this->renderFile($viewFile, $data, true);

            $cs = Yii::app()->clientScript;
            Yii::app()->setComponent('clientScript', new X2ClientScript);
            $output = $this->renderPartial($view, $data, true);
            $output .= Yii::app()->clientScript->renderOnRequest($includeScriptFiles);
            Yii::app()->setComponent('clientScript', $cs);

            if ($return)
                return $output;
            else
                echo $output;
        } else {
            throw new CException(
                Yii::t('yii', '{controller} cannot find the requested view "{view}".', 
                    array('{controller}' => get_class($this), '{view}' => $view)));
        }
    }

    /**
     * Determines if we have permission to edit something based on the assignedTo field.
     *
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param string $action
     * @return boolean
     */
    public function checkPermissions(&$model, $action = null) {
        return $this->X2PermissionsBehavior->checkPermissions($model, $action);
    }

    /**
     * Displays a particular model.
     *
     * This method is called in child controllers
     * which pass it a model to display and what type of model it is (i.e. Contact,
     * Opportunity, Account).  It also creates an action history and provides appropriate
     * variables to the view.
     *
     * @param mixed $model The model to be displayed (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param String $type The type of the module being displayed
     */
    public function view(&$model,$type=null,$params=array()) {
        $this->noBackdrop = true;

        // should only happen when the model is known to have LinkableBehavior
        if($type === null)    // && $model->asa('LinkableBehavior') !== null)    
            $type = $model->module;

        if(!isset($_GET['ajax'])){
            $log=new ViewLog;
            $log->user=Yii::app()->user->getName();
            $log->recordType=get_class($model);
            $log->recordId=$model->id;
            $log->timestamp=time();
            $log->save();
            //printR($model, true);
            X2Flow::trigger('RecordViewTrigger',array('model'=>$model));
        }
       
        $this->render('view', array_merge($params,array(
            'model' => $model,
            'actionHistory' => $this->getHistory($model,$type),
            'currentWorkflow' => $this->getCurrentWorkflow($model->id,$type),
        )));
    }

    /**
     * Obtain the history of actions associated with a model.
     *
     * Returns the data provider that references the history.
     * @param mixed $model The model in question (subclass of {@link CActiveRecord} or {@link X2Model}
     * @param mixed $type The association type (type of the model)
     * @return CActiveDataProvider
     */
    public function getHistory(&$model, $type = null) {

        if (!isset($type))
            $type = get_class($model);

        $filters = array(
            'actions'=>' AND type IS NULL',
            'comments'=>' AND type="note"',
            'attachments'=>' AND type="attachment"',
            'all'=>''
        );

        $history = 'all';
        if(isset($_GET['history']) && array_key_exists($_GET['history'],$filters))
            $history = $_GET['history'];

        return new CActiveDataProvider('Actions',array(
            'criteria'=>array(
                'order'=>'GREATEST(createDate, IFNULL(completeDate,0), IFNULL(dueDate,0), IFNULL(lastUpdated,0)) DESC',
                'condition'=>'associationId='.$model->id.' AND associationType="'.$type.'" '.$filters[$history].' AND (visibility="1" OR assignedTo="admin" OR assignedTo="'.Yii::app()->user->getName().'")'
            )
        ));
    }

    /**
     * Obtains the current workflow for a model of given type and id.
     * Prioritizes incomplete workflows over completed ones.
     * @param integer $id the ID of the record
     * @param string $type the associationType of the record
     * @return int the ID of the current workflow (0 if none are found)
     */
    public function getCurrentWorkflow($id, $type) {
        $currentWorkflow = Yii::app()->db->createCommand()
            ->select('workflowId,completeDate,createDate')
            ->from('x2_actions')
            ->where(
                'type="workflow" AND associationType=:type AND associationId=:id',
                array(':type'=>$type,':id'=>$id))
            ->order('IF(completeDate = 0 OR completeDate IS NULL,1,0) DESC, createDate DESC')
            ->limit(1)
            ->queryRow(false);

        if($currentWorkflow === false || !isset($currentWorkflow[0])) {
            $defaultWorkflow = Yii::app()->db->createCommand("
                select x2_workflows.id
                from x2_workflows, x2_modules
                where x2_workflows.isDefault=1 or 
                    x2_modules.id=:moduleId and x2_modules.defaultWorkflow=x2_workflows.id
                limit 1
            ")->queryScalar (array (
                ':moduleId' => $this->getModuleObj ()->id
            ));
            if($defaultWorkflow !== false)
                return $defaultWorkflow;
            return 0;
        }
        return $currentWorkflow[0];
    }

    /**
     * Used in function convertUrls
     *
     * @param mixed $a
     * @param mixed $b
     * @return mixed
     */
    private static function compareChunks($a, $b) {
        return $a[1] - $b[1];
    }

    /**
     * Replaces any URL in text with an html link (supports mailto links)
     *
     * @todo refactor this out of controllers
     * @param string $text Text to be converted
     * @param boolean $convertLineBreaks
     */
    public static function convertUrls($text, $convertLineBreaks = true) {
        /* URL matching regex from the interwebs:
         * http://www.regexguru.com/2008/11/detecting-urls-in-a-block-of-text/
         */
        $url_pattern = '/\b(?:(?:https?|ftp|file):\/\/|www\.|ftp\.)(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[-A-Z0-9+&@#\/%=~_|$?!:,.])*(?:\([-A-Z0-9+&@#\/%=~_|$?!:,.]*\)|[A-Z0-9+&@#\/%=~_|$])/i';
        $email_pattern = '/(([_A-Za-z0-9-]+)(\\.[_A-Za-z0-9-]+)*@([A-Za-z0-9-]+)(\\.[A-Za-z0-9-]+)*)/i';

        /* First break the text into two arrays, one containing <a> tags and the like
         * which should not have any replacements, and another with all the text that
         * should have URLs activated.  Each piece of each array has its offset from
         * original string so we can piece it back together later
         */

        //add any additional tags to be passed over here
        $tags_with_urls = "/(<a[^>]*>.*<\/a>)|(<img[^>]*>)|(<iframe[^>]*>.*<\/iframe>)|(<script[^>]*>.*<\/script>)/i";
        $text_to_add_links = preg_split($tags_with_urls, $text, NULL, PREG_SPLIT_OFFSET_CAPTURE);
        $matches = array();
        preg_match_all($tags_with_urls, $text, $matches, PREG_OFFSET_CAPTURE);
        $text_to_leave = $matches[0];

        // Convert all URLs into html links
        foreach ($text_to_add_links as $i => $value) {
            $text_to_add_links[$i][0] = preg_replace(
                    array($url_pattern,
                $email_pattern), array("<a href=\"\\0\">\\0</a>",
                "<a href=\"mailto:\\0\">\\0</a>"), $text_to_add_links[$i][0]
            );
        }

        // Merge the arrays and sort to be in the original order
        $all_text_chunks = array_merge($text_to_add_links, $text_to_leave);

        usort($all_text_chunks, 'x2base::compareChunks');

        $new_text = "";
        foreach ($all_text_chunks as $chunk) {
            $new_text = $new_text . $chunk[0];
        }
        $text = $new_text;

        // Make sure all links open in new window, and have http:// if missing
        $text = preg_replace(
                array('/<a([^>]+)target=("[^"]+"|\'[^\']\'|[^\s]+)([^>]+)/i',
            '/<a([^>]+href="?\'?)(www\.|ftp\.)/i'), array('<a\\1 target=\\2\\3',
            '<a\\1http://\\2'), $text
        );

        //convert any tags into links
        $matches = array();
        // avoid matches that end with </span></a>, like other record links
        preg_match('/(^|[>\s\.])(#\w\w+)(?!.*<\/span><\/a>)/u', $text, $matches);
        $tags = Yii::app()->cache->get('x2_taglinks');
        if ($tags === false) {
            $dependency = new CDbCacheDependency('SELECT MAX(timestamp) FROM x2_tags');
            $tags = Yii::app()->db->createCommand()
                    ->selectDistinct('tag')
                    ->from('x2_tags')
                    ->queryColumn();
            // cache either 10min or until a new tag is added
            Yii::app()->cache->set('x2_taglinks', $tags, 600, $dependency);
        }
        if (sizeof ($matches) > 1 && $matches[2] !== null && 
            array_search($matches[2], $tags) !== false) {

            $template = "\\1<a href=" . Yii::app()->createUrl('/search/search') . 
                '?term=%23\\2' . ">#\\2</a>";
            //$text = preg_replace('/(^|[>\s\.])#(\w\w+)($|[<\s\.])/u',$template,$text);
            $text = preg_replace('/(^|[>\s\.])#(\w\w+)/u', $template, $text);
        }

        //TODO: separate convertUrl and convertLineBreak concerns
        if ($convertLineBreaks)
            return Formatter::convertLineBreaks($text, true, false);
        else
            return $text;
    }

    // Deletes a note action
    public function actionDeleteNote($id) {
        $note = X2Model::model('Actions')->findByPk($id);
        if ($note->delete()) {
            $this->redirect(array('view', 'id' => $note->associationId));
        }
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function create($model, $oldAttributes, $api) {
        if($model->save()) {
            if($api == 0)
                $this->redirect(array('view', 'id' => $model->id));
            else
                return true;
        } else {
            return false;
        }
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function update($model, $oldAttributes, $api) {
        if($model->save()) {
            if($api == 0)
                $this->redirect(array('view', 'id' => $model->id));
            else
                return true;
        } else {
            return false;
        }
    }

    /**
     * Lists all models.
     */
    public function index($model, $name) {
        $this->render('index', array('model' => $model));
    }

    /**
     * Manages all models.
     * @param $model The model to use admin on, created in a controller subclass.  The model must be constucted with the parameter 'search'
     * @param $name The name of the model being viewed (Opportunities, Actions, etc.)
     */
    public function admin($model, $name) {
        $this->render('admin', array('model' => $model));
    }

    /**
     * Search for a term.  Defined in X2Base so that all Controllers can use, but
     * it makes a call to the SearchController.
     */
    public function actionSearch() {
        $term = $_GET['term'];
        $this->redirect(Yii::app()->controller->createAbsoluteUrl('/search/search',array('term'=>$term)));
    }

    /**
     * Delete all tags associated with a model
     */
    public function cleanUpTags($model) {
        Tags::model()->deleteAllByAttributes(array('itemId' => $model->id));
    }

    
    public function decodeQuotes($str) {
        return preg_replace('/&quot;/u', '"', $str);
    }

    public function encodeQuotes($str) {
        // return htmlspecialchars($str);
        return preg_replace('/"/u', '&quot;', $str);
    }

    public function getPhpMailer($sendAs = -1) {
        $mail = new InlineEmail;
        $mail->credId = $sendAs;
        return $mail->mailer;
    }

    public function throwException($message) {
        throw new Exception($message);
    }

    public function parseEmailTo($string) {

        if (empty($string))
            return false;
        $mailingList = array();
        $splitString = explode(',', $string);

        require_once('protected/components/phpMailer/class.phpmailer.php');

        foreach ($splitString as &$token) {

            $token = trim($token);
            if (empty($token))
                continue;

            $matches = array();

            if (PHPMailer::ValidateAddress($token)) { // if it's just a simple email, we're done!
                $mailingList[] = array('', $token);
            } else if (preg_match('/^"?([^"]*)"?\s*<(.+)>$/i', $token, $matches)) {
                if (count($matches) == 3 && PHPMailer::ValidateAddress($matches[2]))
                    $mailingList[] = array($matches[1], $matches[2]);
                else
                    return false;
            } else
                return false;
        }

        if (count($mailingList) < 1)
            return false;

        return $mailingList;
    }

    public function mailingListToString($list, $encodeQuotes = false) {
        $string = '';
        if (is_array($list)) {
            foreach ($list as &$value) {
                if (!empty($value[0]))
                    $string .= '"' . $value[0] . '" <' . $value[1] . '>, ';
                else
                    $string .= $value[1] . ', ';
            }
        }
        return $encodeQuotes ? $this->encodeQuotes($string) : $string;
    }

    /**
     * Obtain the widget list for the current web user.
     *
     * @param CFilterChain $filterChain
     */
    public function filterSetPortlets($filterChain) {
        if (!Yii::app()->user->isGuest) {
            $themeURL = Yii::app()->theme->getBaseUrl();
            $this->portlets = Profile::getWidgets();
        }
        $filterChain->run();
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model) {
        if (isset($_POST['ajax'])) {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }

    /**
     * Calls renderInput for model and input type with given names and returns the result.
     */
    public function actionGetX2ModelInput ($modelName, $fieldName) {
        if (!isset ($modelName) || !isset ($fieldName)) {
            throw new CHttpException (400, 'modelName or fieldName not set');
            return;
        }
        $model = X2Model::model ($modelName);
        if (!$model) {
            throw new CHttpException (400, 'Invalid model name');
            return;
        }
        $field = $model->getField ($fieldName);
        if (!$model) {
            throw new CHttpException (400, 'Invalid field name');
            return;
        }
        $input = '';
        if ($fieldName == 'associationName') {
            $input .= CHtml::activeDropDownList(
                $model, 'associationType', 
                array_merge(
                    array(
                        'none' => Yii::t('app', 'None'), 
                        'calendar' => Yii::t('calendar', 'Calendar')), 
                    Fields::getDisplayedModelNamesList()
                ), 
                array(
                'ajax' => array(
                    'type' => 'POST', 
                    'url' => CController::createUrl('/actions/actions/parseType'), 
                    'update' => '#', //selector to update
                    'data' => 'js:$(this).serialize()',
                    'success' => 'function(data){
                        if(data){
                            $("#auto_select").autocomplete("option","source",data);
                            $("#auto_select").val("");
                            $("#auto_complete").show();
                        }else{
                            $("#auto_complete").hide();
                        }
                    }'
                )
            ));
            $input .= "<div id='auto_complete' style='display: none'>";
            $input .= $this->widget('zii.widgets.jui.CJuiAutoComplete', array(
                'name' => 'auto_select',
                'value' => $model->associationName,
                'source' => ($model->associationType !== 'Calendar' ? 
                    $this->createUrl(X2Model::model($modelName)->autoCompleteSource) : ''),
                'options' => array(
                    'minLength' => '2',
                    'select' => 'js:function( event, ui ) {
                        $("#'.CHtml::activeId($model, 'associationId').'").val(ui.item.id);
                        $(this).val(ui.item.value);
                        return false;
                    }',
                ),
            ), true);
            $input .= "</div>";
        } else {
            $input .= $model->renderInput ($fieldName);
        }

        // force loading of scripts normally rendered in view
        $input .= '<br /><br /><script id="x2-model-render-input-scripts">'."\n";
        if (isset (Yii::app()->clientScript->scripts[CClientScript::POS_READY])) {
            foreach(
                Yii::app()->clientScript->scripts[CClientScript::POS_READY] as $id => $script) {

                if(strpos($id,'logo')===false)
                $input .= "$script\n";
            }
        }
        $input .= "</script>";
        $response = array (
            'input' => $input,
            'field' => array (
                'type' => $field->type
            )
        );
        echo CJSON::encode ($response);
    }

    /**
     * Helper method to hide specific menu options or unset
     * links before the menu is rendered
     * @param array $menuItems Original menu items
     * @param array|true $selectOptions Menu items to include. If set to true, all default menu
     *  items will get displayed
     */
    protected function prepareMenu(&$menuItems, $selectOptions) {
        if ($selectOptions === true) {
            $selectOptions = array_map (function ($item) {
                return $item['name'];
            }, $menuItems);
        }
        $currAction = $this->action->id;
        for ($i = count($menuItems) - 1; $i >= 0; $i--) {
            // Iterate over the items from the end to avoid consistency issues
            // while items are being removed
            $item = $menuItems[$i];

            // Remove requested items
            if (!in_array($item['name'], $selectOptions)) {
                unset($menuItems[$i]);
            }
            // Hide links to requested items
            else if ((is_array($item['url']) && in_array($currAction, $item['url']))
                    || $item['url'] === $currAction) {
                unset($menuItems[$i]['url']);
            }
        }
    }

    protected function renderLayout ($layoutFile, $output) {
        $output = $this->renderFile (
            $layoutFile,
            array (
                'content'=>$output
            ), true);
        return $output;
    }

    /**
     * Override parent method so that layout business logic can be moved to controller 
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public function render($view,$data=null,$return=false)
    {
        
        if($this->beforeRender($view))
        {
            
            $output=$this->renderPartial($view,$data,true);
            /* x2modstart */ 
            if(($layoutFile=$this->getLayoutFile($this->layout))!==false) {
                $output = $this->renderLayout ($layoutFile, $output);
            }
            /* x2modend */ 

            $this->afterRender($view,$output);

            $output=$this->processOutput($output);

            if($return)
                return $output;
            else
                echo $output;
        }
    }

    /**
     * Overrides parent method so that x2base's _pageTitle property is used instead of 
     * CController's.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function setPageTitle($value) {
        $this->_pageTitle = $value;
    }

    /**
     * Overrides parent method so that configurable app name is used instead of name
     * from the config file.
     *
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
    public function getPageTitle() {
        if($this->_pageTitle!==null) {
            return $this->_pageTitle;
        } else {
            $name=ucfirst(basename($this->getId()));

             
             /* x2modstart */ 
            // Try and load the configured module name
            $moduleName = Modules::displayName(true, $name);
            if (!empty($moduleName))
                $name = $moduleName;
            /* x2modend */ 

            if($this->getAction()!==null && 
               strcasecmp($this->getAction()->getId(),$this->defaultAction)) {

                return $this->_pageTitle=
                    /* x2modstart */Yii::app()->settings->appName/* x2modend */.' - '.
                        ucfirst($this->getAction()->getId()).' '.$name;
            } else {
                return $this->_pageTitle=
                    /* x2modstart */Yii::app()->settings->appName/* x2modend */.' - '.$name;
            }
        }
    }

    /**
     * Assumes that convention of (<module name> === ucfirst (<modelClass>)) is followed. 
     * @return Module module associated with this controller.  
     */
    /*public function getModuleModel () {
        return Modules::model()->findByAttributes (array ('name' => ucfirst ($this->modelClass)));
    }*/

    /**
     * Overridden to add $run param
     * 
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/
     */
	public function widget($className,$properties=array(),$captureOutput=false,$run=true)
	{
		if($captureOutput)
		{
			ob_start();
			ob_implicit_flush(false);
			try
			{
				$widget=$this->createWidget($className,$properties);
                /* x2modstart */ 
                if ($run) $widget->run();
                /* x2modend */ 
			}
			catch(Exception $e)
			{
				ob_end_clean();
				throw $e;
			}
			return ob_get_clean();
		}
		else
		{
			$widget=$this->createWidget($className,$properties);
            /* x2modstart */ 
            if ($run) $widget->run();
            /* x2modend */ 
			return $widget;
		}
	}


    public function actionQuickView ($id) {
        $model = $this->loadModel($id);
        if (!FormLayout::model()->findByAttributes (array ('model' => get_class ($model)))) {
            echo Yii::t('app', 'Quick view not supported');
        }
        if ($this->checkPermissions($model, 'view')) {
            $that = $this;
            X2Widget::ajaxRender (function () use ($model, $that) {
                $that->widget ('DetailView', array_merge(array(
                    'model' => $model,
                    'scenario' => 'Inline',
                    'nameLink' => true
                )));
            });
            return;
        }
        throw new CHttpException (403);
    }

    /**
     * DUMMY METHOD: left to avoid breaking old custom modules (now done in ChangeLogBehavior)
     * @deprecated
     */
    protected function updateChangelog($model, $changes) {
        return $model;
    }

    /**
     * DUMMY METHOD: left to avoid breaking old custom modules (now done in ChangeLogBehavior)
     * @deprecated
     */
    protected function calculateChanges($old, $new, &$model = null) {
        return array();
    }

    protected function getModelFromTypeAndId ($modelName, $modelId, $x2ModelOnly=true) {
        $model = X2Model::getModelOfTypeWithId ($modelName, $modelId);
        if (!$model || ($x2ModelOnly && !($model instanceof X2Model))) {
            throw new CHttpException (400, Yii::t('app', 'Invalid record type or record id')); 
        }
        return $model;
    }

    protected function getModelsFromTypeAndId (array $recordInfo) {
        // validate record info and look up models
        foreach ($recordInfo as $info) {
            $model = $this->getModelFromTypeAndId ($info[0], $info[1]);
            $models[] = $model;
        }
        return $models;
    }
}
