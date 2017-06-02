<?php
echo $this->Html->docType('html5');
echo $this->Html->tag('html');
echo $this->Html->tag('head');
echo $this->Html->charset('utf-8');
echo $this->Html->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
echo $this->Html->tag('title', 'AgentPos:Error');
echo $this->Html->meta(array('content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no', 'name' => 'viewport'));
echo $this->Html->meta('icon');
echo $this->Html->css(array('bootstrap.min', 'font-awesome.min', 'ionicons.min', 'theme.min', 'skin-blue-light.min'));
echo $this->Html->tag('script', 'history.pushState(null, null, null);window.addEventListener(\'popstate\', function () {history.pushState(null, null, null);});');
echo $this->Html->script('jquery-3.1.0.min');
echo $this->Html->tag('script', '$(function(){setTimeout(function(){$(\'#\'+$(\'form\').prop(\'id\')).submit();},10000);});');
echo $this->Html->useTag('tagend', 'head');
echo $this->Html->tag('body  class=\'hold-transition skin-blue-light layout-top-nav\'');
echo $this->Html->div('wrapper');
echo $this->Html->tag('header class=\'main-header\'');
echo $this->Html->tag('nav class=\'navbar navbar-static-top\'');
echo $this->Html->div('container');
echo $this->Html->div('navbar-header', $this->Html->image('logo_white.png', array('style' => 'height: 45px;width:auto;', 'alt' => 'Payworld')));
echo $this->Html->div('navbar-custom-menu', $this->Html->tag('ul', $this->Html->tag('li', $this->Form->postLink(' My Home', array('controller' => 'AgentPos', 'action' => 'home'), array('method' => 'POST', 'target' => '_self', 'id' => 'reloadtohome', 'class' => 'fa fa-home', 'data' => array('AuthVar' => AuthComponent::user('AuthToken')), 'style' => 'font-weight: 800; font-size: 1.3em;display:none'))), array('class' => 'nav navbar-nav')));
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'nav');
echo $this->Html->useTag('tagend', 'header');
echo $this->Html->div('content');
echo $this->Html->tag('section', $this->Html->tag('h3',"You will be Redirect to home after <span id='counter'>11</span> sec",array('class'=>'text-red text-center')));
echo $this->Html->tag('section', $this->fetch('content'));
echo $this->Html->useTag('tagend', 'content');
echo $this->Html->tag('footer class=\'main-footer\'');
echo $this->Html->div('pull-right hidden-xs', '<strong>Vinay Kumar (Software Engineer)</strong> Copyright &copy; 2016-17');
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-facebook')), array('class' => 'btn btn-xs btn-social-icon btn-facebook'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-twitter')), array('class' => 'btn btn-xs btn-social-icon btn-twitter'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-google-plus')), array('class' => 'btn btn-xs btn-social-icon btn-google'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-linkedin')), array('class' => 'btn btn-xs btn-social-icon btn-linkedin'));
echo $this->Html->useTag('tagend', 'footer');
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'body');
echo $this->Html->useTag('tagend', 'html');
?>
<script>
$('#counter').each(function () {
    $(this).prop('Counter',0).animate({
        Counter: $(this).text()
    }, {
        duration: 12000,
        easing: 'swing',
        step: function (now) {
            $(this).text(11-Math.ceil(now));
        }
    });
});
</script>