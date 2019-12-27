var socks = require('socksv5');

var srv = socks.createServer(function(info, accept, deny) {
  accept();
});
var port = 1080;
if (process.argv.length > 2) {
  port = parseInt(process.argv[2])
}
srv.listen(port, 'localhost', function() {
  console.log('SOCKS server listening on port ' + port);
});

srv.useAuth(socks.auth.None());

