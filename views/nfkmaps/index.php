<?php
/* @var models\Map[] $maps */
?>
<div align="center">
    <table id="tbl" border="0" cellspacing="1">
        <thead>
            <tr>
                <th>Id</th>
                <th>Name</th>
                <th>Hash</th>
            </tr>
        </thead>
        <?foreach ($maps as $map):?>
        <tr bgcolor=#C7CCD9>
            <td><span class='newsnumcom'><?=$map->map_id?></span></td>
            <td><span class='newsautor'><?=$map->name?></span></td>
            <td><span class='newsnumcom'><?=$map->hash?></span></td>
        </tr>
        <?endforeach?>
    </table>
</div>
