(function () {
  var links = [];
  var maxLevel = 2;

  $('.indexer-button').on('click', function(e) {
    var initialLink = $('.indexer-input').val();

    links = [];
    links.push({
      url: initialLink,
      isReady: false,
      level: 0
    });

    indexLink();
  });

  function indexLink() {
    showLinks();

    var currentLink = null;

    for (var i = 0; i < links.length; i++) {
      if (!links[i].isReady) {
        currentLink = links[i];
        break;
      }
    }

    if (currentLink === null) {
      return;
    }

    $.ajax({
      url: "search.php?type=indexer&link=" + currentLink.url
    }).done(function(newLinks) {

      currentLink.isReady = true;

      var linksJson = $.parseJSON(newLinks);

      if (newLinks && linksJson.length !== 0) {
          if (currentLink.level !== maxLevel) {
              for (var i = 0; i < linksJson.length; i++) {
                  var filterContains = function(link) {
                      return link.url === linksJson[i];
                  };

                  if (links.filter(filterContains).length === 0) {
                      links.push({
                          url: linksJson[i],
                          isReady: false,
                          level: currentLink.level + 1
                      });
                  }
              }
          }
      }

      showLinks();
      indexLink();
    });
  }

  function showLinks() {
    var linksHtml = '';

    for (var i = 0; i < links.length; i++) {
      linksHtml += '<li><a href="' + links[i].url + '">' + links[i].url + '</a>' +
          (links[i].isReady ? ' [Ok]' : ' [In queue]') + '</li>';
    }

    $('.links-section > ol').html(linksHtml);

    var finished_count = links.filter(function(link) {
        return link.isReady;
    }).length;

    $('.info').html('Total documents in queue: ' + links.length + ', finished: ' + finished_count);
  }

})();
