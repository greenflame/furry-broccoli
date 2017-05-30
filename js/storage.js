(function () {

    $('.update-button').on('click', function(e) {
        update();
    });

    $('.clear-button').on('click', function(e) {
        $.ajax({
            url: "search.php?type=clear"
        }).done(function() {
            alert('cleared');
            update();
        });
    });

    function update() {
        $.ajax({
            url: "search.php?type=storage"
        }).done(function(links) {
            links = JSON.parse(links);

            var linksHtml = '';

            for (var i = 0; i < links.length; i++) {
                linksHtml += '<li><a href="' + links[i].url + '">' + links[i].url + '</a></li>';
            }

            $('.links-section > ol').html(linksHtml);
            $('.info').html('Total documents in storage: ' + links.length);
        });
    }

    update();
})();
