<?php
/** 
 * Build from wf/form_v2 to keep form data in variable $data.
 * Plugin to render and handle forms.
 */
class PluginFormForm_v1{
  private $i18n = null;
  public $data = array();
  function __construct($buto = false) {
    if($buto){
      wfPlugin::includeonce('wf/array');
      wfPlugin::includeonce('i18n/translate_v1');
      $this->i18n = new PluginI18nTranslate_v1();
      $this->i18n->setPath('/plugin/form/form_v1/i18n');
      wfPlugin::enable('icons/octicons');
    }
  }
  public static function translate($text){
    wfPlugin::includeonce('i18n/translate_v1');
    $i18n = new PluginI18nTranslate_v1();
    $i18n->setPath('/plugin/form/form_v1/i18n');
    return $i18n->translateFromTheme($text);
  }
  public function setData($data){
    $this->data = $data;
  }
  public function getData($key = null){
    if(!is_null($key)){
      $data = new PluginWfArray($this->data);
      return $data->get($key);
    }else{
      return $this->data;
    }
  }
  public function setDataFromFile($file, $root_path_to_key = null){
    $file = wfSettings::replaceTheme($file);
    $file = wfSettings::addRoot($file);
    if(!file_exists($file)){
      throw new Exception('PluginFormForm_v1 could not find file '.$file.'.');
    }else{
      $this->data = wfFilesystem::loadYml($file);
    }
  }
  /**
   * Primary key (only one).
   */
  private static function getSchemaFieldPrimary($field){
    $primary_key = null;
    $primary_type = null;
    foreach ($field as $key => $value) {
      $item = new PluginWfArray($value);
      if($item->get('primary_key')){
        if($primary_key){
          exit('PluginFormForm_v1 says: Table should only have one primary key.');
        }else{
          $primary_key = $key;
          if(wfPhpfunc::strstr($item->get('type'), 'varchar(')){
            $primary_type = 's';
          }elseif(wfPhpfunc::strstr($item->get('type'), 'int(')){
            $primary_type = 'i';
          }
        }
      }
    }
    if(!$primary_key){
      exit('PluginFormForm_v1 says: Table has no primary key.');
    }
    return new PluginWfArray(array('primary_key'  => $primary_key, 'primary_type' => $primary_type));
  }
  private function setFormItemsDefaultFromDb(){
    $form = new PluginWfArray($this->data);
    /**
     * Get items via schema.
     */
    $field = $this->getSchema();
    /**
     * Primary key (only one).
     */
    $primary = PluginFormForm_v1::getSchemaFieldPrimary($field);
    $primary_key = $primary->get('primary_key');
    $primary_type = $primary->get('primary_type');
    /**
     * Create select sql.
     */
    $sql = 'select ';
    foreach ($field as $key => $value) {
      $sql .= "$key, ";
    }
    $sql = wfPhpfunc::substr($sql, 0, wfPhpfunc::strlen($sql)-2);
    $sql .= " from ".$form->get('table')." where $primary_key=?;";
    $select = array();
    foreach ($field as $key => $value) {
      $select[] = $key;
    }
    $params = array();
    $params[$primary_key] = array('type' => $primary_type, 'value' => wfRequest::get($primary_key));
    $mysql_data = array('sql' => $sql, 'select' => $select, 'params' => $params);
    /**
     * Get data.
     */
    if(wfRequest::get($primary_key)){
      wfPlugin::includeonce('wf/mysql');
      $mysql = new PluginWfMysql();
      $mysql->open($form->get('mysql'));
      $mysql->execute($mysql_data);
      $rs = new PluginWfArray($mysql->getStmtAsArray());
      if($rs->get('0')){
        foreach ($rs->get('0') as $key => $value) {
          if($form->get("items/$key")){
            $form->set("items/$key/default", $value);
          }
        }
      }
    }
    return $form;
  }
  /**
   * <p>Render a form.</p> 
   * <p>Consider to add data in separate yml file because you need to pic it up again when handle posting values. Use widget to handle post request if necessary.</p> 
   * <p>'yml:/theme/[theme]/form/my_form.yml'</p>
   */
  public static function widget_render($data){
    $data_obj = new PluginWfArray($data);
    /**
     * i18n.
     */
    wfPlugin::includeonce('i18n/translate_v1');
    $i18n = new PluginI18nTranslate_v1();
    if(!$data_obj->get('data/i18n/path')){
      $i18n->setPath('/plugin/form/form_v1/i18n');
    }else{
      $i18n->setPath($data_obj->get('data/i18n/path'));
      /**
       * Set globals i18n if not set.
       */
      if($data_obj->get('data/settings/globals/0/path_to_key') != 'settings/plugin/i18n/translate_v1/settings/path'){
        $data_obj->set('data/settings/globals/', array('path_to_key' => 'settings/plugin/i18n/translate_v1/settings/path', 'value' => $data_obj->get('data/i18n/path')));
      }
    }
    /**
     * 
     */
    if(false){
      /**
       * Do not thing this is to be used anymore due to converting yml file string to array is done before (231025). 
       */
      /**
       * Handle data param.
       */
      if(wfArray::isKey($data, 'data')){
        if(!is_array(wfArray::get($data, 'data'))){
          /**
           * If not an array it must be path to file.
           */
          $filename = wfArray::get($GLOBALS, 'sys/app_dir').wfArray::get($data, 'data');
          if(file_exists($filename)){
            $data['data'] = sfYaml::load($filename);
          }else{
            throw new Exception("Could not find file $filename.");
          }
        }
      }else{
        throw new Exception("Param data is not set.");
      }
    }
    /**
     * 
     */
    $widget_data = new PluginWfArray($data_obj->get('data'));
    /**
     * Set data from request param on get:_name_ items.
     */
    $widget_data->setByTag(wfRequest::getAll(), 'get');
    /**
     * 
     */
    $form_form_v1 = new PluginFormForm_v1(true);
    $form_form_v1->setData($widget_data->get());
    /**
     * 
     */
    /**
     * Create form and include dependencies.
     */
    wfPlugin::includeonce('wf/array');
    $scripts = array();
    /**
     * Get from db via schema.
     */
    if($form_form_v1->has('schema') && $form_form_v1->has('table') && $form_form_v1->has('mysql')){
      $form_form_v1->setFormItemsDefaultFromDb();
    }
    /**
     * Call a render method if exist to fill the form.
     */
    if($form_form_v1->hasRender()){
      $form_form_v1->runCaptureMethod('render');
    }
    /**
     * Default values.
     */
    $default = array(
        'submit_value' => 'Send',
        'submit_class' => 'btn btn-primary',
        'id' => wfPhpfunc::str_replace('.', '', uniqid(mt_rand(), true)),
        'script' => null,
        'ajax' => false,
        'url' => '/doc/_',
        'items' => array(),
        'target' => null,
        'submit_on_enter' => false,
        'submit_method' => 'null',
        'ctrl_s_save' => false,
        'focus_first_element' => true,
        'embed_alert' => false,
        'buttons_align_right' => false
        );
    /**
     * Merge defaults with widget data.
     */
    $default = array_merge($default, $form_form_v1->data);
    $default['url'] = wfSettings::replaceClass($default['url']);
    /**
     * 
     */
    if($form_form_v1->getData('layout')){
      $scripts[] = wfDocument::createHtmlElement('script', "PluginFormForm_v1.renderLayout({id: '".$default['id']."'});");
    }
    /**
     * Buttons.
     */
    $buttons = array();
    $form_submit_data = "{ajax_element: '".$data_obj->get('data/ajax_element')."', url: '".$default['url']."', id: '".$default['id']."', submit_method: '".$default['submit_method']."', buttons_align_right: '".$default['buttons_align_right']."'}";
    if($default['ajax']) {
      $onclick = "PluginFormForm_v1.submit(".$form_submit_data.", this)";
      $buttons[] = wfDocument::createHtmlElement('input', null, array('type' => 'button', 'value' => $i18n->translateFromTheme($default['submit_value']), 'class' => $default['submit_class'], 'onclick' => $onclick, 'id' => $default['id'].'_save'), array('i18n' => false));
    }  else {
      $onclick = "document.getElementById('".$default['id']."').submit();";
      $buttons[] = wfDocument::createHtmlElement('button', $default['submit_value'], array('class' => $default['submit_class'], 'onclick' => $onclick, 'id' => $default['id'].'_save'));
    }
    if($form_form_v1->getData('buttons')){
      foreach ($form_form_v1->getData('buttons') as $key => $value) {
        $buttons[] = $value;
      }
    }
    /**
     * Elements above.
     */
    $form_element = array();
    if($form_form_v1->getData('elements_above')){
      $form_element[] = wfDocument::createHtmlElement('div', $form_form_v1->getData('elements_above'), array('id' => $default['id'].'_elements_above'));
    }
    /**
     * Items.
     */
    $form_row = array();
    if(sizeof($default['items']) > 0){
      foreach ($default['items'] as $key => $value) {
        $row = $form_form_v1->getRow($key, $value, $default, $i18n);
        if($row){
          $form_row[] = $row['element'];
          foreach ($row['script'] as $key => $value) {
            $scripts[] = $value;
          }
        }
      }
    }else{
      exit('No items or schema/table/mysql is set.');
    }
    $form_element[] = wfDocument::createHtmlElement('div', $form_row, array('id' => $default['id'].'_controls'));
    /**
     * Layout.
     */
    if($form_form_v1->getData('layout')){
      $form_element[] = wfDocument::createHtmlElement('div', $form_form_v1->getData('layout'), array('id' => $default['id'].'_layout'));
      $scripts[] = wfDocument::createHtmlElement('script', "document.getElementById('".$default['id']."_controls').style.display='none';");
    }
    /**
     * Elements below.
     */
    if($form_form_v1->getData('elements_below')){
      $form_element[] = wfDocument::createHtmlElement('div', $form_form_v1->getData('elements_below'), array('id' => $default['id'].'_elements_below'));
    }
    /**
     * Embed alert.
     */
    if($default['embed_alert']){
      $form_element[] = wfDocument::createHtmlElement('div', '', array('class' => 'alert alert-warning', 'id' => $default['id'].'_alert', 'style' => 'display:none'));
    }
    /**
     * Buttons.
     */
    $class = 'wf_form_row';
    if($default['buttons_align_right']){
      $class .= ' text-right';
    }
    $form_element[] = wfDocument::createHtmlElement('div', $buttons, array('class' => $class, 'style' => 'margin-bottom:10px'));
    /**
     * Allow post via keypress, enter in input field.
     */
    if($default['submit_on_enter']){
      $scripts[] = wfDocument::createHtmlElement('script', "document.getElementById('".$default['id']."').onkeypress=function(element){PluginFormForm_v1.keypress(element, ".$form_submit_data.");}");
    }
    /**
     * Attribute.
     */
    $form_attribute = array('id' => $default['id'], 'method' => 'post', 'role' => 'form', 'onsubmit' => 'return false');
    /**
     * autocomplete
     */
    if(isset($default['autocomplete'])){
      $form_attribute['autocomplete'] = $default['autocomplete'];
    }
    /**
     * 
     */
    if($default['target']){
      $form_attribute['target'] = $default['target'];
    }
    if(!$default['ajax']){
      $form_attribute['action'] = $default['url'];
    }
    $form_render = wfDocument::createHtmlElement('form', $form_element, $form_attribute, $widget_data->get('settings'));
    /**
     * Move buttons to footer if Bootstrap modal and NOT Bootstrap 4.
     */
    $scripts[] = wfDocument::createHtmlElement('script', "if(typeof PluginWfBootstrapjs == 'object' && document.getElementById('PluginTwitterBootstrap335v')){PluginWfBootstrapjs.moveModalButtons('".$form_form_v1->getData('id')."');}");
    /**
     * Set focus on first element.
     */
    if($default['focus_first_element']){
      $scripts[] = wfDocument::createHtmlElement('script', "if(typeof PluginFormForm_v1=='undefined'){alert('Method PluginFormForm_v1.focus is undefined!');}else{PluginFormForm_v1.focus({id: '".$form_form_v1->getData('id')."'});}");
    }
    /**
     * Onkeypress
     */
    if($default['ctrl_s_save']){
      $scripts[] = wfDocument::createHtmlElement('script', "if(typeof PluginWfOnkeypress=='object'){PluginWfOnkeypress.set('".$default['id']."', {ctrlKey: true, which: 83}, function(){document.getElementById('".$default['id']."_save').click();});}");
    }
    /**
     * Elements first.
     */
    if($form_form_v1->getData('elements_before')){
      $element = array();
      $element[] = wfDocument::createHtmlElement('div', array(wfDocument::createHtmlElement('div', $form_form_v1->getData('elements_before'), array('class' => 'col-12'))), array('class' => 'row', 'id' => $default['id'].'_elements_before'));
      wfDocument::renderElement($element);
    }
    /**
     * Render.
     */
    wfDocument::renderElement(array($form_render));
    wfDocument::renderElement($scripts);
    /**
     * Elements after.
     */
    if($form_form_v1->getData('elements_after')){
      $element = array();
      $element[] = wfDocument::createHtmlElement('div', array(wfDocument::createHtmlElement('div', $form_form_v1->getData('elements_after'), array('class' => 'col-12'))), array('class' => 'row', 'id' => $default['id'].'_elements_after'));
      wfDocument::renderElement($element);
    }
  }
  /**
   * Get fields via schema.
   */
  public function getSchema(){
    $form = new PluginWfArray($this->data);
    $schema = new PluginWfYml(wfArray::get($GLOBALS, 'sys/app_dir').$form->get('schema'));
    $field = new PluginWfArray($schema->get('tables/'.$form->get('table')));
    $extra = new PluginWfArray($schema->get('extra'));
    if($extra->get('field')){
      foreach ($extra->get('field') as $key => $value) {
        $field->set("field/$key", $value);
      }
    }
    return $field->get('field');
  }
  /**
   * 
   * @param string $key
   * @param array $value
   * @param array $default
   * @return array
   */
  private function getRow($key, $value, $default, $i18n){
    $scripts = array();
    $default_value = array(
        'label' => $key,
        'default' => '',
        'element_id' => $default['id'].'_'.$key,
        'name' => $key,
        'readonly' => null,
        'type' => 'varchar',
        'checked' => null,
        'mandatory' => null,
        'option' => null,
        'wrap' => null,
        'class' => 'form-control',
        'style' => null,
        'placeholder' => null,
        'html' => false,
        'i18n' => true,
        'settings' => array(),
        'attribute' => array(),
        'rows' => 5
            );
    $value = new PluginWfArray($value);
    if($value->get('class_add')){
      $default_value['class'] = $default_value['class'].' '.$value->get('class_add');
    }
    $default_value = array_merge($default_value, $value->get());
    if($default_value['type']=='checkbox'){
      $default_value['class'] = 'form-check-input';
    }
    if($default_value['type']=='date' && !$default_value['placeholder']){
      $default_value['placeholder'] = 'YYYY-MM-DD';
    }
    $type = null;
    $innerHTML = null;
    $attribute = array('name' => $default_value['name'], 'id' => $default_value['element_id'], 'class' => $default_value['class'], 'style' => $default_value['style']);
    /**
     * Add extra attribute to control.
     */
    $attribute = array_merge($attribute, $default_value['attribute']);
    /**
     * 
     */
    switch ($default_value['type']) {
      case 'checkbox':
        $type = 'input';
        $attribute['type'] = 'checkbox';
        if($default_value['checked'] || $default_value['default']=='1'){
          $attribute['checked'] = 'checked';
        }
        break;
      case 'text':
        $type = 'textarea';
        $attribute['wrap'] = $default_value['wrap'];
        $attribute['rows'] = $default_value['rows'];
        $innerHTML = $default_value['default'];
        /**
         * HTML editor via Nic Editor.
         */
        if($default_value['html']){
          wfPlugin::includeonce('wysiwyg/nicedit');
          $nicedit = new PluginWysiwygNicedit();
          $scripts[] = $nicedit->getTextareaScript($default_value['element_id']);
        }
        break;
      case 'password':
        $type = 'input';
        $attribute['type'] = 'password';
        $attribute['value'] = $default_value['default'];
        break;
      case 'map':
        $type = 'input';
        $attribute['type'] = 'text';
        $attribute['value'] = htmlentities((string) $default_value['default']);
        $attribute['style'] = 'display:none';
        $attribute['onchange'] = "if(this.value.length){document.getElementById('span_map_icon_".$default_value['element_id']."').style.display='';}else{document.getElementById('span_map_icon_".$default_value['element_id']."').style.display='none';}";
        break;
        break;
      case 'color':
        $type = 'input';
        $attribute['type'] = 'color';
        $attribute['value'] = $default_value['default'];
        break;
      case 'varchar':
      case 'date':
        if($default_value['type']=='date'){
          $scripts[] = wfDocument::createHtmlElement('script', "PluginEternicodeBootstrapdatepicker2.render('".$default['id']."_$key')");
        }
        if(!$default_value['option']){
          $type = 'input';
          $attribute['type'] = 'text';
          $attribute['value'] = htmlentities((string) $default_value['default']);
          $attribute['placeholder'] = $default_value['placeholder'];
          if(false){
            /**
             * 191004.
             * This does not work well. Consider not to use type=number for inputs.
             * When typing "-" for a negative number the character disappear.
             * Not sure if it was external event scripts causing this.
             */
            if($attribute['placeholder']==='0'){
              $attribute['type'] = 'number';
            }elseif($attribute['placeholder']==='0.0'){
              $attribute['type'] = 'number';
              $attribute['step'] = '0.1';
            }elseif($attribute['placeholder']==='0.00'){
              $attribute['type'] = 'number';
              $attribute['step'] = '0.01';
            }elseif($attribute['placeholder']==='0.000'){
              $attribute['type'] = 'number';
              $attribute['step'] = '0.001';
            }
          }
        }else{
          /**
           * Set data from yml file if 'yml:_pat_to_yml_file_'.
           */
          if(!is_array($default_value['option'])){
            $default_value['option'] = wfSettings::getSettingsFromYmlString($default_value['option']);
          }
          if(!is_array($default_value['option'])){
            $default_value['option'] = wfSettings::getSettingsFromMethod($default_value['option']);
          }
          /**
           * 
           */
          $type = 'select';
          $settings = new PluginWfArray();
          if($default_value['i18n']===false){
            $settings->set('event/document_render_string/disabled', true);
          }
          if(is_array($default_value['option'])){
            /**
             * i18n
             */
            if($default_value['i18n']){
              foreach($default_value['option'] as $k => $v){
                $default_value['option'][$k] = $i18n->translateFromTheme($v);
              }
            }
            /**
             * 
             */
            $default_value['default'] = wfSettings::getGlobalsFromString($default_value['default']);
            /**
             * 
             */
            if(true){
              $option = array();
              $option_match = false;
              foreach ($default_value['option'] as $key2 => $value2) {
                $temp = array();
                $temp['value'] = $key2;
                /**
                 * 
                 */
                if((string)$default_value['default']===(string)$key2){
                  $temp['selected'] = 'true';
                  $option_match = true;
                }
                /**
                 * 
                 */
                $option[] = wfDocument::createHtmlElement('option', $value2, $temp, $settings->get());
              }
              /**
               * 
               */
              if($default_value['option'] && $default_value['default'] && !$option_match){
                $temp = array();
                $temp['value'] = $default_value['default'];
                $temp['selected'] = 'true';
                $option[] = wfDocument::createHtmlElement('option', '(No match on '.$default_value['default'].')', $temp, $settings->get());
              }
              /**
               * 
               */
              $innerHTML = $option;
            }else{
              /**
               * 2022-07-13
               * Omit this code due to unpredicted behavor. 
               * Should be removed in the future.
               * Also js method add_option should be removed.
               */
              $data_option = $default['id']."_".$key."_option";
              $scripts[] = wfDocument::createHtmlElement('script', "var $data_option = ".json_encode($default_value['option']));
              $scripts[] = wfDocument::createHtmlElement('script', "PluginFormForm_v1.add_option($data_option, '".$default['id']."_$key', '".(string)$default_value['default']."');");
            }
          }
        }
        break;
      case 'hidden':
        $type = 'input';
        $attribute['type'] = 'hidden';
        $attribute['value'] = $default_value['default'];
        break;
      case 'div':
        $type = 'div';
        break;
      default:
        break;
    }
    if($type){
      if($type=='div'){
        return $value->get();
      }else{
        $element = new PluginWfArray();
        $input = wfDocument::createHtmlElement($type, $innerHTML, $attribute, array('i18n' => false));
        /**
         * Label.
         */
        if(wfArray::get($attribute, 'type') != 'hidden'){
          $label = PluginFormForm_v1::getLabel($default_value);
          if($default_value['type']=='checkbox'){
            $label->set('innerHTML', array($input, wfDocument::createHtmlElement('span', $label->get('innerHTML'), array('class' => 'form-check-label'))));
          }
          $element->set('label', $label->get());
          /**
           * Mandatory label.
           */
          if($default_value['mandatory']){
            $element->set('mandatory', wfDocument::createHtmlElement('label', '*', array('id' => 'label_mandatory_'.$default_value['element_id'])));
          }
        }
        /**
         * Map.
         */
        if($default_value['type'] == 'map'){
          $display = 'none';
          if(wfPhpfunc::strlen($default_value['default'])){
            $display = '';
          }
          $element->set('map_icon', wfDocument::createHtmlElement('a', array(wfDocument::createHtmlElement('span', null, array('id' => 'span_map_icon_'.$default_value['element_id'], 'class' => 'glyphicon glyphicon-map-marker', 'style' => "display:$display"))), array('onclick' => "PluginFormForm_v1.showMap('".$default_value['element_id']."');", 'class' => 'form-control', 'style' => "text-align:right")));
        }
        /**
         * Info icon.
         */
        if($value->get('info/text')){
          $user = wfUser::getSession();
          if($user->get('plugin/twitter/bootstrap413v/include')){
            $data_placement = 'bottom';
            if($value->get('info/position')){
              $data_placement = $value->get('info/position');
            }
            $element->set('glyphicon_info', wfDocument::createHtmlElement('span', array(wfDocument::createWidget('icons/octicons', 'svg', array('name' => 'info'))), array(
              'id' => 'info_'.$default_value['element_id'],
              'style' => 'opacity:0.4',
              'tabindex' => '-1',
              'data-toggle' => 'popover',
              'data-trigger' => 'focus',
              'data-html' => "true",
              'data-content' => $value->get('info/text'),
              'data-original-title' => $default_value['label'],
              'data-placement' => $data_placement
              ),
              $value->get('info/settings')
            ));
            $scripts[] = wfDocument::createHtmlElement('script', " $(function () {  $('#info_".$default_value['element_id']."').popover(); }) ");
            
          }else{
            $data_placement = 'bottom';
            if($value->get('info/position')){
              $data_placement = $value->get('info/position');
            }
            wfPlugin::enable('icons/bootstrap_v1_8_1');
            $icon = wfDocument::createWidget('icons/bootstrap_v1_8_1', 'icon', array('icon' => 'info-circle'));
            $element->set('info_text', wfDocument::createHtmlElement('span', array($icon), array(
              'id' => 'info_'.$default_value['element_id'],
              'title' => $default_value['label'], 
              'class' => '', 
              'style' => 'cursor:pointer',
              'data-bs-toggle' => 'popover',
              'data-bs-trigger' => 'manual',
              'data-bs-placement' => $data_placement,
              'data-bs-content' => $value->get('info/text'),
              'onclick' => "$('#".'info_'.$default_value['element_id']."').popover('toggle');"
              ),
              $value->get('info/settings')
            ));
          }
        }
        /**
         * Input placeholder
         */
        if($default_value['type']=='varchar' && wfPhpfunc::strstr($default_value['placeholder'], '0.')){
          /**
           * Add script.
           */
          $scripts[] = wfDocument::createHtmlElement('script', "PluginFormForm_v1.input_placeholder(". json_encode(array('id' => $default_value['element_id'], 'placeholder' => $default_value['placeholder'])).");");
        }
        /**
         * Change class form-control to form-select if element is select (Bootstap 5).
         */
        $user = wfUser::getSession();
        if($user->get('plugin/twitter/bootstrap530v/include')){
          if($input['type'] == 'select'){
            $input['attribute']['class'] = wfPhpfunc::str_replace('form-control', 'form-select', $input['attribute']['class']);
          }
        }
        /**
         * 
         */
        if($default_value['type']!='checkbox'){
          $element->set('input', $input);
        }
        /**
         * Class.
         */
        $class_div = 'mb-3';
        if($default_value['type']=='checkbox'){
          $class_div = 'checkbox';
        }
        /**
         * placeholder, i18n
         */
        if($element->get('input/attribute/placeholder')){
          $element->set('input/attribute/placeholder', wfPhpfunc::str_replace('digits', $this->i18n->translateFromTheme('digits'), $element->get('input/attribute/placeholder')));
        }
        return array('element' => wfDocument::createHtmlElement('div', $element->get(), array(
                'id' => 'div_'.$default['id'].'_'.$key, 
                'class' => $class_div.' '.$value->get('container_class'), 
                'style' => $value->get('container_style')
                ), $default_value['settings']), 'script' => $scripts);
      }
    }else{
      return null;
    }
  }
  private static function getLabel($default_value){
    return wfDocument::createHtmlElementAsObject('label', $default_value['label'], array('class' => 'form-label', 'for' => $default_value['element_id'], 'id' => 'label_'.$default_value['element_id']));
  }
  /**
   * Capture post from form via ajax.
   * @param array $data
   * @return string
   */
  public static function widget_capture($data){
    wfPlugin::includeonce('wf/array');
    $data = new PluginWfArray($data);
    $json = new PluginWfArray();
    /**
     * 
     */
    $form_form_v1 = new PluginFormForm_v1(true);
    $form_form_v1->setData($data->get('data'));
    $form_form_v1->bind();
    if($form_form_v1->hasValidationBefore()){
      $form_form_v1->runCaptureMethod('validation_before');
    }
    $form_form_v1->validate();
    $json->set('success', false);
    $json->set('uid', wfCrypt::getUid());
    $json->set('data', $form_form_v1->data);
    if($form_form_v1->isValid()){
      if($form_form_v1->hasCapture()){
        $form_form_v1->runCaptureMethod('capture');
        $json->set('script', $form_form_v1->data);
      }else{
        $json->set('script', array("alert(\"Param capture is missing in form data!\");"));
      }
    }else{
      $errors = array();
      $er = $form_form_v1->getErrorsAsArray();
      $content = '';
      foreach($er['item'] as $v){
        foreach($v as $v2){
          $content .= "$v2<br>";
        }
      }
      $errors[] = "PluginWfBootstrapjs.modal({id: 'my_modal', label: '".$er['errors'][0]."', content: '$content', icon: 'alert', size: 'sm', backdrop: false, body_bg: 'warning'});";
      /**
       * Set errors_script.
       */
      foreach ($form_form_v1->getErrorsScript() as $key => $value) {
        $errors[] = $value;
      }
      $json->set('script', $errors);
    }
    exit(json_encode($json->get()));
  }
  public function isValid(){
    $form = new PluginWfArray($this->data);
    return $form->get('is_valid');
  }
  public function hasValidationBefore(){
    $form = new PluginWfArray($this->data);
    if($form->get('validation_before/plugin') && $form->get('validation_before/method')){
      return true;
    }else{
      return false;
    }
  }
  public function hasCapture(){
    $form = new PluginWfArray($this->data);
    if($form->get('capture/plugin') && $form->get('capture/method')){
      return true;
    }else{
      return false;
    }
  }
  public function hasRender(){
    $form = new PluginWfArray($this->data);
    if($form->get('render/plugin') && $form->get('render/method')){
      return true;
    }else{
      return false;
    }
  }
  public function has($key){
    $form = new PluginWfArray($this->data);
    if($form->get($key)){
      return true;
    }else{
      return false;
    }
  }
  /**
   * Bind request params to form.
   * @param boolean $preserve_default
   * @return null
   */
  public function bind($preserve_default = false){
    $form = $this->data;
    /**
     * Set validators if empty and placeholder value.
     */
    foreach ($form['items'] as $k => $v) {
      $i = new PluginWfArray($v);
      if($i->get('type')=='varchar'){
        if($i->get('placeholder')==='0'){
          /**
           * valdate/integer, validate_integer
           */
          $form['items'][$k]['validator'][] = array('plugin' => 'validate/integer', 'method' => 'validate_integer', 'data' => $i->get('validator_data'));
        }elseif(wfPhpfunc::substr($i->get('placeholder'), 0, 2)==='0.'){
          /**
           * validate_double, validate_double
           */
          $i->set('validator_data/decimals', wfPhpfunc::strlen($i->get('placeholder'))-2);
          $form['items'][$k]['validator'][] = array('plugin' => 'validate/double', 'method' => 'validate_double', 'data' => $i->get('validator_data'));
        }elseif(wfPhpfunc::substr($i->get('placeholder'), 0, 6)==='Text ('){
          /**
           * validate/string, validate_length_minmax
           * Example: Text (4-8)
           */
          $str = wfPhpfunc::substr($i->get('placeholder'), 6);
          $str = wfPhpfunc::substr($str, 0, wfPhpfunc::strlen($str)-1);
          /**
           * 
           */
          wfPlugin::includeonce('string/array');
          $plugin = new PluginStringArray();
          $str = new PluginWfArray($plugin->from_char($str, '-'));
          $form['items'][$k]['validator'][] = array('plugin' => 'validate/string', 'method' => 'validate_length_minmax', 'data' => array('min' => $str->get('0'), 'max' => $str->get('1')));
        }elseif( wfPhpfunc::strstr($i->get('placeholder'), ' digits)')){
          $length = $i->get('placeholder');
          $length = wfPhpfunc::substr($length, 1, strpos($length, ' ')-1);
          $length = (int)$length;
          $form['items'][$k]['validator'][] = array('plugin' => 'validate/digits', 'method' => 'validate_digits', 'data' => array('length' => $length));
        }elseif($i->get('placeholder')==='@'){
          /**
           * form/form_v1, validate_email
           */
          $form['items'][$k]['validator'][] = array('plugin' => 'form/form_v1', 'method' => 'validate_email');
        }
      }elseif($i->get('type')=='date'){
        if(!isset($form['items'][$k]['validator'])){
          $form['items'][$k]['validator'][] = array('plugin' => 'form/form_v1', 'method' => 'validate_date');
        }else{
          $form['items'][$k]['validator'] = array_merge(array(array('plugin' => 'form/form_v1', 'method' => 'validate_date')), $form['items'][$k]['validator']);
        }
      }
    }
    /**
     * 
     */
    foreach ($form['items'] as $key => $value) {
      $str = wfRequest::get($key);
      if($form['items'][$key]['type']=='checkbox'){
        if($str=='on'){$str=true;}
      }
      $form['items'][$key]['post_value'] = $str;
      if(!$preserve_default){
        $form['items'][$key]['default'] = $str;
      }
      /**
       * Set '' to null if type is date to get it to work with wf/mysql.
       */
      if($form['items'][$key]['type']=='date' && $form['items'][$key]['post_value']==''){
        $form['items'][$key]['post_value'] = null;
      }
    }
    $this->data = $form;
    return null;
  }
  /**
   * Bind array where keys matching keys in form.
   */
  public function setDefaultsFromArray($array){
    foreach ($this->data['items'] as $key => $value) {
      if(isset($array[$key])){
        $this->data['items'][$key]['default'] = $array[$key];
      }
    }
    return null;
  }
  /**
   * Bind array where keys matching keys in form.
   */
  public function setPostvalue($array){
    foreach ($this->data['items'] as $key => $value) {
      if(isset($array[$key])){
        $this->data['items'][$key]['post_value'] = $array[$key];
      }else{
        $this->data['items'][$key]['post_value'] = null;
      }
    }
    return null;
  }
  /**
   * Set option from array.
      -
        value: 11
        option: 'Blekinge län'
      -
        value: 19
        option: Dalarna
   */
  public static function setOptionFromArray($form, $item, $array, $add_empty=true){
    $option = PluginFormForm_v1::getOption($array, $add_empty);
    $form->set("items/$item/option", $option);
    return $form;
  }
  /**
   * Format options to be used in forms.
   * @param Array $array Keys must be value and option.
   * @param Boolena $add_empty If begin with an empty option.
   * @return Array
   */
  public static function getOption($array, $add_empty=true){
    $option = array();
    if($add_empty){
      $option[''] = '';
    }
    foreach ($array as $key => $value) {
      $option[$value['value']] = $value['option'];
    }
    return $option;
  }
  /**
   * Validate form.
   * @return boolean
   */
  public function validate(){
    $form = $this->data;
    /**
     * i18n.
     */
    wfPlugin::includeonce('i18n/translate_v1');
    $i18n = new PluginI18nTranslate_v1();
    /**
     * Set i18n path.
     */
    if(isset($form['i18n']['path'])){
      $i18n->path = $form['i18n']['path'];
    }
    /**
     * Translate labels.
     */
    foreach ($form['items'] as $key => $value) {
      if(isset($form['items'][$key]['label'])){
        $form['items'][$key]['label'] = $i18n->translateFromTheme($form['items'][$key]['label']);
      }
    }
    /**
     * Then set i18n path to translate the rest from this plugin.
     */
    $i18n->setPath('/plugin/form/form_v1/i18n');
    //Validate mandatory.
    foreach ($form['items'] as $key => $value) {
      /**
       * If already validated skip this field.
       */
      if(isset($form['items'][$key]['is_valid']) && $form['items'][$key]['is_valid']==false){
        continue;
      }
        if(isset($value['mandatory']) && $value['mandatory']){
            if(isset($value['post_value']) && wfPhpfunc::strlen($value['post_value'])){
                $form['items'][$key]['is_valid'] = true;
            }else{
                $form['items'][$key]['is_valid'] = false;
                $form['items'][$key]['errors'][] = $i18n->translateFromTheme('?label is empty.', array('?label' => $form['items'][$key]['label']));
            }
        }else{
            $form['items'][$key]['is_valid'] = true;
        }
    }
    //Validate email (should be removed, 191018).
    if(false){
      foreach ($form['items'] as $key => $value) {
          if($value['is_valid']){
              if(isset($value['validate_as_email']) && $value['validate_as_email']){
                  if (!filter_var($value['post_value'], FILTER_VALIDATE_EMAIL)) {
                      // invalid emailaddress
                      $form['items'][$key]['errors'][] = __('?label is not an email!', array('?label' => $form['items'][$key]['label']));
                      $form['items'][$key]['is_valid'] = false;
                  }                
              }
          }
      }
    }
    //Validate php code injection.
    foreach ($form['items'] as $key => $value) {
      if($value['is_valid']){
        if (isset($value['post_value']) && (wfPhpfunc::strstr($value['post_value'], '<?php') || wfPhpfunc::strstr($value['post_value'], '?>'))) {
            $form['items'][$key]['errors'][] = $i18n->translateFromTheme('?label has illegal character.', array('?label' => $form['items'][$key]['label']));
            $form['items'][$key]['is_valid'] = false;
        }                
      }
    }
    // Validator
    foreach ($form['items'] as $key => $value) {
      if(wfArray::get($value, 'validator')){
        foreach (wfArray::get($value, 'validator') as $key2 => $value2) {
          wfPlugin::includeonce($value2['plugin']);
          $obj = wfSettings::getPluginObj($value2['plugin']);
          $method = $value2['method'];
          if(wfArray::get($value2, 'data')){
            $form = $obj->$method($key, $form, wfArray::get($value2, 'data'));
          }else{
            $form = $obj->$method($key, $form);
          }
        }
      }
    }
    //Set form is_valid.
    $form['is_valid'] = true;
    foreach ($form['items'] as $key => $value) {
        if(!$value['is_valid']){
            $form['is_valid'] = false;
            $form['errors'][] = $i18n->translateFromTheme('The form does not pass validation.');
            break;
        }
    }
    $this->data = $form;
    return $this->isValid();
  }
  /**
   * Bind and validate form.
   * @return boolean
   */      
  public function bindAndValidate(){
    $this->bind();
    $this->validate();
    return $this->isValid();
  }
  /**
   * Set error for a field.
   * @param string $field
   * @param string $message
   * @return null
   */
  public function setErrorField($field, $message){
    $form = $this->data;
    $form['is_valid'] = false;
    $form['items'][$field]['is_valid'] = false;
    $form['items'][$field]['errors'][] = $message;
    $this->data = $form;
    return null;
  }
  /**
   * Get errors in plain text.
   * @param string $nl Default '<br>'
   * @return string
   */
  public function getErrors($nl = '<br>'){
    $form = new PluginWfArray($this->data);
    $errors = null;
    if($form->get('errors')){
      foreach ($form->get('errors') as $key => $value){
        $errors .= $value.$nl;
      }
    }
    foreach ($form->get('items') as $key => $value) {
      if(!$value['is_valid']){
        foreach ($value['errors'] as $key2 => $value2){
          $errors .= '- '.$value2.$nl;
        }
      }
    }
    return $errors;
  }
  public function getErrorsScript(){
    $form = new PluginWfArray($this->data);
    $errors_script = array();
    foreach ($form->get('items') as $key => $value) {
      $i = new PluginWfArray($value);
      if(!$i->get('is_valid') && $i->get('errors_script')){
        foreach ($i->get('errors_script') as $key2 => $value2){
          $errors_script[] = $value2;
        }
      }
    }
    return $errors_script;
  }
  /**
   * Get all form errors as array.
   * @param PluginWfArray $form
   * @return Array
   */
  public function getErrorsAsArray(){
    $form = new PluginWfArray($this->data);
    $errors = array();
    if($form->get('errors')){
      foreach ($form->get('errors') as $key => $value){
        $errors['errors'][] = $value;
      }
    }
    foreach ($form->get('items') as $key => $value) {
      $item = new PluginWfArray($value);
      if(!$item->get('is_valid')){
        foreach ($item->get('errors') as $key2 => $value2){
          $errors['item'][$key][] =$value2;
        }
      }
    }
    return $errors;
  }
  /**
   * Validate email.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return array
   */
  public function validate_email($field, $form, $data = array()){
    if(wfArray::get($form, "items/$field/is_valid") && wfArray::get($form, "items/$field/post_value")){
      if (!filter_var(wfArray::get($form, "items/$field/post_value"), FILTER_VALIDATE_EMAIL)) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not an email!', array('?label' => $this->i18n->translateFromTheme(wfArray::get($form, "items/$field/label")))));
      }
    }
    return $form;
  }
  /**
   * Validate password.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return type
   */
  public function validate_password($field, $form, $data = array()){
    if(wfArray::get($form, "items/$field/is_valid")){
      $validate = $this->validatePasswordAbcdef09(wfArray::get($form, "items/$field/post_value"));
      if (!wfArray::get($validate, 'success')) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label must have at lest one uppercase, lowercase, number and a minimum length of 8!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }
    }
    return $form;
  }
  /**
   * Validate password.
   * @param string $password
   * @param array $settings
   * @return array
   */
  private function validatePasswordAbcdef09($password, $settings = array()) {
    // '$\S*(?=\S{8,})(?=\S*[a-z])(?=\S*[A-Z])(?=\S*[\d])(?=\S*[\W])\S*$';
    $data = array(
      'password' => $password,
      'settings' => $settings,
      'success' => false,
      'item' => array(
        'length' => array(
          'default' => '8',
          'match' => '(?=\S{[length],})',
          'result' => 2,
          'default_with_settings' => null 
        ),
        'lower_case' => array(
          'default' => true,
          'match' => '(?=\S*[a-z])',
          'result' => 2,
          'default_with_settings' => null 
        ),
        'upper_case' => array(
          'default' => true,
          'match' => '(?=\S*[A-Z])',
          'result' => 2,
          'default_with_settings' => null 
        ),
        'digit' => array(
          'default' => true,
          'match' => '(?=\S*[\d])',
          'result' => 2,
          'default_with_settings' => null 
        ),
        'special_character' => array(
          'default' => false,
          'match' => '(?=\S*[\W])',
          'result' => 2,
          'default_with_settings' => null 
        ),
      ),
      'match' => null
    );
    foreach ($data['item'] as $key => $value) {
      if(isset($data['settings'][$key])){
        $data['item'][$key]['default_with_settings'] = $data['settings'][$key];
      }else{
        $data['item'][$key]['default_with_settings'] = $data['item'][$key]['default'];
      }
    }
    if($data['item']['length']['default_with_settings']){
      // Replace length tag.
      $data['item']['length']['match'] = wfPhpfunc::str_replace('[length]', $data['item']['length']['default_with_settings'], $data['item']['length']['match']);
    }
    $data['match'] = '$\S*';
    foreach ($data['item'] as $key => $value) {
      if($data['item'][$key]['default_with_settings']){
        $data['match'] .= $data['item'][$key]['match'];
        $data['item'][$key]['result'] = preg_match('$\S*'.$data['item'][$key]['match'].'\S*$', $data['password']);
      }
    }
    $data['match'] .= '\S*$';
    if (preg_match($data['match'], $data['password'])){
      $data['success'] = true;
    }
    return $data;
  }
  /**
   * Validate equal.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return array
   */
  public function validate_equal($field, $form, $data = array('value' => 'some value')){
    if(wfArray::get($form, "items/$field/is_valid")){
      if (wfArray::get($form, "items/$field/post_value") != wfArray::get($data, 'value')) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not equal to expected value!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }
    }
    return $form;
  }
  /**
   * Validate date.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return array
   */
  public function validate_date($field, $form, $data = array()){
    if(wfArray::get($form, "items/$field/is_valid") && wfArray::get($form, "items/$field/post_value")){
      if (!PluginFormForm_v1::isDate(wfArray::get($form, "items/$field/post_value"))){
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not a date!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }
    }
    return $form;
  }
  /**
   * Check if value is a date.
   * @param string $value
   * @return boolean
   */
  public static function isDate($value){
    if(strtotime($value)){
      $format_datetime = 'Y-m-d H:i:s';
      $format_date = wfDate::format();
      $d = DateTime::createFromFormat($format_datetime, $value);
      if($d && $d->format($format_datetime) == $value){
        return true;
      }else{
        $d = DateTime::createFromFormat($format_date, $value);
        if($d && $d->format($format_date) == $value){
          return true;
        }else{
          return false;
        }
      }
    }else{
      return false;
    }
  }
  /**
   * Validate numeric.
   * @param string $field
   * @param array $form
   * @param PluginWfArray $data
   * @return array
   */
  public function validate_numeric($field, $form, $data = array()){
    wfPlugin::includeonce('wf/array');
    $default = array('min' => 0, 'max' => 999999);
    $data = new PluginWfArray(array_merge($default, $data));
    if(wfArray::get($form, "items/$field/is_valid") && wfPhpfunc::strlen(wfArray::get($form, "items/$field/post_value"))){
      if (!is_numeric(wfArray::get($form, "items/$field/post_value"))) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not numeric!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }else{
        if(
                (double)wfArray::get($form, "items/$field/post_value") < (double)$data->get('min') || 
                (double)wfArray::get($form, "items/$field/post_value") > (double)$data->get('max')
                ){
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label must be between ?min and ?max!', array(
          '?label' => wfArray::get($form, "items/$field/label"),
          '?min' => $data->get('min'),
          '?max' => $data->get('max')
          )));
        }
      }
    }
    return $form;
  }
  /**
   * Validate integer.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return array
   */
  public function validate_integer($field, $form, $data = array()){
    /**
     * Using plugin validate/integer instead.
     */
    if(wfArray::get($form, "items/$field/is_valid") && wfPhpfunc::strlen(wfArray::get($form, "items/$field/post_value"))){ // Only if valid and has data.
      if (!$this->is_integer(wfArray::get($form, "items/$field/post_value"))) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not an integer!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }
    }
    return $form;    
  }
  private function is_integer($num){
    if(preg_match('/^\d+$/',$num)){
      return true;
    }else{
      return false;
    }
  }
  /**
   * Check double and decimals.
   * Param data/decimals must be set.
   * @param string $field
   * @param array $form
   * @param array $data
   * @return array
   */
  public function validate_double($field, $form, $data = array()){
    /**
     * Using plugin validate/double instead.
     */
    if(!isset($data['decimals'])){
      throw new Exception('PluginFormForm_v1.validate_double SAYS param data/decimals is not included.');
    }
    if(wfArray::get($form, "items/$field/is_valid") && wfPhpfunc::strlen(wfArray::get($form, "items/$field/post_value"))){ // Only if valid and has data.
      if (!$this->is_double(wfArray::get($form, "items/$field/post_value"), $data)) {
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label is not a double!', array('?label' => wfArray::get($form, "items/$field/label"))));
      }elseif(!$this->check_decimals(wfArray::get($form, "items/$field/post_value"), $data)){
        $form = wfArray::set($form, "items/$field/is_valid", false);
        $form = wfArray::set($form, "items/$field/errors/", $this->i18n->translateFromTheme('?label has more than ?decimals decimals!', array('?label' => wfArray::get($form, "items/$field/label"), '?decimals' => $data['decimals'])));
      }
    }
    return $form;    
  }
  private function check_decimals($num, $data = array()){
    $num = wfPhpfunc::str_replace(',', '.', $num);
    if(wfPhpfunc::strstr($num, '.')){
      /**
       * We deal with a decimal value.
       * Counting decimals.
       */
      $x = preg_split('/_dot_/', wfPhpfunc::str_replace('.', '_dot_', $num));
      if(wfPhpfunc::strlen($x[1]) > $data['decimals']){
        return false;
      }else{
        return true;
      }
    }else{
      return true;
    }
  }
  private function is_double($num, $data = array()){
    $num = wfPhpfunc::str_replace(',', '.', $num);
    if($num == '0'){
      return true;
    }
    if(intval($num)){
      /**
       * We consider an integer is also a valid double.
       * 23 / 23,12 / 23.12 / 23xxx comes here.
       */
      if(is_numeric($num)){
        return true;
      }else{
        /**
         * 23xxx comes here.
         */
        return false;
      }
    }else{
      if(is_numeric($num)){
        return true;
      }else{
        /**
         * 23xxx comes here.
         */
        return false;
      }
    }
  }
  /**
   * Save form to yml file.
   * @param PluginWfArray $form
   * @return boolean
   */
  public static function saveToYml($form){
    wfPlugin::includeonce('wf/array');
    wfPlugin::includeonce('wf/yml');
    $form = new PluginWfArray($form);
    if($form->get('yml/file') && $form->get('yml/path_to_key') && $form->get('items')){
      $yml = new PluginWfYml($form->get('yml/file'), $form->get('yml/path_to_key'));
      foreach ($form->get('items') as $key => $value) {
        $yml->set($key, wfArray::get($value, 'post_value'));
      }
      $yml->save();
      return true;
    }else{
      return false;
    }
    return false;
  }
  /**
   * Capture method.
   * @param string $type
   * @return null
   */
  public function runCaptureMethod($type = 'validation_before'){
    $data = new PluginWfArray($this->data);
    $method = $data->get($type.'/method');
    wfPlugin::includeonce($data->get($type.'/plugin'));
    $obj = wfSettings::getPluginObj($data->get($type.'/plugin'));
    $this->data = $obj->$method($this->data);
    return null;
  }
  public function test_validation_before($form){
    /**
     * Do stuff.
     */
    return $form;
  }
  /**
   * Method to test capture.
   * @return type
   */
  public function test_capture(){
    return array("alert('PluginFormForm_v1 method test_capture was tested! Replace to another to proceed your work.')");
  }
  /**
   * 
   */
  public function schema_capture($form){
    /**
     * Create save sql.
     */
    if($form->get('schema') && $form->get('table') && $form->get('mysql')){
      $field = new PluginWfArray(PluginFormForm_v1::getSchema($form));
      /**
       * Primary key (only one).
       */
      $primary = PluginFormForm_v1::getSchemaFieldPrimary($field->get());
      $primary_key = $primary->get('primary_key');
      $primary_type = $primary->get('primary_type');
      $sql = "update ".$form->get('table')." set ";
      foreach ($form->get('items') as $key => $value) {
        $sql .= "$key=?, ";
      }
      $sql = wfPhpfunc::substr($sql, 0, wfPhpfunc::strlen($sql)-2);
      $sql .= " where $primary_key=?;";
      $params = array();
      foreach ($form->get('items') as $key => $value) {
        $item = new PluginWfArray($value);
        
        $type = null;
        if(wfPhpfunc::strstr($field->get("$key/type"), 'varchar(')){
          $type = 's';
        }elseif(wfPhpfunc::strstr($field->get("$key/type"), 'int(')){
          $type = 'i';
        }
        $params[$key] = array('type' => $type, 'value' => $item->get('post_value'));
      }
      $params['primary_key'] = array('type' => $primary_type, 'value' => wfRequest::get($primary_key));
      $mysql_data = array('sql' => $sql, 'params' => $params);
      wfHelp::yml_dump($mysql_data);
      /**
       * Save to db.
       */
      wfPlugin::includeonce('wf/mysql');
      $mysql = new PluginWfMysql();
      $mysql->open($form->get('mysql'));
      $mysql->execute($mysql_data);
    }
  }
  /**
   * Include javascript file.
   */
  public static function widget_include(){
    $element = array();
    wfPlugin::enable('include/js');
    $element[] = wfDocument::createWidget('include/js', 'include', array('src' => '/plugin/form/form_v1/PluginFormForm_v1.js'));
    /**
     * Unset Bootstrap width attribute form select elements, Bootstrap 4.
     */
    $element[] = wfDocument::createHtmlElement('style', "select.form-control{width:unset !important}");
    /**
     * Unset Bootstrap width attribute form select elements, Bootstrap 5.
     */
    $element[] = wfDocument::createHtmlElement('style', "select.form-select{width:unset !important}");
     wfDocument::renderElement($element);
  }
  public function send($form){
    $form = new PluginWfArray($form);
    if(!is_array($form->get('capture/data'))){
      $form->set('capture/data', wfSettings::getSettingsFromYmlString($form->get('capture/data')));
    }
    /**
     * Body.
     */
    $body = null;
    foreach ($form->get('items') as $key => $value) {
      $item = new PluginWfArray($value);
      $label = $item->get('label');
      $post_value = $item->get('post_value');
      /**
       * checkbox
       */
      if($item->get('type')=='checkbox' && !$post_value){
        $post_value = '(not checked)';
      }elseif($item->get('type')=='checkbox' && $post_value=='1'){
        $post_value = '(checked)';
      }
      /**
       * 
       */
      $body .= "<p><strong>$label</strong></p>";
      $body .= "<p>$post_value</p>";
    }
    $body = "<html><body>".$body."</body></html>";
    /**
     * Send
     */
    if(!$form->get('capture/data/mailqueue')){
      /**
       * phpmailer.
       */
      $phpmailer = wfSettings::getSettingsFromYmlString($form->get('capture/data/phpmailer'));
      if(!$phpmailer){
        throw new Exception('PluginFormForm_v1 says: No data.');
      }
      $phpmailer = new PluginWfArray($phpmailer);
      /**
       * Reply to.
       */
      if(wfRequest::get('email')){
        $phpmailer->set('ReplyTo', wfRequest::get('email'));
      }
      $phpmailer->set('Body', $body);
      /**
       * Send.
       */
      wfPlugin::includeonce('wf/phpmailer');
      $wf_phpmailer = new PluginWfPhpmailer();
      foreach ($form->get('capture/data/email') as $key => $value) {
        $phpmailer->set('To', $value);
        $wf_phpmailer->send($phpmailer->get());
      }
    }else{
      /**
       * mailqueue
       */
      wfPlugin::includeonce('mail/queue');
      $mail = new PluginMailQueue(true);
      foreach ($form->get('capture/data/email') as $key => $email) {
        $mail->create($form->get('capture/data/subject'), $body, $email, null, null, null, null, wfUser::getSession()->get('user_id'), $form->get('capture/data/mailqueue_tag'));      
      }
    }
    /**
     * Return script.
     */
    return $form->get('capture/data/script');
  }
}
