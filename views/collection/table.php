<?php $this->yield_start('content') ?>
<div class="row">
    <div class="col-md-3">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">篩選</h6>
            </div>
            <div class="card-body">
                <div class="dropdown mb-4">
                    <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownFilter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                        新增/取消篩選欄位
                    </button>
                    <div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownFilter" id="dropdown-filter">
                    </div>
                    <div id="filter-fields"></div>
<script id="tmpl-filter-field" type="text/html">
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary agg-name"></h6>
    </div>
    <div class="card-body">
    </div>
</div>
</script>

                    <?php foreach ($this->data->aggs as $agg_data) { ?>
                    <?php $agg = $agg_data->agg; ?>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">資料</h6>
            </div>
            <div class="card-body">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <?php foreach (TypeHelper::getColumns($this->type) as $col) { ?>
                            <th><?= $this->escape($col) ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php $this->yield_end() ?>
<?php $this->yield_start('body-load') ?>
<script>
var table_config = {
    aggs: <?= json_encode(TypeHelper::getCurrentAgg($this->type)) ?>,
    data_column: <?= json_encode(TypeHelper::getDataColumn($this->type)) ?>,
    columns: <?= json_encode(TypeHelper::getColumns($this->type)) ?>,
};

$(document).ready(function() {
    $('#dataTable').DataTable({
        serverSide: true,
        ajax: function(data, callback, settings){
            var api_url = <?= json_encode(TypeHelper::getApiUrl($this->type)) ?>;
            api_url += '?limit=' + data.length;
            api_url += '&page=' + (data.start / data.length + 1);
            if (data.search.value) {
                v = data.search.value.split(/\s+/).map(function(v){ return '"' + v + '"'; }).join(' ')
                api_url += '&q=' + encodeURIComponent(v);
            }
            for (let agg_fields of table_config.aggs) {
                api_url += '&agg=' + encodeURIComponent(agg_fields);
            }

            // check search word
            $.get(api_url, function(ret) {
                var records = ret[table_config.data_column];
                $('#dropdown-filter').empty();
                for (let supported_filter_field of ret.supported_filter_fields) {
                    var a_dom = $('<a class="dropdown-item toggle-filter" href="#"></a>');
                    a_dom.text(supported_filter_field);
                    a_dom.data('field', supported_filter_field);
                    $('#dropdown-filter').append(a_dom);
                }
                $('#filter-fields').empty();
                for (let agg_data of ret.aggs) {
                    var tmpl = $('#tmpl-filter-field').html();
                    var dom = $(tmpl);
                    dom.find('.agg-name').text(agg_data.agg);
                    $('#filter-fields').append(dom);
                    for (let bucket of agg_data.buckets) {
                        var label_dom = $('<label class="form-check"></label>');
                        label_dom.append($('<input type="checkbox" class="form-check-input" checked>'));
                        label_dom.append($('<span class="form-check-label"></span>').text(bucket[agg_data.agg]));
                        label_dom.append($('<span class="badge"></span>').text('(' + bucket.count + ')'));
                        dom.find('.card-body').append(label_dom);
                    }
                }

                data.data = [];
                data.recordsTotal = ret.total;
                data.recordsFiltered = ret.total;
                for (let record of records) {
                    var row = [];
                    for (let col of table_config.columns) {
                        row.push(record[col]);
                    }
                    data.data.push(row);
                }
                callback(data);
            }, 'json');
        }
    });
    $('.toggle-filter').click(function(e){
        e.preventDefault();
    });
});
</script>
<?php $this->yield_end() ?>

<?= $this->partial('layout/app', $this) ?>
