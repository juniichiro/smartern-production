(function() {
    window.addEventListener('pageshow', function(event) {
        const perfEntries = window.performance.getEntriesByType("navigation");
        const isBackForward = perfEntries.length > 0 && perfEntries[0].type === "back_forward";
        
        if (event.persisted || isBackForward) {
            window.location.reload();
        }
    });
})();