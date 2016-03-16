<?php

$list = get_param($users, null, array());

if (!count($list)) {
    echo <<<ESET
    <tr class="warning">
        <td colspan="4">Нет данных</td>
    </tr>
ESET;
}

foreach ($list as $item) {

    $lname = $item['lname'];
    $pos = nl2br($item['pos']);
    $fname = $item['fname'];
    $pname = $item['pname'];
    echo <<<UROW
        <tr>
            <td>$lname</td>
            <td>$fname</td>
            <td>$pname</td>
            <td>$pos</td>
        </tr>
UROW;
}