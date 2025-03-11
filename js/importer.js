( function( $ ) {
  $( document ).ready( function() {
    $( '#submit' ).on( 'click', function( event ) {
      event.preventDefault();

      $.ajax({
        type: "GET",
        url: "https://anchor.fm/s/81032c4/podcast/rss",
        dataType: "xml",
        success: processData
      })
      // change label and switch class

    });

  });

})( jQuery );

var importList = [];
var epCount = 0;

function processData(data) {
  epCount = 0;
  importList = [];

  jQuery(data).find("item").each(function(index, item) {
    const title = jQuery(item).find("title").text();
    const matches = title.match(/^(\d*)\.\s(.*)/);
    var newTitle, epNumber;

    if (matches && matches.length === 3) {
      newTitle = matches[2];
      epNumber = matches[1];
    } else {
      newTitle = title;
      epNumber = 0;
    }

    var date = Date.parse(jQuery(item).find("pubDate").text());

    const json = {
      action: 'import_podcast',
      number: epNumber,
      title: newTitle,
      description: jQuery(item).find("description").text(),
      link: jQuery(item).find("link").text(),
      duration: jQuery(item).find("itunes\\:duration").text(),
      image: jQuery(item).find("itunes\\:image").attr("href"),
      source: jQuery(item).find("enclosure").attr("url"),
      pubDate: jQuery(item).find("pubDate").text(),
      date: date
    };

    importList.push(json);
  });

  importList.sort(function (o1, o2) {
    return o2.date - o1.date;
  });

  importPodcast();
}

function importPodcast() {
  if (importList.length > 0) {
    epCount++;
    var json = importList.pop();

    if (json.number === 0) {
      json.number = epCount;
    }

    jQuery( '#info' ).append( "<li>Importing " + json.title + " ...");

    jQuery.post(settings.ajaxurl, json, function (res) {
      jQuery( '#info li' ).last().remove();
      const info = jQuery( '#info' );

      if (res == "0") {
        info.append( "<li>" + json.title + " already exists.</li>");
      } else {
        info.append( "<li>Imported " + json.title + " as post " + res + ".</li>");
      }

      importPodcast();
    });
  }
}
