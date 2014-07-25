/*globals PatientZero,jQuery*/
PatientZero.Search = (function Search ($, config) {
    var exports = {};

    exports.getResults = function getResults (search) {
        var promise = $.getJSON(config.baseUrl + 'data/search_results_' + search.term + '.json');

        return promise;
    };

    return exports;
})(jQuery, PatientZero.Config);