(function () {
  var preparedLinks = [];
  var linksStatuses = [];
  var maxOrder = 2;

  $('.indexer-button').on('click', function(e) {
    preparedLinks = [];
    var initialLink = $('.indexer-input').val();
    indexLink(initialLink, 0);
  });

  $('.clear-button').on('click', function(e) {
    $.ajax({
      url: "search.php?type=clear"
    }).done(function() {
      alert('cleared');
    });
  });

  function indexLink(link, order) {
    if (order >= maxOrder || !link || link.length === 0) {
      return;
    }

    $.ajax({
      url: "search.php?type=indexer&link=" + link
    }).done(function(links) {
      var linksJson = $.parseJSON(links);

      if (linksJson === 'ai') {
        showAlreadyIndexed();
      }
      else if (links && linksJson.length > 0) {
        preparedLinks = preparedLinks.concat(linksJson);
        linksStatuses[preparedLinks.indexOf(link)] = 'Ok';
        showLinks(preparedLinks);

        for (var i = 0; i < links.length; i++) {
          if (preparedLinks.indexOf(linksJson[i]) === -1) {
            indexLink(linksJson[i], order + 1);
          }
        }
      }
    });
  }

  function showLinks(links) {
    var linksHtml = '';

    for (var i = 0; i < links.length; i++) {
      linksHtml += '<li><a href="' + links[i] + '">' + links[i] + '</a>' + linksStatuses[i] + '</li>';
    }

    $('.links-section > ol').html(linksHtml);
  }

  function showAlreadyIndexed() {
    alert('Already indexed!');
  }
})();
