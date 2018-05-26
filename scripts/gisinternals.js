var timeout         = 500;
var closetimer		= 0;
var ddmenuitem      = 0;

var serverlist = [
{name: 'download.gisinternals.com', url: 'https://download.gisinternals.com/getcontent.php?callback=?'},
{name: 'download2.gisinternals.com', url: 'http://download.gisinternals.com/getcontent.php?callback=?'},
{name: 'download3.gisinternals.com', url: 'http://download2.gisinternals.com/sdk/GetContent.ashx?callback=?'},
{name: 'download4.gisinternals.com', url: 'http://download3.gisinternals.com/sdk/GetContent.ashx?callback=?'},
];

var currenthost = 0;

$.ajaxSetup({
     timeout: 7000
  });

// open hidden layer
function mopen(id)
{	
	// cancel close timer
	mcancelclosetime();

	// close old layer
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';

	// get new layer and show it
	ddmenuitem = document.getElementById(id);
	ddmenuitem.style.visibility = 'visible';

}
// close showed layer
function mclose()
{
	if(ddmenuitem) ddmenuitem.style.visibility = 'hidden';
}

// go close timer
function mclosetime()
{
	closetimer = window.setTimeout(mclose, timeout);
}

// cancel close timer
function mcancelclosetime()
{
	if(closetimer)
	{
		window.clearTimeout(closetimer);
		closetimer = null;
	}
}

function loadContent(params) {
    loadContent2(params, currenthost);
}

function loadContent2(params, host) {
    $('#servercontent').html("Loading content from " + serverlist[host].name + "...<img src=\"images/loader.gif\" width=\"150px\"/>");
    $.getJSON(serverlist[host].url,
      params,
      function(data) {
          $('#servercontent').html(data.html);
          currenthost = host;
          $('#currentmirror').html("loaded from: " + serverlist[host].name);
      })
      .error(function(jqXHR, textStatus, errorThrown) {
          $('#servercontent').html("<span style=\"color:red\">Could not access server content! </span><input type=\"button\" value=\"Retry\" onClick=\"window.location.reload()\">");
          ++host;
          if (host == serverlist.length)
              host = 0;

          if (host != currenthost)
              loadContent2(params, host);
      });
  }

  function parseUrl(url) {
      var a = document.createElement('a');
      a.href = url;
      return {
          source: url,
          protocol: a.protocol.replace(':', ''),
          host: a.hostname,
          port: a.port,
          query: a.search,
          params: (function() {
              var ret = {},
                seg = a.search.replace(/^\?/, '').split('&'),
                len = seg.length, i = 0, s;
              for (; i < len; i++) {
                  if (!seg[i]) { continue; }
                  s = seg[i].split('=');
                  ret[s[0]] = s[1];
              }
              return ret;
          })(),
          file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
          hash: a.hash.replace('#', ''),
          path: a.pathname.replace(/^([^\/])/, '/$1'),
          relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
          segments: a.pathname.replace(/^\//, '').split('/')
      };
  }


// close layer when click-out
document.onclick = mclose; 
