
    <div class="row">
        <div class="col-xs-6 col-xs-offset-3 col-md-4 col-md-offset-4">
            <div class="login-panel panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Смена пароля</h3>
                </div>
                <div class="panel-body">
                    <label for="npwd">Придумайте новый пароль</label>
                    <form method="post" action="<?= createURL('', 'auth/savepassword');?>">
                        <fieldset>
                            <div class="form-group">
                                <input id="npwd" class="form-control" placeholder="Введите новый пароль" name="password" type="password" value="" required autofocus>
                            </div>
                            <button class="btn  btn-primary btn-block" type="submit">Изменить</button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
