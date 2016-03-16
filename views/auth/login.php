
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3 col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Авторизация доступа</h3>
                </div>
                <div class="panel-body">
                    <form method="post" autocomplete="off" action="<?= createURL('', 'auth/login');?>">
                        <fieldset>
                            <div class="form-group">
                                <input id="autologin" class="form-control" placeholder="Начните набирать фамилию.." name="login" type="text" 
                                       autofocus required>
                                <input id="userid" name="userid" type="hidden" />
                            </div>                           
                            <div class="form-group">
                                <input id="upass" class="form-control" placeholder="Пароль" name="password" type="password" value="" required>
                            </div>
                            <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
