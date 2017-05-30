(function () {
  var preparedLinks = [];
  var maxOrder = 2;

  $('.indexer-button').on('click', function(e) {
    preparedLinks = [];
    var initialLink = $('.indexer-input').val();
    indexLink(initialLink, 0);
  });

  function indexLink(link, order) {
    if (order >= maxOrder) {
      return;
    }

    $.ajax({
      url: "search.php?type=indexer&link=" + link
    }).done(function(links) {
      preparedLinks = preparedLinks.concat(links);
      showLinks(preparedLinks);

      for (var i = 0; i < links.length; i++) {
        indexLink(links[i], order + 1);
      }
    });
  }

  function showLinks(links) {
    var linksHtml = '';

    for (var i = 0; i < links.length; i++) {
      linksHtml += '<a href="' + links[i] + '">' + links[i] + "</a>";
    }

    $('.links-section').html(linksHtml);
  }
})();
