<table class="table">
    <thead>
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach (TypeHelper::getRecordList($this->data->data) as $record) { ?>
    <tr>
        <td><?= $this->escape($record['key']) ?></td>
        <td><?= $this->escape($record['value']) ?></td>
    </tr>
    <?php } ?>
    </tbody>
</table>
