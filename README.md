# Buto-Plugin-FormForm_v1
Form plugin.






Set defaults from array.

```
$obj = new PluginFormForm_v1();
$obj->setData($form->get());
$obj->setDefaultsFromArray(array('name' => 'James Smith'));
$form = new PluginWfArray($obj->data);
```



