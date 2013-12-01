jQuery(function($){

    $('form').on('submit', function (e) {
        var $radioNo = $(this).find('input[type=radio][value=no]');
        if ($radioNo.is(':checked')) {
            alert('you must choose a field to be unique (typically an ID field)');
            return false;
        }
    });

    $("ul.importer-nav a").click(function(){
        $("ul.importer-nav a").removeClass("active");
        $(this).addClass("active");
        $("div.importer").hide();
        $("div."+$(this).attr("rel")).show();
        return false;
    });

    if(window.location.hash.replace('#', '') == 'multi')
    {
        $("ul.importer-nav a[rel=multilanguage]").click();
    }
});