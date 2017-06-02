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
echo $this->Html->tag('script', 'history.pushState(null, null, null);window.addEventListener(\'popstate\', function () {history.pushState(null, null, null);});');
echo $this->Html->useTag('tagend', 'head');
echo $this->Html->tag('body  class=\'hold-transition login-page\' style=\'background: #00a0db;\'');
echo $this->Html->div('loaderDiv', $this->Html->div('', $this->Html->div('cssload-whirlpool', ''), array('style' => 'position:fixed;top:45%;left:50%;z-index:1999;')), array('id' => 'loaderDiv', 'style' => "position: fixed; top: 0px;bottom: 0px; left: 0px; right: 0px; background: rgba(155, 155, 155, 0.8); z-index: 1888;"));
echo $this->Html->div('login-box');
echo $this->Html->div('login-box-body');
if (!$this->elementExists('login')) {
    echo $this->Html->div('login-logo', $this->Html->image('logo_blue.png', array('alt' => 'Payworld', 'style' => 'width:200px;height:auto;')));
    echo $this->Html->para('text-red', isset($ResponseArr['authMessage']) ? $ResponseArr['authMessage'] : '', array('style' => 'text-align:center'));
    echo $this->Form->create('Apos', array('url' => array('controller' => 'AgentPos', 'action' => 'login'), 'target' => '_self'));
    echo $this->Html->div('form-group has-feedback');
    echo $this->Form->input('login', array('label' => FALSE, 'div' => FALSE, 'placeholder' => 'Login Name', 'value' => '', 'class' => 'form-control'));
    echo $this->Html->tag('span', '', array('class' => 'glyphicon glyphicon-user form-control-feedback'));
    echo $this->Html->useTag('tagend', 'div');
    echo $this->Html->div('form-group has-feedback');
    echo $this->Form->password('pass', array('label' => FALSE, 'div' => FALSE, 'placeholder' => 'Password', 'value' => '', 'class' => 'form-control'));
    echo $this->Html->tag('span', '', array('class' => 'glyphicon glyphicon-lock form-control-feedback'));
    echo $this->Html->useTag('tagend', 'div');
    echo $this->Form->input('loginname', array('type' => 'hidden', 'value' => ''));
    echo $this->Form->input('password', array('type' => 'hidden', 'value' => ''));
    echo $this->Form->unlockField('loginname');
    echo $this->Form->unlockField('password');
    echo $this->Form->end(array('label' => 'Sign In', 'onclick' => 'return validateLogin();', 'class' => 'btn btn-primary btn-block btn-flat'));
    echo $this->Form->postLink('I forgot my password', array('controller' => 'AgentPos', 'action' => 'reset'), array('method' => 'POST', 'target' => '_self'));
    echo $this->Html->tag('script', 'function validateLogin(){if($.trim($("#login").val())==""){alert("Enter Login Name");return false;}else if($.trim($("#pass").val())==""){alert("Enter Password");return false;}else{$("#loginname").val(base64_encode($("#login").val()));$("#password").val(base64_encode(MD5($("#pass").val()).toUpperCase()));$("#login,#pass").val("");return true;}}');
} else {
    echo $this->element('login');
}
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'body');
echo $this->Html->useTag('tagend', 'html');
echo $this->Html->tag('script', "document.getElementById('loaderDiv').style.display='none';");
echo $this->Html->tag('script', "$(document).ready(function(){ $('form').on('submit',function(){ $('#loaderDiv').show();});});");