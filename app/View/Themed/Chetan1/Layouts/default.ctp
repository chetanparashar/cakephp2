<?php

echo $this->Html->docType('html5');
echo $this->Html->tag('html');
echo $this->Html->tag('head');
echo $this->Html->charset('utf-8');
echo $this->Html->meta(array("http-equiv" => "X-UA-Compatible", "content" => "IE=edge"));
echo $this->Html->meta(array('content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no', 'name' => 'viewport'));
echo $this->Html->meta('icon');
echo $this->fetch('meta');
echo $this->Html->tag('title', "Agent Pos");
echo $this->Html->css(array('loader', 'bootstrap.min', 'font-awesome.min', 'ionicons.min', 'theme.min', 'skin-blue-light.min', 'jquery-ui'));
echo $this->fetch('css');
echo $this->Html->script(array('jquery-3.1.0.min', 'jquery-migrate-3.0.0.min', 'bootstrap.min', 'jquery.slimscroll.min', 'fastclick.min', 'theme.min', 'jquery-ui.min', 'validation'));
echo $this->Html->tag('script', "history.pushState(null, null, null);window.addEventListener('popstate', function () {history.pushState(null, null, null);});");
echo $this->fetch('script');
echo $this->Html->useTag('tagend', 'head');
echo $this->Html->tag('body  class="hold-transition skin-blue-light fixed sidebar-mini"'); // fixed
echo $this->Html->div('loaderDiv', $this->Html->div('', $this->Html->div('cssload-whirlpool', ''), array('style' => 'position:fixed;top:45%;left:50%;z-index:1999;')), array('id' => 'loaderDiv', 'style' => "position: fixed; top: 0px;bottom: 0px; left: 0px; right: 0px; background: rgba(155, 155, 155, 0.8); z-index: 1888;"));
echo $this->Html->div('wrapper');
echo $this->Html->tag('header class="main-header"');
echo $this->Html->div('logo');
echo $this->Html->div('logo-mini', $this->Html->image('logo_white_mini.png', array('style' => 'height: 45px;width:auto;', 'alt' => 'PW')));
echo $this->Html->div('logo-lg', $this->Html->image('logo_white.png', array('style' => 'height: 45px;width:auto;', 'alt' => 'Payworld')));
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->tag('nav class="navbar navbar-static-top"');
echo $this->Html->div('sidebar-toggle', '<span class="sr-only">Toggle navigation</span><span class="icon-bar"></span><span class="icon-bar"></span><span class="icon-bar"></span>', array("data-toggle" => "offcanvas", "role" => "button"));
echo $this->Html->div("navbar-custom-menu", $this->element('menu_profile'));
echo $this->Html->useTag('tagend', 'nav');
echo $this->Html->useTag('tagend', 'header');
echo $this->Html->tag('aside class="main-sidebar"');
echo $this->Html->tag('section', $this->element('menu_list'), array("class" => "sidebar"));
echo $this->Html->useTag('tagend', 'aside');
echo $this->Html->div('content-wrapper');
echo $this->Html->tag('section', $this->Html->tag('marquee', "Welcome To Payworld's New Agent Pannel", array('class' => 'text-red', 'style' => 'position: fixed; background: #FFF;z-index:999')), array('class' => 'content-header', "style" => "padding: 0px;"));
echo $this->Html->tag('section', $this->fetch('content'), array('class' => "content"));
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->tag('footer class="main-footer"');
echo $this->Html->div("pull-right hidden-xs", "<strong>Vinay Kumar (Software Engineer)</strong> Copyright &copy; 2016-17");
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-facebook')), array('class' => 'btn btn-xs btn-social-icon btn-facebook'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-twitter')), array('class' => 'btn btn-xs btn-social-icon btn-twitter'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-google-plus')), array('class' => 'btn btn-xs btn-social-icon btn-google'));
echo $this->Html->tag('a', $this->Html->tag('i', '', array('class' => 'fa fa-linkedin')), array('class' => 'btn btn-xs btn-social-icon btn-linkedin'));
echo $this->Html->useTag('tagend', 'footer');
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'body');
echo $this->Html->useTag('tagend', 'html');
echo $this->Html->tag('script', "document.getElementById('loaderDiv').style.display='none';");
echo $this->Html->tag('script', "$(document).ready(function(){ $('form').on('submit',function(){ $('#loaderDiv').show();});});");
if (Configure::read('debug') < 2) {
    echo $this->Html->tag('script', 'document.body.addEventListener("cut",function(e){e.preventDefault();});document.body.addEventListener("copy",function(e){e.preventDefault();});document.body.addEventListener("paste",function(e){e.preventDefault();});document.oncontextmenu=document.body.oncontextmenu=function(){return false;};');
}