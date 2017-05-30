(function () {
  $('.search-button').on('click', function(e) {
    var searchPhrase = $('.search-input').val();

    $.ajax({
      url: "search.php?type=search&searchPhrase=" + searchPhrase
    }).done(function(results) {
      var resultsJson = $.parseJSON(results);
      showResults(resultsJson);
    });
  });

  function showResults(results) {
    var resultsHtml = '';

    for (var i = 0; i < results.length; i++) {
      resultsHtml +=
        '<div class="result">' +
          '<a href="' + results[i].url + '">' + results[i].url + '</a>' +
          '<div class="preview">' + results[i].content + '</div>' +
        '</div>';
    }

    $('.result-section').html(resultsHtml);
    $('.info').html('Результатов поиска: ' + results.length);
  }
})();
