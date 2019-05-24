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

## Basic usage

*Param items/id/default will get value from request param name id.* 
Param capture is called after validation.

```
url: _url_where_to_post_
ajax: true
submit_value: Save
id: _my_form_
focus_first_element: true
items:
  id:
    type: varchar
    label: ID
    default: get:id
  customer_no:
    type: varchar
    label: Customer number
    validator:
      -
        plugin: form/form_v1
        method: validate_integer
    mandatory: true
  date:
    type: date
    label: Date
    placeholder: 'yyyy-mm-dd'
  memb_account_id:
    type: varchar
    label: Member
    mandatory: true
    default: rs:memb_account_id
    option: method:memb_inc/abo:option_memb_account
    info:
      text: 'Select a member.'
capture:
  plugin: xxx/yyy
  method: form_capture
```





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

## Wait indicator

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
