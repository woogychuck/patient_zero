/*globals PatientZero,jQuery*/
PatientZero.Search = (function Search ($, config) {
    var exports = {};

    /**
     *
     * @param search
     * @param until (Optional) Date to get results up to, as a Date or raw timestamp
     * @returns {Promise}
     */
    exports.getResults = function getResults (search, until) {
        var dateLimit = until ? '_' + (until.valueOf ? until.valueOf() : until) : '';

        var promise = $.getJSON(config.baseUrl + 'data/search_results_' + search.term + dateLimit + '.json');

        return promise;
    };

    return exports;
})(jQuery, PatientZero.Config);