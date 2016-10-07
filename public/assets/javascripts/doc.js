var apidoc = {
	index:    "/assets/documents/README1.md",
    // page element ids
    content_id: "#api-content",
    sidebar_id: "#api-sidebar",

    // initialize function
    run: initialize
};

function initialize() {
    router();
    $(window).on('hashchange', router);
}

function router() { 
	var path = location.hash.replace("#", "/");
    
    if (location.pathname === "/api/doc") {
        path = location.pathname.replace("/api/doc", apidoc.index);
    } else if (path === "") {
        path = window.location + apidoc.index; 
    } else {
        path = path + ".md";
    }

    $(window).off('scroll');

    $.get(path, function(data) {
    	$(apidoc.content_id).html(marked(data));
    });

}

apidoc.run();