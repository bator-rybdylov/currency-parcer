jQuery(document).ready(function() {
    var url = document.getElementById('currency_container').dataset.request_url;
    setInterval(function() {
        $.ajax({
            url: url,
            method: "POST",
            data: {},
            success: function (response) {
                if (response.currency_source_name) {
                    console.log('update');
                    // Update name of currency source
                    document.getElementById('currency_source_name').innerText = response.currency_source_name;

                    // Update currencies rates
                    $.each(response.currency_rates, function (key, value) {
                        document.getElementById('currency_code_' + key).innerText = key;
                        document.getElementById('currency_rate_' + key).innerText = value;
                    });
                }
            }
        });
    }, 10000);
});
