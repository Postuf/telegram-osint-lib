const socks = require('socksv5');

// noinspection JSUnusedLocalSymbols,JSUnresolvedFunction
const srv = socks.createServer(function (info, accept, deny) {
    accept();
});
let port = 1080;
if (process.argv.length > 2) {
  port = parseInt(process.argv[2])
}
srv.listen(port, 'localhost', function() {
  console.log('SOCKS server listening on port ' + port);
});

// noinspection JSUnresolvedFunction
srv.useAuth(socks.auth.None());

