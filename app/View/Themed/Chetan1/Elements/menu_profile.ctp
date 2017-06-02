<ul class="nav navbar-nav">
    <li style="padding: 7px 0px;" >
        <div class="input-group">
            <div class="input-group-btn"><button class="btn btn-success"><i class="fa fa-inr"></i></button></div>
            <input class="form-control" placeholder="<?= (-1) * (AuthComponent::user('ClsBal')) ?>" id="ref_cls_bal" disabled="disabled" style="width: 125px;">
        </div>
    </li>
    <li class="dropdown user user-menu">
        <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <i class="glyphicon glyphicon-user"></i>
            <span> <i class="caret"></i></span>
        </a>
        <ul class="dropdown-menu">
            <li class="user-header" style="height: auto;">
                <?php
                $email = AuthComponent::user("emailid");
                echo $this->Html->image('user.jpg', array('class' => 'img-circle', 'alt' => "User Image"));
                echo $this->Html->para("", AuthComponent::user("agentname") . ' (' . AuthComponent::user("ph1") . ')' . $this->Html->tag('small', empty($email) ? 'Email ID Not Registered.' : $email));
                ?>
            </li>
            <li class="user-footer">
                <div class="pull-left">
                    <?php
                    echo $this->Form->create('fpass', array('method' => 'post', 'target' => '_self', 'url' => array('controller' => 'AgentPos', 'action' => 'ChangePasswd')));
                    echo $this->Form->hidden('AuthVar', array('value' => AuthComponent::user("AuthToken")));
                    echo $this->Form->end(array('label' => 'Change Password', 'class' => 'btn btn-block btn-default', 'div' => FALSE));
                    ?>
                </div>
                <div class="pull-right">
                    <?php
                    echo $this->Form->create('login', array('method' => 'post', 'target' => '_self', 'url' => array('controller' => 'AgentPos', 'action' => 'logout')));
                    echo $this->Form->hidden('AuthVar', array('value' => AuthComponent::user("AuthToken")));
                    echo $this->Form->end(array('label' => 'Logout', 'class' => 'btn btn-block btn-default', 'div' => FALSE));
                    ?>
                </div>
            </li>
        </ul>
    </li>
</ul>