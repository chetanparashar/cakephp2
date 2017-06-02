<?php
echo $this->Html->docType('html5');
echo $this->Html->tag('html');
echo $this->Html->tag('head');
echo $this->Html->charset('utf-8');
echo $this->Html->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
echo $this->Html->meta(array('content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no', 'name' => 'viewport'));
echo $this->Html->meta('icon');
echo $this->Html->tag('title', "Agent Pos");
echo $this->Html->css(array('loader', 'bootstrap.min', 'font-awesome.min', 'ionicons.min', 'theme.min', 'skin-blue-light.min'));
echo $this->Html->script(array('jquery-3.1.0.min', 'jquery-migrate-3.0.0.min', 'bootstrap.min', 'security.min'));
echo $this->Html->tag('script', "history.pushState(null, null, null);window.addEventListener('popstate', function () {history.pushState(null, null, null);});");
echo $this->Html->tag('script', '$(function(){setTimeout(function(){$(\'#\'+$(\'form\').prop(\'id\')).submit();},10000);});');
echo $this->Html->useTag('tagend', 'head');
echo $this->Html->tag('body  class=\'hold-transition\'');
echo $this->Html->div('loaderDiv', $this->Html->div('', $this->Html->div('cssload-whirlpool', ''), array('style' => 'position:fixed;top:45%;left:50%;z-index:1999;')), array('id' => 'loaderDiv', 'style' => "position: fixed; top: 0px;bottom: 0px; left: 0px; right: 0px; background: rgba(155, 155, 155, 0.8); z-index: 1888;"));
echo $this->Form->postLink(' My Home', array('controller' => 'LoginPage', 'action' => 'home'), array('method' => 'POST', 'target' => '_self', 'id' => 'reloadtohome', 'class' => 'fa fa-home', 'style' => 'font-weight: 800; font-size: 1.3em;display:none'));
echo $this->Html->div('content');
echo $this->Html->tag('section', $this->Html->tag('h3', "You will be Redirect to home after <span id='counter'>11</span> sec", array('class' => 'text-red text-center')));
echo $this->Html->tag('section', $this->fetch('content'));
echo $this->Html->useTag('tagend', 'content');
echo $this->Html->useTag('tagend', 'body');
echo $this->Html->useTag('tagend', 'html');
echo $this->Html->tag('script', "document.getElementById('loaderDiv').style.display='none';");
echo $this->Html->tag('script', "$(document).ready(function(){ $('form').on('submit',function(){ $('#loaderDiv').show();});});");
?>
<script>
    $('#counter').each(function () {
        $(this).prop('Counter', 0).animate({
            Counter: $(this).text()
        }, {
            duration: 12000,
            easing: 'swing',
            step: function (now) {
                $(this).text(11 - Math.ceil(now));
            }
        });
    });
</script>
