
<div class="row">
    <div class="col-md-12">
        <h2 class="page-header">
            <?= $jname; ?>
            <small>:: Архив</small>
        </h2>
    </div>
</div>

<div class="row">
    <div class="col-md-8 col-md-offset-2 col-lg-12 col-lg-offset-0">
        <div class="panel panel-green">
            <div class="panel-heading">
                <i class="glyphicon glyphicon-book">&nbsp;</i>
                Архив смен журнала
            </div>
            <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Дата</th>
                                <th>Смена</th>
                                <th>Вахта</th>
                                <th>ФИО</th>
                                <th></th>
                                
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($archive as $shift) {
                                    echo '<tr>' . PHP_EOL;
                                    $fio = createShortNameArray($shift);
                                    extract($shift);
                                    echo <<<ACOL
                                    <td>$dopen</td>
                                    <td>$period</td>
                                    <td>$abbr</td>
                                    <td>$fio</td>
                                    <td><a href="/dashboard/viewshift/id/$id" class="btn btn-primary btn-xs">
                                        <i class="glyphicon glyphicon-search"></i>
                                        Просмотр
                                    </a></td>
ACOL;
                                    echo '</tr>' . PHP_EOL;
                                }
                            ?>
                        </tbody>
                    </table>
                </div> 
            </div>
        </div>
    </div>
</div>