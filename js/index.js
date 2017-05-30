(function () {
  var searchPhrase;

  $('.search-button').on('click', function(e) {
    searchPhrase = $('.search-input').val();

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
          '<div class="preview">' + findInText(results[i].content, searchPhrase.split(' ')[0]) + '</div>' +
        '</div>';
    }

    $('.result-section').html(resultsHtml);
    $('.info').html('Результатов поиска: ' + results.length);
  }

  function findInText(text, word) {
    var textArray = text.split(' ');
    var textArrayLower = text.toLowerCase().split(' ');

    var foundText = '';
    var wordPos = indexOf(textArrayLower, word.toLowerCase());

    for (var i = -8; i < 9; i++) {
      if (i === 0) {
        foundText += '<b>';
      }

      if (wordPos + i >= 0) {
        foundText += textArray[wordPos + i] + ' ';
      }

      if (i === 0) {
        foundText += '</b>';
      }
    }

    return foundText;
  }

  function indexOf(text, word) {
    for (var i = 0; i < text.length; i++) {
      if (text[i].indexOf(word) !== -1) {
        return i;
      }
    }

    return -1;
  }
})();
