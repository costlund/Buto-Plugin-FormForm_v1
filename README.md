# Buto-Plugin-FormForm_v1

<p>Form plugin.</p>

<a name="key_0"></a>

## Usage



<a name="key_0_0"></a>

### Data

<p>Create yml file and call it from widgets.</p>

<a name="key_0_0_0"></a>

#### url (and more)

<pre><code>url: _url_where_to_post_
ajax: true
submit_value: Save
id: _my_form_</code></pre>

<a name="key_0_0_1"></a>

#### autocomplete

<p>Optional form attribute.</p>
<pre><code>autocomplete: off</code></pre>

<a name="key_0_0_2"></a>

#### items

<p>Items.</p>
<pre><code>items:
  id:
    type: varchar
    label: ID
  date:
    type: date
    label: Date
  text:
    type: text
    label: Text
    rows: 20 (optional, default 5)</code></pre>

<a name="key_0_0_2_0"></a>

##### mandatory

<p>Mandatory.</p>
<pre><code>    mandatory: true</code></pre>

<a name="key_0_0_2_1"></a>

##### default

<p>Default from request param id.</p>
<pre><code>    default: get:id</code></pre>

<a name="key_0_0_2_2"></a>

##### i18n

<p>I18n can be turned off.</p>
<pre><code>    i18n: false</code></pre>

<a name="key_0_0_2_3"></a>

##### attribute

<p>Attributes.</p>
<pre><code>    attribute:
      onkeyup: this.value=this.value.toUpperCase()</code></pre>

<a name="key_0_0_2_4"></a>

##### class

<p>Class.</p>
<pre><code>    class: _user_only_this_class_</code></pre>

<a name="key_0_0_2_5"></a>

##### class_add

<p>Class add.</p>
<pre><code>    class_add: _add_a_class_</code></pre>

<a name="key_0_0_2_6"></a>

##### placehoder

<p>Placeholder.</p>
<pre><code>    placeholder: 'Text (1-10)'</code></pre>

<a name="key_0_0_2_7"></a>

##### validator

<p>Validators.</p>
<pre><code>    validator:
      -
        plugin: form/form_v1
        method: validate_integer</code></pre>

<a name="key_0_0_2_8"></a>

##### settings

<p>Settings.</p>
<pre><code>    settings:
      enabled: false</code></pre>

<a name="key_0_0_2_9"></a>

##### info

<p>Info text.</p>
<pre><code>    info:
      text: 'Select a member.'
      i18n: false (optional, default true)</code></pre>

<a name="key_0_0_2_10"></a>

##### option

<p>Options.</p>
<pre><code>    option:
      '': ''
      'Yes': 'Yes'</code></pre>

<a name="key_0_0_2_11"></a>

##### option (method)

<p>Options from method.</p>
<pre><code>    option: method:memb_inc/abo:option_memb_account</code></pre>

<a name="key_0_0_2_12"></a>

##### option (yml)

<p>Options from yml.</p>
<pre><code>    option: yml:/plugin/_folder_/_folder_/mysql/schema.yml:tables/TABLE_NAME/field/FIELD_NAME/option </code></pre>

<a name="key_0_0_3"></a>

#### focus_first_element

<p>Focus on first element.</p>
<pre><code>focus_first_element: false</code></pre>

<a name="key_0_0_4"></a>

#### ajax_element

<p>Set element where to put ajax request (optional).</p>
<pre><code>ajax_element: Use this to put ajax request in an element.</code></pre>

<a name="key_0_0_5"></a>

#### buttons_align_right

<p>Align buttons right.</p>
<pre><code>buttons_align_right: false</code></pre>

<a name="key_0_0_6"></a>

#### Methods

<p>Methods to run on render, capture, validation.</p>
<ul>
<li>One could omit param plugin to run current plugin in request.</li>
<li>Or one could replace param plugin with "[plugin]" to run current plugin in the request.</li>
</ul>

<a name="key_0_0_6_0"></a>

##### render

<p>Render methods.</p>
<pre><code>render:
  plugin: xxx/yyy
  method: form_render</code></pre>

<a name="key_0_0_6_1"></a>

##### validation_before

<p>Validation methods.</p>
<pre><code>validation_before:
  plugin: _plugin_/_plugin_
  method: form_validate</code></pre>
<p>Example in PHP.</p>
<pre><code>public function form_validate($form){
  $form = new PluginWfArray($form);
  if(wfRequest::get('id')){
    $rs = $this-&gt;db_memb_messages_one_by_inc();
    if($rs-&gt;get('count_mail')){
      $form-&gt;set("items/id/is_valid", false);
      $form-&gt;set("items/id/errors/", 'One could not edit if there is mail!');
    }
  }
  return $form-&gt;get();
}</code></pre>

<a name="key_0_0_6_2"></a>

##### capture

<p>Capture methods called after validation.</p>
<pre><code>capture:
  plugin: xxx/yyy
  method: form_capture</code></pre>

<a name="key_0_0_7"></a>

#### i18n

<p>Param i18n/path is used on validate and translate options.
This also sets the globals if not set.</p>
<pre><code>i18n:
  path: /plugin/_some_/_plugin_/i18n</code></pre>

<a name="key_0_0_8"></a>

#### settings

<p>Change global param.
Omit this if param i18n/path is set and should be the same.</p>
<pre><code>settings:
  globals:
    -
      path_to_key: 'settings/plugin/i18n/translate_v1/settings/path'
      value: '/plugin/_folder_/_folder_/i18n'</code></pre>

<a name="key_0_0_9"></a>

#### elements (4)

<p>Elements outside form.</p>
<pre><code>elements_above:
  -
    type: p
    innerHTML: Element inside form element first.
elements_below:
  -
    type: p
    innerHTML: Element inside form element below.
elements_before:
  -
    type: p
    innerHTML: Element before form element.
elements_after:
  -
    type: p
    innerHTML: Element after form element.</code></pre>

<a name="key_0_0_10"></a>

#### layout

<p>Using param layout to place controls. Using type div to move div with label and form element. Also button can be moved.</p>
<pre><code>layout:
  -
    type: div
    attribute: {class: row}
    innerHTML:
      -
        type: div
        attribute: {class: col-md-6}
        innerHTML: 'item[{"id": "name", "type": "div"}]'
      -
        type: div
        attribute: 
          class: col-md-4
          style: 
            padding-top: 30px
        innerHTML: 'item[{"id": "save", "type": "control"}]'</code></pre>
<p>Using type control to only move the form element.</p>
<pre><code>  -
    type: div
    attribute: {class: row}
    innerHTML:
      -
        type: div
        attribute: {class: col-md-12 font-weight-bold}
        innerHTML: Description
      -
        type: div
        attribute: {class: col-md-12}
        innerHTML: 'item[{"id": "description", "type": "control"}]'</code></pre>

<a name="key_0_0_11"></a>

#### submit_method

<p>Submit method.
Set optional submit javascript method. Could be handy for extra validation.</p>
<pre><code>submit_method: 'my_form_method()'</code></pre>

<a name="key_0_0_12"></a>

#### ctrl_s_save

<p>Save on CTRL+S.
Save form when user enter CTRL+S on keyboard. Require plugin wf/onkeypress to be included.</p>
<pre><code>ctrl_s_save: true</code></pre>

<a name="key_0_0_13"></a>

#### buttons

<p>Handle submit.
Add a button with script to handle submit.</p>
<pre><code>buttons:
  -
    type: a
    attribute: 
      class: btn btn-primary
      data-content: Are you sure?
      onclick: |
        if(confirm(this.getAttribute('data-content'))){
          document.getElementById('_btn_save_').click();
        }
    innerHTML: Save</code></pre>

<a name="key_0_0_14"></a>

#### Hide default button.

<pre><code>elements_below:
  -
    type: script
    innerHTML: document.getElementById('_btn_save_').style.display = 'none'</code></pre>

<a name="key_0_1"></a>

### PHP

<p>Render form.</p>
<pre><code>$form = new PluginWfYml(__DIR__.'/form/_my_form.yml');
$widget = wfDocument::createWidget('form/form_v1', 'render', $form-&gt;get());
wfDocument::renderElement(array($widget));</code></pre>
<p>Capture form.</p>
<pre><code>$form = new PluginWfYml(__DIR__.'/form/_my_form.yml');
$widget = wfDocument::createWidget('form/form_v1', 'capture', $form-&gt;get());
wfDocument::renderElement(array($widget));</code></pre>
<p>Set defaults from array.</p>
<pre><code>$obj = new PluginFormForm_v1();
$obj-&gt;setData($form-&gt;get());
$obj-&gt;setDefaultsFromArray(array('name' =&gt; 'James Smith'));
$form = new PluginWfArray($obj-&gt;data);</code></pre>
<p>Capture method.</p>
<pre><code>public function form_capture(){
  // To something...
  return array("alert('Some javascript to be fired.');");
}</code></pre>

<a name="key_0_2"></a>

### Wait indicator

<p>A loading image will appears in form button when form is submitted.</p>

<a name="key_0_3"></a>

### Wait indicator (deprecated)

<p>Deprecated due to scroll problem.
Include this widget in head section to get an wait indicator.</p>
<pre><code>type: widget
data:
  plugin: bootstrap/alertwait
  method: include            </code></pre>

<a name="key_0_4"></a>

### Send form as mail using send method

<p>If param mailqueue is true phpmailer is omited. Proper settings for PluginMailQueue is required.
Param subject is only in usage if mailqueue is in use.</p>
<pre><code>capture:
  plugin: 'form/form_v1'
  method: send
  data:
    phpmailer: 'yml:/theme/[theme]/config/phpmailer.yml'
    mailqueue: true
    mailqueue_tag: '_my_tag_'
    subject: 'Contact from my homepage'
    email:
      - 'me@world.com'
    script:
      - "alert('Message was sent.');location.href='/';"  </code></pre>

<a name="key_0_5"></a>

### Placeholder

<p>If type is date and placeholder is empty it will be set to YYYY-MM-DD.
If placeholder is 0.* a script to replace comma (,) with dot (.) is added.</p>
<ul>
<li>If placeholder is 0 validator validate_integer is added in widget_capture.</li>
<li>If placeholder is 0.* validator validate_double is added in widget_capture.</li>
<li>If placeholder is "Text (1-4)" validator is added for min/max length.</li>
<li>If placeholder is "(33 digits)" validator validate/digits, validate_digits is added (with length of 33).</li>
<li>If placeholder is "@" validator validate_email is added.</li>
<li>If placeholder is "Link" validator validate_link is added.</li>
</ul>
<p>One could add validator data via param validator_data.</p>
<pre><code>items:
  price:
    type: varchar
    label: Price
    placeholder: '0'
    validator_data:
      min: 1
      max: 10</code></pre>

<a name="key_0_6"></a>

### Errors in PHP

<p>Params is_valid, errors and errors_script should be set in a validator script.</p>
<pre><code>items:
  name:
    type: varchar
    label: Name
    is_valid: false
    errors:
      - Error in name field.
    errors_script:
      - alert('Error script for name field.')</code></pre>

<a name="key_0_7"></a>

### Options method

<p>SQL.</p>
<pre><code>db_option:
  sql: |
    select
    id,
    name
    from friends
    order by name
  select:
    - value
    - option</code></pre>
<p>Method</p>
<pre><code>  public function option_friends(){
    $temp = $this-&gt;db_option();
    $obj = new PluginFormForm_v1();
    $option = $obj-&gt;getOption($temp);
    return $option;
  }</code></pre>
<p>Form settings</p>
<pre><code>items:
  friends_id:
    type: varchar
    label: Friend
    default: rs:friends_id
    option: method:my/plugin:option_friends</code></pre>

<a name="key_0_8"></a>

### Using param ajax_element

<p>When this is set form will be sent to this element by id. In this case one has to deal with validation like this.</p>
<pre><code>public function page_form_capture(){
  /**
    * Form data.
    */
  $data = new PluginWfYml(__DIR__.'/form_data.yml');
  /**
    * Form plugin.
    */
  wfPlugin::includeonce('form/form_v1');
  $form = new PluginFormForm_v1();
  $form-&gt;setData($data-&gt;get());
  $form-&gt;bindAndValidate();
  /**
    * Is valid/invalid?
    */
  if(!$form-&gt;getData('is_valid')){
    exit('Some validation problem...');
  }else{
    exit('Do stuff...');
  }
}</code></pre>

<a name="key_1"></a>

## Widgets



<a name="key_1_0"></a>

### widget_capture

<p>Capture form.</p>
<pre><code>type: widget
data:
  plugin: 'form/form_v1'
  method: capture
  data: yml:/plugin/my/plugin/form/form.yml</code></pre>

<a name="key_1_1"></a>

### widget_include

<p>Include in document head.</p>
<pre><code>type: widget
data:
  plugin: 'form/form_v1'
  method: include               </code></pre>

<a name="key_1_2"></a>

### widget_render

<p>Render form.</p>
<pre><code>type: widget
data:
  plugin: 'form/form_v1'
  method: render
  data: yml:/plugin/my/plugin/form/form.yml</code></pre>

<a name="key_2"></a>

## Construct



<a name="key_2_0"></a>

### __construct



<a name="key_3"></a>

## Methods



<a name="key_3_0"></a>

### translate



<a name="key_3_1"></a>

### setData



<a name="key_3_2"></a>

### getData



<a name="key_3_3"></a>

### setDataFromFile



<a name="key_3_4"></a>

### getSchemaFieldPrimary



<a name="key_3_5"></a>

### setFormItemsDefaultFromDb



<a name="key_3_6"></a>

### getSchema



<a name="key_3_7"></a>

### getRow



<a name="key_3_8"></a>

### getLabel



<a name="key_3_9"></a>

### isValid



<a name="key_3_10"></a>

### hasValidationBefore



<a name="key_3_11"></a>

### hasCapture



<a name="key_3_12"></a>

### hasRender



<a name="key_3_13"></a>

### has



<a name="key_3_14"></a>

### bind



<a name="key_3_15"></a>

### setDefaultsFromArray



<a name="key_3_16"></a>

### setPostvalue



<a name="key_3_17"></a>

### setOptionFromArray



<a name="key_3_18"></a>

### getOption



<a name="key_3_19"></a>

### validate



<a name="key_3_20"></a>

### bindAndValidate



<a name="key_3_21"></a>

### setErrorField



<a name="key_3_22"></a>

### getErrors



<a name="key_3_23"></a>

### getErrorsScript



<a name="key_3_24"></a>

### getErrorsAsArray



<a name="key_3_25"></a>

### validate_email



<a name="key_3_26"></a>

### validate_password



<a name="key_3_27"></a>

### validatePasswordAbcdef09



<a name="key_3_28"></a>

### validate_equal



<a name="key_3_29"></a>

### validate_date



<a name="key_3_30"></a>

### isDate



<a name="key_3_31"></a>

### validate_numeric



<a name="key_3_32"></a>

### validate_integer



<a name="key_3_33"></a>

### is_integer



<a name="key_3_34"></a>

### validate_double



<a name="key_3_35"></a>

### check_decimals



<a name="key_3_36"></a>

### is_double



<a name="key_3_37"></a>

### saveToYml



<a name="key_3_38"></a>

### runCaptureMethod



<a name="key_3_39"></a>

### test_validation_before



<a name="key_3_40"></a>

### test_capture



<a name="key_3_41"></a>

### schema_capture



<a name="key_3_42"></a>

### send

<p>Capture method to send all form data.</p>
<pre><code>capture:
  plugin: 'form/form_v1'
  method: send
  data:
    email:
      - 'me@world.com'
    script:
      - "alert('Message was sent!');location.href='/';"  
    mailqueue: true
    subject: Contact message</code></pre>
<p>If item session_id is included in form extra text is added to body who tells if session_id() was the same posted as in request.</p>

