<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<link rel="icon" href="data:;base64,iVBORw0KGgo=">-->
        <link rel="icon" type="image/ico" href="/favicon.ico"/>
        <title><?= $this->pageTitle; ?></title>

        <link rel="stylesheet" href="/public/css/bootstrap.css"/>
        <link rel="stylesheet" href="/public/css/jquery-ui.css"/>
        <link rel="stylesheet" href="/public/css/jquery.ui.theme.css"/>
        <link rel="stylesheet" href="/public/css/jquery-ui-timepicker-addon.css"/>
        <link rel="stylesheet" href="/public/css/main.css"/>
    </head>
    <body>
        <div id="wrap">
            <div id="overlay"></div>
            <div id="oinfo">
                Ожидание активности пользователя...
                <i class="glyphicon glyphicon-time"></i>
            </div>
            <nav class="navbar navbar-default navbar-fixed-top">
                <div class="navbar-header navbar-default">
                    <a href="/auth/select/" class="navbar-brand" title="Выбрать журнал">
                        Оперативные журналы
                    </a>
                    <button class="navbar-toggle" type="button" data-toggle="collapse" data-target="#navbar-main">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                </div>
                <div id="navbar-main" class="navbar-collapse collapse navbar-default">
                    <ul class="nav navbar-nav">
                        <li><a href="/journal/">Текущая смена</a></li>
                        <li><a href="/journal/archive/">Архив</a></li>
                        <!--<li><a href="/syncronization/">Синхронизация</a></li>-->
                        <!--<li><a id="fuck" href="/syncronization/">Блокировка</a></li>-->
                    </ul>

                    <ul class="nav navbar-top-links navbar-right">
                        <?php if ($authdata === false): ?>
                            <li><a href="/auth/"><span class="glyphicon glyphicon-log-in" aria-hidden="true"></span> Вход</a>
                            </li>
                        <?php else: ?>
                            <li><span>Вы вошли как:</span></li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                    <span class="glyphicon glyphicon-user"></span>
                                    <?= get_param($authdata, 'fio', 'n/a'); ?>
                                    <span class="caret"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-user">
                                    <li>
                                        <a href="/auth/newpassword/">
                                            <span class="glyphicon glyphicon-lock"></span>
                                            Изменить пароль
                                        </a>
                                        <a href="/auth/logout/">
                                            <span class="glyphicon glyphicon-log-out"></span>
                                            Выход
                                        </a>
                                    </li>
                                    <?php if (get_param($authdata, 'groupid') === '1'): ?>
                                    <li class="divider"></li>
                                    <li>
                                        <a href="#" class="disabled">Сервер: <strong><?= get_param($server, null, 'n/a'); ?></strong></a>
                                    </li>
                                    <?php endif;?>
                                </ul>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>
            <div class="container top-shift">
                <div class="row text-center" id="row-error">
                    <?php $this->drawError(); ?>
                </div>

