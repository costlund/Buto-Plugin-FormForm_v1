function plugin_form_form_v1(){
  this.btn_submit = null;
  this.d = null;
  /**
   * Move controls if form has own layout.
   * @param {type} data
   * @returns {unresolved}
   */
  this.renderLayout = function(data){
    var layout = document.getElementById(data.id+'_layout');
    var elements = layout.getElementsByTagName('*');
    for(var i=0;i<elements.length;i++){
      var element = elements[i];
      var innerHTML = element.innerHTML;
      if(innerHTML.substr(0, 5)=='item['){
        element.innerHTML = '';
        var json = JSON.parse(innerHTML.substr(4));
        for(var i=0; i<json.length; i++){
          if(json[i].type == 'control'){
            if(document.getElementById(data.id+'_'+json[i].id)){element.appendChild(document.getElementById(data.id+'_'+json[i].id));}
          }else if(json[i].type == 'label'){
            if(document.getElementById('label_'+data.id+'_'+json[i].id)){element.appendChild(document.getElementById('label_'+data.id+'_'+json[i].id));}
          }else if(json[i].type == 'info'){
            if(document.getElementById('info_'+data.id+'_'+json[i].id)){element.appendChild(document.getElementById('info_'+data.id+'_'+json[i].id));}
          }else if(json[i].type == 'div'){
            if(document.getElementById('div_'+data.id+'_'+json[i].id)){element.appendChild(document.getElementById('div_'+data.id+'_'+json[i].id));}
          }
        }
      }
    }
    return null;
  }
  this.submit = function(data, btn_submit){
    this.btn_submit = btn_submit;
    if(data.submit_method && data.submit_method!='null'){
      eval(data.submit_method);
      return false;
    }else if(data.ajax_element){
      if(typeof PluginBootstrapAlertwait == 'object'){
        PluginBootstrapAlertwait.run();
        PluginWfCallbackjson.setElement(data.ajax_element, data.url, data.id )     
        PluginBootstrapAlertwait.close();
        return false; 
      }else{
        PluginWfCallbackjson.setElement(data.ajax_element, data.url, data.id ); 
        return false;
      }
    }else{
      if(false && typeof PluginBootstrapAlertwait == 'object'){
        PluginBootstrapAlertwait.run();
        $.post(data.url, $('#'+data.id).serialize()).done(function(data) {
          PluginBootstrapAlertwait.close();
          PluginWfCallbackjson.call( data );
        });
        return false;
      }else{
        this.loading_add(data);
        $.post(data.url, $('#'+data.id).serialize()).done(function(d) {
          PluginFormForm_v1.loading_remove();
          /**
           * 
           */
          var json_data = null;
          try {
            json_data = JSON.parse(d);
          } catch(e) {
            PluginFormForm_v1.d = d;
            alert('PluginFormForm_v1 says: There was a problem when trying to post this form to '+data.url+'! <a href="#" onclick="PluginFormForm_v1.alert_d()">More!</a>');
            return null;
          }          
          /**
           * 
           */
          if(!json_data.data || json_data.data.is_valid){
            if(json_data.data){
              $('#'+json_data.data.id+'_alert').collapse('hide');
            }
            PluginWfCallbackjson.call( d );
          }else{
            if(!document.getElementById(json_data.data.id+'_alert')){
              PluginWfCallbackjson.call( d );
            }else{
              /**
               * Run script.
               * Avoid first script because that should be the alert with errors.
               */
              if(json_data.script){
                for(var i = 0; i<json_data.script.length; i++){
                  if(i>0){
                    eval(json_data.script[i]);
                  }
                }
              }
              /**
               * 
               */
              var html = '';
              for(var i=0;i<json_data.data.errors.length;i++){
                html += '<strong>'+json_data.data.errors[i]+'</strong><br>';
              }
              /**
               * Remove class is-invalid.
               */
              for(var item in json_data.data.items){
                $('#'+json_data.data.id+'_'+item).removeClass('is-invalid');
              }
              for(var item in json_data.data.items){
                if(!json_data.data.items[item].is_valid){
                  for(var j=0;j<json_data.data.items[item].errors.length;j++){
                    html += json_data.data.items[item].errors[j]+'<br>';
                    /**
                     * Add class is-invalid.
                     */
                    $('#'+json_data.data.id+'_'+item).addClass('is-invalid');
                  }
                }
              }
              document.getElementById(json_data.data.id+'_alert').style.display='';
              document.getElementById(json_data.data.id+'_alert').innerHTML=html;
              $('#'+json_data.data.id+'_alert').collapse('show');
            }
          }
        });
        return false; 
      }
    }
  }
  this.alert_d = function(){
    alert(PluginFormForm_v1.d);
  }
  this.loading_add = function(data){
    var img = document.createElement('img');
    img.src = '/plugin/form/form_v1/loading.gif';
    img.className = 'plugin_form_form_v1_loading';
    img.style.marginLeft = '10px';
    img.style.marginRight = '10px';
    if(!data.buttons_align_right){
      document.getElementById(data.id+'_save').parentNode.appendChild(img);
    }else{
      document.getElementById(data.id+'_save').parentNode.insertBefore(img, document.getElementById(data.id+'_save'));
    }
    /**
     * Button disabled
     */
    this.btn_submit.setAttribute('disabled', 'disabled');
  }
  this.loading_remove = function(){
    $(".plugin_form_form_v1_loading").remove();
    /**
     * Button disabled
     */
    this.btn_submit.removeAttribute('disabled');
  }
  this.keypress = function(element, data){
    if(element.keyCode==13 && (element.target.tagName=='INPUT' || element.target.tagName=='SELECT') ){
      PluginFormForm_v1.submit(data);
    }
  }
  /**
   * Set focus on first element in form after 0.5 sec.
   */
  this.focus = function(data){
    var x = document.getElementById(data.id).elements;    
    var y = null;
    var focus_element = false;
    for (var i = 0; i < x.length; i++) {
      if(x[i].tagName=='INPUT' && x[i].getAttribute('type')!='hidden'){
        focus_element = true;
      }else if(x[i].tagName=='SELECT'){
        focus_element = true;
      }else if(x[i].tagName=='TEXTAREA'){
        focus_element = true;
      }
      if(y == null && focus_element){
        y = x[i];
      }
    }
    if(y != null && focus_element){
      setTimeout(plugin_form_form_v1_focus, 500);
    }
    function plugin_form_form_v1_focus(){y.focus();}    
  }
  this.input_placeholder = function(data){
    if(data.placeholder.indexOf('0.') != -1){
      document.getElementById(data.id).onkeyup = function(){ this.value = this.value.replace(',', '.'); }    
    }
  }
  this.add_option = function(data, id, default_option){
    var selected = '';
    var select_match = false;
    for (const property in data) {
      if(property==default_option){
        selected = ' selected';
        select_match = true;
      }else{
        selected = '';
      }
      $('#'+id).append("<option value='" + property+ "' "+selected+">" + data[property] + "</option>");
    }
    if(default_option.length && !select_match){
      $('#'+id).append("<option value='" + default_option + "' selected>(Value " + default_option + " has no option)</option>");
    }
  }
}
var PluginFormForm_v1 = new plugin_form_form_v1();
