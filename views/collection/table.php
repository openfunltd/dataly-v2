<?php
$config = TypeHelper::getTypeConfig()[$this->type];
?>
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
    <div class="card-body" style="max-height: 200px; overflow-y: auto;">
    </div>
</div>
</script>

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
                            <th>連結</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php $this->yield_start('list-body-load') ?>
<script>
var table_config = {
    type: <?= json_encode($this->type) ?>,
    aggs: <?= json_encode(TypeHelper::getCurrentAgg($this->type)) ?>,
    data_column: <?= json_encode(TypeHelper::getDataColumn($this->type)) ?>,
    columns: <?= json_encode(TypeHelper::getColumns($this->type)) ?>,
    filter: <?= json_encode(TypeHelper::getCurrentFilter()) ?>,
};

$(document).ready(function() {
    $('#dropdown-filter').on('change', 'input[type="checkbox"]', function(){
        var checked = [];
        $('#dropdown-filter input[type="checkbox"]').each(function(){
            if ($(this).prop('checked')) {
                checked.push($(this).data('field'));
            }
        });
        table_config.aggs = checked;
        $('#dataTable').DataTable().draw();
    });

    $('#filter-fields').on('change', 'input[type="checkbox"]', function(){
        table_config.filter = [];
        $('#filter-fields input[type="checkbox"]').each(function(){
            if ($(this).prop('checked')) {
                table_config.filter.push([$(this).data('field'), $(this).data('value')]);
            }
        });
        $('#dataTable').DataTable().draw();
    });

    data_table_config = {
        dom: '<"top"lif>rt<"bottom"ip><"clear">',
        serverSide: true,
        ajax: function(data, callback, settings){
            var records_api_url = <?= json_encode(TypeHelper::getApiUrl($this->type)) ?>;
            // 檢查有哪些 field 有設定 filter
            var filter_fields = [];
            var filter_agg_api_urls = {};
            var filter_agg_api_reasons = {};
            for (let filter of table_config.filter) {
                filter_fields.push(filter[0]);
            }
            // 設為不重覆
            filter_fields = filter_fields.filter(function(item, pos) {
                return filter_fields.indexOf(item) == pos;
            });
            // 設定各 filter 網址
            for (let filter_field of filter_fields) {
                filter_agg_api_urls[filter_field] = records_api_url;
                filter_agg_api_urls[filter_field] += "?limit=0";
                filter_agg_api_urls[filter_field] += "&agg=" + encodeURIComponent(filter_field);
                filter_agg_api_reasons[filter_field] = "搜尋 " + table_config.type + " 條件: 筆數:0, 分群:" + filter_field;
            }

            page_params = [];
            api_terms = [];
            records_api_url += '?limit=' + data.length;
            api_terms.push("筆數:" + data.length);
            if (data.length != 10) {
                page_params.push('limit=' + data.length);
            }
            page = Math.floor(data.start / data.length) + 1;
            records_api_url += '&page=' + page;
            api_terms.push("頁數:" + page);
            if (page != 1) {
                page_params.push('page=' + page);
            }
            if (data.search.value) {
                v = data.search.value.split(/\s+/).map(function(v){ return '"' + v + '"'; }).join(' ');
                records_api_url += '&q=' + encodeURIComponent(v);
                api_terms.push("搜尋:" + v);
                page_params.push('q=' + encodeURIComponent(data.search.value));
                for (let filter_field of filter_fields) {
                    filter_agg_api_urls[filter_field] += "&q=" + encodeURIComponent(v);
                    filter_agg_api_reasons[filter_field] += "搜尋:" + v;
                }
            }
            for (let agg_fields of table_config.aggs) {
                if (filter_fields.indexOf(agg_fields) != -1) {
                    continue;
                }
                records_api_url += '&agg=' + encodeURIComponent(agg_fields);
                api_terms.push("分群:" + agg_fields);
                page_params.push('agg=' + encodeURIComponent(agg_fields));
            }
            for (let filter of table_config.filter) {
                records_api_url += '&' + encodeURIComponent(filter[0]) + '=' + encodeURIComponent(filter[1]);
                api_terms.push("篩選:" + filter[0] + ':' + filter[1]);
                page_params.push('filter=' + encodeURIComponent(filter[0] + ':' + filter[1]));

                for (let filter_field of filter_fields) {
                    if (filter_field == filter[0]) {
                        continue;
                    }
                    filter_agg_api_urls[filter_field] += '&' + encodeURIComponent(filter[0]) + '=' + encodeURIComponent(filter[1]);
                    filter_agg_api_reasons[filter_field] += "篩選:" + filter[0] + ':' + filter[1];
                }
            }
            window.history.replaceState({}, '', '?' + page_params.join('&'));

            $('#api-log').empty();
            add_log(records_api_url, "搜尋 " + table_config.type + " 條件: " + api_terms.join(', '));

            var promises = [];
            promises.push($.get(records_api_url));
            for (let filter_field in filter_agg_api_urls) {
                add_log(filter_agg_api_urls[filter_field], filter_agg_api_reasons[filter_field]);
                promises.push($.get(filter_agg_api_urls[filter_field]));
            }

            $.when.apply($, promises).done(function(){
                var rets = [];
                if (promises.length == 1) {
                    ret = arguments[0];
                } else {
                    for (let i = 0; i < promises.length; i++) {
                        rets.push(arguments[i][0]);
                    }
                    ret = rets.shift();
                }
                if ('undefined' === typeof(ret.aggs)) {
                    ret.aggs = [];
                }
                // 這是包含資料的回傳

                var agg_data = {};
                for (let agg_ret of rets) {
                    ret.aggs = ret.aggs.concat(agg_ret.aggs);
                }

                var records = ret[table_config.data_column];
                $('#dropdown-filter').empty();
                for (let supported_filter_field of ret.supported_filter_fields) {
                    var label_dom = $('<label class="form-check"></label>');
                    var input_dom = $('<input type="checkbox" class="form-check-input">');
                    input_dom.data('field', supported_filter_field);
                    if (table_config.aggs.indexOf(supported_filter_field) != -1) {
                        input_dom.prop('checked', true);
                    }
                    label_dom.append(input_dom);
                    label_dom.append($('<span class="form-check-label"></span>').text(supported_filter_field));
                    $('#dropdown-filter').append(label_dom);
                }
                $('#filter-fields').empty();
                agg_dom = {};
                for (let agg_data of ret.aggs) {
                    var tmpl = $('#tmpl-filter-field').html();
                    var dom = $(tmpl);
                    dom.find('.agg-name').text(agg_data.agg);
                    agg_dom[agg_data.agg] = dom;
                    // 如果 agg_data.agg 是 "屆" 的話，裡面改成依照 bucket[agg_data.agg] 排序
                    if (['屆', '會期'].indexOf(agg_data.agg) != -1) {
                        agg_data.buckets.sort(function(a, b){
                            return b[agg_data.agg] - a[agg_data.agg];
                        });
                    }
                    for (let bucket of agg_data.buckets) {
                        var label_dom = $('<label class="form-check"></label>');
                        var input_dom = $('<input type="checkbox" class="form-check-input">');
                        input_dom.data('field', agg_data.agg);
                        input_dom.data('value', bucket[agg_data.agg]);
                        for (let filter of table_config.filter) {
                            if (filter[0] == agg_data.agg && filter[1] == bucket[agg_data.agg]) {
                                input_dom.prop('checked', true);
                            }
                        }
                        label_dom.append(input_dom);
                        if ('undefined' !== typeof(bucket[agg_data.agg + ':str'])) {
                            label_dom.append($('<span class="form-check-label"></span>').text(bucket[agg_data.agg + ':str']));
                        } else {
                            label_dom.append($('<span class="form-check-label"></span>').text(bucket[agg_data.agg]));
                        }
                        label_dom.append($('<span class="badge"></span>').text('(' + bucket.count + ')'));
                        dom.find('.card-body').append(label_dom);
                    }
                }
                for (let agg of table_config.aggs) {
                    $('#filter-fields').append(agg_dom[agg]);
                }

                data.data = [];
                data.recordsTotal = ret.total;
                data.recordsFiltered = ret.total;
                for (let record of records) {
                    var row = [];
                    for (let col of table_config.columns) {
                        row.push(record[col] || '');
                    }
                    var id_terms = [];
                    for (let id_field of ret.id_fields) {
                        id_terms.push(record[id_field]);
                    }
                    var uri = '/collection/item/' + table_config.type + '/' + encodeURIComponent(id_terms.join('-'));
                    var a_dom = $('<a></a>').attr('href', uri);
                    a_dom.append($('<i class="fas fa-fw fa-eye"></i>'));
                    row.push($('<span></span>').append(a_dom).html());
                    data.data.push(row);
                }
                callback(data);
            }, 'json');
        }
    };

    var add_log = function(url, reason) {
        var li_dom = $($('#tmpl-api-log').html());
        $('.link', li_dom).attr('href', url).text(reason);
        $('#api-log').append(li_dom);
    };
    // handle url
    var url = new URL(window.location.href);
    var page_params = [];
    for (let key of url.searchParams.keys()) {
        if (key == 'page') {
            data_table_config.page = parseInt(url.searchParams.get(key));
        } else if (key == 'limit') {
            data_table_config.pageLength = parseInt(url.searchParams.get(key));
        } else if (key == 'q') {
            data_table_config.search = {search: url.searchParams.get(key)};
        }
    }

    $('#dataTable').DataTable(data_table_config);

    $('.toggle-filter').click(function(e){
        e.preventDefault();
    });
});
</script>
<?php $this->yield_end() ?>
