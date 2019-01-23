# Buto-Plugin-FormForm_v1
Form plugin.


## Basic usage

*Param items/id/default will get value from request param name id.* 

```
url: _url_where_to_post_
ajax: true
submit_value: Save
id: _my_form_
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
```





## Submit method
Set optional submit javascript method. Could be handy for extra validation.
```
submit_method: 'my_form_method()'
```



## PHP
Set defaults from array.

```
$obj = new PluginFormForm_v1();
$obj->setData($form->get());
$obj->setDefaultsFromArray(array('name' => 'James Smith'));
$form = new PluginWfArray($obj->data);
```




