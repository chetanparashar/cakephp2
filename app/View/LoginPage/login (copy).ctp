<?php
echo $this->Html->div('login-box');
echo $this->Html->div('login-box-body');
echo $this->Html->div('login-logo', $this->Html->image('logo_blue.png', array('alt' => 'Payworld', 'style' => 'width:200px;height:auto;')));
echo $this->Html->para('text-red', isset($ResponseArr['authMessage']) ? $ResponseArr['authMessage'] : '', array('style' => 'text-align:center'));
echo $this->Form->create('LoginPage', array('url' => array('controller' => 'LoginPage', 'action' => 'login'), 'target' => '_self'));
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
//echo $this->Form->postButton('I forgot my password', array('controller' => 'Analytics', 'action' => 'reset'), array('method' => 'POST', 'target' => '_self','class'=>'btn-link'));
echo $this->Html->useTag('tagend', 'div');
echo $this->Html->useTag('tagend', 'div');
?>
<script>
    function validateLogin() {
        if ($.trim($("#login").val()) == "") {
            alert("Enter Login Name");
            return false;
        } else if ($.trim($("#pass").val()) == "") {
            alert("Enter Password");
            return false;
        } else {
            $("#loginname").val(base64_encode($("#login").val()));
            $("#password").val(base64_encode(MD5($("#pass").val()).toUpperCase()));
            $("#login,#pass").val('');
            return true;
        }
    }
</script>
