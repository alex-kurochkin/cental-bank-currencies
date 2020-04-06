const showMessage = function (jqXHR) {
    'use strict';

    if(jqXHR.responseJSON && jqXHR.responseJSON.message) {
        alert(jqXHR.responseJSON.message);
        return;
    }
    alert('Server error detected');
};

const Format = function() {
    'use strict';

    let formatZero = function(v) {
        return (10 > v) ? '0' + v : v;
    };

    return {
        date: function (dateString) {
            let date = new Date(dateString);
            return formatZero(date.getDate()) + '-' + formatZero(date.getMonth()) + '-' + date.getFullYear();
        }
    };
}();

const CurrenciesList = function () {
    'use strict';

    let currenciesList = $('#currencies-list');

    return {
        load: function (currencies) {

            currenciesList.empty();

            if(!currencies.length) {
                alert('No data found.');
                return;
            }

            $.each(currencies, function (index, currency) {
                CurrenciesList.loadCurrency(currency);
            });
        },
        loadCurrency: function (currency) {
            currenciesList.append(
                '<tr id="' + currency.id + '">' +
                    '<td>' + currency.id + '</td>' +
                    '<td>' + currency.valuteId + '</td>' +
                    '<td>' + currency.numCode + '</td>' +
                    '<td>' + currency.charCode + '</td>' +
                    '<td>' + currency.name + '</td>' +
                    '<td>' + currency.nominal + '</td>' +
                    '<td>' + currency.value + '</td>' +
                    '<td>' + Format.date(currency.date) + '</td>' +
                '</tr>'
            );
        }
    }
}();

const Currencies = function () {
    'use strict';

    let url = 'http://api-currencies.local/currencies/';

    return {
        load: function (params) {

            $.ajax({
                url: url + params.from + '/' + params.to,
                headers: {
                    'Authorization': 'Bearer secret-code', // At this project not used
                    'Content-Type': 'application/json'
                },
                method: 'GET',
                dataType: 'json',
                data: '',
                success: function (data) {
                    CurrenciesList.load(data.data);
                },
                error: function (jqXHR) {
                    showMessage(jqXHR);
                }
            });
        }
    };
}();

$(document).ready(function () {
    'use strict';

    $('#date-selector-form').on('submit', function () {

        let params = [];
        $.each($(this).serializeArray(), function(k, v) {
            params[v.name] = v.value;
        });

        Currencies.load(params);
    });
});
