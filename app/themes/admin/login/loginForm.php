<?php $this->layout('layout::standard') ?>
<div class="container">
    <div class="row">
        <div class="col-4 offset-4">
            <div class="login-panel panel panel-default">
                <div class="panel-heading" align="center">
                    <h2 class="panel-title"><?=$this->t('Enter login parameters:')?></h2>
                </div>
                <div class="panel-body panel-bodyWhite">
                    <form id="login-form" action="/login/login/" method="post">
                        <?=$this->formToken(); ?>
                        <fieldset>
                            <div class="form-group">
                                <div class="input-group">
                                    <div class="input-group-addon login-form-addon">
                                        <i class="fa fa-envelope"></i>
                                    </div>
                                    <input placeholder="<?=$this->t('Email:')?>" type="text" name="email" required class="form-control" autofocus/>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon login-form-addon"><i class="fa fa-lock"></i></span>
                                    <input required class="form-control" placeholder="<?=$this->t('Password:')?>" type="password" name="password"/>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-addon login-form-addon"><?=$this->t('Remember me for 30 days:')?></span>
                                    <input class="form-control" type="checkbox" name="rememberMe" />
                                </div>
                            </div>
                            <div class="form-group">
                                <input type="submit" value="<?=$this->t('Login:')?>" class="btn btn-lg btn-primary btn-block"/>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>