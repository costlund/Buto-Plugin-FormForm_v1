# Buto-Plugin-FormForm_v1
Form plugin.

## Include in head

Include in head section.

```
type: widget
data:
  plugin: 'form/form_v1'
  method: include               
```


## Widget render
```
type: widget
data:
  plugin: 'form/form_v1'
  method: render
  data: _check_below_
```

## Data

```
url: _url_where_to_post_
ajax: true
submit_value: Save
id: _my_form_
items:
  id:
    type: varchar
    label: ID
  date:
    type: date
    label: Date
  text:
    type: text
    label: Text
```

Mandatory.
```
    mandatory: true
```

Default. Value from request param name id.
```
    default: get:id
```

I18N. Turn off.
```
    i18n: false
```

Attribute.
```
    attribute:
      onkeyup: this.value=this.value.toUpperCase()
```

Class only one.
```
    class: _user_only_this_class_
```

Class add.
```
    class_add: _add_a_class_
```

Placeholder.
```
    placeholder: 'Text (1-10)'
```

Validator.
```
    validator:
      -
        plugin: form/form_v1
        method: validate_integer
```

Settings.
```
    settings:
      enabled: false
```

Info text.
```
    info:
      text: 'Select a member.'
```

Options method.
```
    option: method:memb_inc/abo:option_memb_account
```

Options from yml.
```
    option: yml:/plugin/_folder_/_folder_/mysql/schema.yml:field/level/option 
```



Focus on first element.
```
focus_first_element: true
```


Set element where to put ajax request (optional).
```
ajax_element: Use this to put ajax request in an element. 
```



Align buttons right.
```
buttons_align_right: false
```

Method on render.
```
render:
  plugin: xxx/yyy
  method: form_render
```

Method on capture. Is called after validation.
```
capture:
  plugin: xxx/yyy
  method: form_capture
```

I18N. Param i18n/path is used on validate and translate options.
```
i18n:
  path: /plugin/_some_/_plugin_/i18n
```

I18N. Change global param.
```
settings:
  globals:
    -
      path_to_key: 'settings/plugin/i18n/translate_v1/settings/path'
      value: '/plugin/_folder_/_folder_/i18n'
```

Elements.
```
elements_above:
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
    innerHTML: Element after form element.
```

Validate before.
```
validation_before:
  plugin: _plugin_/_plugin_
  method: _a_method_
```

### Textarea
Default rows in a textarea are 5. Set param rows to change it.
```
  description:
    type: text
    label: Description
    rows: 20
```

### Info
Param info supports settings. 
In this example we omit i18n.
```
    info:
      settings:
        i18n: false
```

### Layout
Using param layout to place controls. Using type div to move div with label and form element. Also button can be moved.
```
layout:
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
        innerHTML: 'item[{"id": "save", "type": "control"}]'
```
Using type control to only move the form element.
```
layout:
  -
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
        innerHTML: 'item[{"id": "description", "type": "control"}]'
```

### Buttons align right

Set to true if buttons should be align right.

### Submit method
Set optional submit javascript method. Could be handy for extra validation.
```
submit_method: 'my_form_method()'
```

### Save on CTRL+S
Save form when user enter CTRL+S on keyboard. Require plugin wf/onkeypress to be included.
```
ctrl_s_save: true
```

### Settings
Settings param will be set for form element. This example show how to change i18n path.
```
settings:
  globals:
    -
      path_to_key: 'settings/plugin/i18n/translate_v1/settings/path'
      value: '/plugin/_folder_/_folder_/i18n'
```


## PHP

Render form.

```
$form = new PluginWfYml(__DIR__.'/form/_my_form.yml');
$widget = wfDocument::createWidget('form/form_v1', 'render', $form->get());
wfDocument::renderElement(array($widget));
```

Capture form.

```
$form = new PluginWfYml(__DIR__.'/form/_my_form.yml');
$widget = wfDocument::createWidget('form/form_v1', 'capture', $form->get());
wfDocument::renderElement(array($widget));
```


Set defaults from array.

```
$obj = new PluginFormForm_v1();
$obj->setData($form->get());
$obj->setDefaultsFromArray(array('name' => 'James Smith'));
$form = new PluginWfArray($obj->data);
```

Capture method.

```
public function form_capture(){
  // To something...
  return array("alert('Some javascript to be fired.');");
}
```



## Wait indicator

A loading image will appears in form button when form is submitted.

## Wait indicator (deprecated)

Deprecated due to scroll problem.

Include this widget in head section to get an wait indicator.

```
type: widget
data:
  plugin: bootstrap/alertwait
  method: include            
```

## Send form as mail using send method.
If param mailqueue is true phpmailer is omited. Proper settings for PluginMailQueue is required.
Param subject is only in usage if mailqueue is in use.
```
capture:
  plugin: 'form/form_v1'
  method: send
  data:
    phpmailer: 'yml:/theme/[theme]/config/phpmailer.yml'
    mailqueue: true
    subject: 'Contact from my homepage'
    email:
      - 'me@world.com'
    script:
      - "alert('Message was sent.');location.href='/';"  
```

## Focus first element
As default focus is on first element. One could turn it of by this parameter.

```
focus_first_element: false
```

## Placeholder

### Script

If type is date and placeholder is empty it will be set to YYYY-MM-DD.
If placeholder is 0.* a script to replace comma (,) with dot (.) is added.

### Validators

- If placeholder is 0 validator validate_integer is added in widget_capture.
- If placeholder is 0.* validator validate_double is added in widget_capture.
- If placeholder is "Text (1-4)" validator is added for min/max length.
- If placeholder is "(33 digits)" validator validate/digits, validate_digits is added (with length of 33).

One could add validator data via param validator_data.
```
items:
  price:
    type: varchar
    label: Price
    placeholder: '0'
    validator_data:
      min: 1
      max: 10
```


### Errors in PHP

Params is_valid, errors and errors_script should be set in a validator script.

```
items:
  name:
    type: varchar
    label: Name
    is_valid: false
    errors:
      - Error in name field.
    errors_script:
      - alert('Error script for name field.')
```

### Options method
SQL
```
db_option:
  sql: |
    select
    id,
    name
    from friends
    order by name
  select:
    - value
    - option
```
Method
```
  public function option_friends(){
    $temp = $this->db_option();
    $obj = new PluginFormForm_v1();
    $option = $obj->getOption($temp);
    return $option;
  }
```
Form settings
```
items:
  friends_id:
    type: varchar
    label: Friend
    default: rs:friends_id
    option: method:my/plugin:option_friends
```
### Using param ajax_element
When this is set form will be sent to this element by id. In this case one has to deal with validation like this.
```
public function page_form_capture(){
  /**
    * Form data.
    */
  $data = new PluginWfYml(__DIR__.'/form_data.yml');
  /**
    * Form plugin.
    */
  wfPlugin::includeonce('form/form_v1');
  $form = new PluginFormForm_v1();
  $form->setData($data->get());
  $form->bindAndValidate();
  /**
    * Is valid/invalid?
    */
  if(!$form->getData('is_valid')){
    exit('Some validation problem...');
  }else{
    exit('Do stuff...');
  }
}
```

