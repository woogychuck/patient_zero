/*globals PatientZero,jQuery*/
PatientZero.Search = (function Search ($, config) {
    var exports = {};

    exports.getResults = function getResults (search, until) {
        var dateLimit = until ? '_' + until.valueOf() : '';

        var promise = $.getJSON(config.baseUrl + 'data/search_results_' + search.term + dateLimit + '.json');

        return promise;
    };

    return exports;
})(jQuery, PatientZero.Config);