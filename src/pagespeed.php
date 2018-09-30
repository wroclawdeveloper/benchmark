<?php
$key = 'AIzaSyAT1JfqvzIVX_PtgvT2-m8LMBKz8jXJVto';
// View https://developers.google.com/speed/docs/insights/v1/getting_started#before_starting to get a key
$data = json_decode(file_get_contents("https://www.googleapis.com/pagespeedonline/v4/runPagespeed?url=$url&key=$key"));
//var_dump($data);
$score = $data->ruleGroups->SPEED->score;
?>
<label for="domain"><?= $url ?></label>
<script>
// Specify your actual API key here:
var API_KEY = 'AIzaSyAT1JfqvzIVX_PtgvT2-m8LMBKz8jXJVto';
// Specify the URL you want PageSpeed results for here:
var URL_TO_GET_RESULTS_FOR = '<?= $url ?>';
</script>
<script>
var API_URL = 'https://www.googleapis.com/pagespeedonline/v4/runPagespeed?';
var CHART_API_URL = 'http://chart.apis.google.com/chart?';

// Object that will hold the callbacks that process results from the
// PageSpeed Insights API.
var callbacks = {}

// Invokes the PageSpeed Insights API. The response will contain
// JavaScript that invokes our callback with the PageSpeed results.
function runPagespeed() {
    var s = document.createElement('script');
    s.type = 'text/javascript';
    s.async = true;
    var query = [
            'url=' + URL_TO_GET_RESULTS_FOR,
            'callback=runPagespeedCallbacks',
            'key=' + API_KEY,
        ].join('&');
    s.src = API_URL + query;
    document.head.insertBefore(s, null);
}

// Our JSONP callback. Checks for errors, then invokes our callback handlers.
function runPagespeedCallbacks(result) {
    if (result.error) {
        var errors = result.error.errors;
        for (var i = 0, len = errors.length; i < len; ++i) {
            if (errors[i].reason == 'badRequest' && API_KEY == 'yourAPIKey') {
                alert('Please specify your Google API key in the API_KEY variable.');
            } else {
                // NOTE: your real production app should use a better
                // mechanism than alert() to communicate the error to the user.
                alert(errors[i].message);
            }
        }
    return;
  }

    // Dispatch to each function on the callbacks object.
    for (var fn in callbacks) {
        var f = callbacks[fn];
        if (typeof f == 'function') {
            callbacks[fn](result);
    }
  }
}

// Invoke the callback that fetches results. Async here so we're sure
// to discover any callbacks registered below, but this can be
// synchronous in your code.
setTimeout(runPagespeed, 0);
</script>

<script>
    var RESOURCE_TYPE_INFO = [
        {label: 'JavaScript', field: 'javascriptResponseBytes', color: 'e2192c'},
        {label: 'Images', field: 'imageResponseBytes', color: 'f3ed4a'},
        {label: 'CSS', field: 'cssResponseBytes', color: 'ff7008'},
        {label: 'HTML', field: 'htmlResponseBytes', color: '43c121'},
        {label: 'Flash', field: 'flashResponseBytes', color: 'f8ce44'},
        {label: 'Text', field: 'textResponseBytes', color: 'ad6bc5'},
        {label: 'Other', field: 'otherResponseBytes', color: '1051e8'},
    ];

    callbacks.displayResourceSizeBreakdown = function(result) {
        var stats = result.pageStats;
        var labels = [];
        var data = [];
        var colors = [];
        var totalBytes = 0;
        var largestSingleCategory = 0;
        for (var i = 0, len = RESOURCE_TYPE_INFO.length; i < len; ++i) {
            var label = RESOURCE_TYPE_INFO[i].label;
            var field = RESOURCE_TYPE_INFO[i].field;
            var color = RESOURCE_TYPE_INFO[i].color;
            if (field in stats) {
                var val = Number(stats[field]);
                totalBytes += val;
                if (val > largestSingleCategory) largestSingleCategory = val;
                labels.push(label);
                data.push(val);
                colors.push(color);
            }
        }
        // Construct the query to send to the Google Chart Tools.
        var query = [
            'chs=300x140',
            'cht=p3',
            'chts=' + ['000000', 16].join(','),
            'chco=' + colors.join('|'),
            'chd=t:' + data.join(','),
            'chdl=' + labels.join('|'),
            'chdls=000000,14',
            'chp=1.6',
            'chds=0,' + largestSingleCategory,
        ].join('&');
        var i = document.createElement('img');
        i.src = 'http://chart.apis.google.com/chart?' + query;
        document.body.insertBefore(i, null);
    };
</script>