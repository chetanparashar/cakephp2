<?php

echo $this->Html->docType('html5');
echo $this->Html->tag('html');
echo $this->Html->tag('head');
echo $this->Html->charset('utf-8');
echo $this->Html->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
echo $this->Html->meta(array('content' => 'width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no', 'name' => 'viewport'));
echo $this->Html->meta('icon');
echo $this->Html->tag('title', "Analytics");
echo $this->Html->css(array('loader', 'bootstrap.min', 'font-awesome.min', 'ionicons.min', 'theme.min', 'skin-blue-light.min'));
echo $this->Html->script(array('jquery-3.1.0.min', 'jquery-migrate-3.0.0.min', 'bootstrap.min', 'security.min'));
echo $this->Html->tag('script', "history.pushState(null, null, null);window.addEventListener('popstate', function () {history.pushState(null, null, null);});");
echo $this->Html->useTag('tagend', 'head');
echo $this->Html->tag('body  class=\'hold-transition login-page\' style=\'background: #00a0db;\'');
//echo $this->Html->div('loaderDiv', $this->Html->div('', $this->Html->div('cssload-whirlpool', ''), array('style' => 'position:fixed;top:45%;left:50%;z-index:1999;')), array('id' => 'loaderDiv', 'style' => "position: fixed; top: 0px;bottom: 0px; left: 0px; right: 0px; background: rgba(155, 155, 155, 0.8); z-index: 1888;"));
echo $this->fetch('content');
echo $this->Html->useTag('tagend', 'body');
echo $this->Html->useTag('tagend', 'html');
//echo $this->Html->tag('script', "document.getElementById('loaderDiv').style.display='none';");
echo $this->Html->tag('script', "$(document).ready(function(){ $('form').on('submit',function(){ });});");
