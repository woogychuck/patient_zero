/*globals PatientZero,jQuery*/
PatientZero.Templating = (function Templating ($) {
    var exports = {};

    exports.compile = function compile (templateId, values) {
        var template = $('#' + templateId).html();

        values = values || {};

        for(var value in values) {
            template = template.replace('{{' + value + '}}', values[value]);
        }

        // Clean up any left over tokens on the way out
        return template.replace(/\{\{.*?\}\}/g, '');
    };

    return exports;
})(jQuery);