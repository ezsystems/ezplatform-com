(function (global) {
    const eZ = (global.eZ = global.eZ || {});

    /**
     * Capitalize given string
     *
     * @param string
     * @returns {string}
     */
    const capitalize = (string) => {
        return string.charAt(0).toUpperCase() + string.slice(1);
    };

    eZ.helpers = eZ.helpers || {};
    eZ.helpers.stringUtils = {
        capitalize
    };
})(window);
