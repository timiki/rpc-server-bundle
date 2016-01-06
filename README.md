# Timiki RPC server

## Configs

#### type (string)

Type of RPC server

#### paths (array)

List dirs for search RPC server methods in next format:

{namespace: Namespace methods, path: Path to methods dir}

## Proxy configs

#### enable (boolean)

Enable|Disable use proxy to forward requests to remote RPC server

#### type (string)

Type of remote RPC server

#### address (array)

Address of remote RPC server

#### forwardHeaders (array)

List headers to forward to remote RPC server

#### forwardCookies (array)

List cookies to forward to remote RPC server

#### forwardCookiesDomain (string)

Set cookies domain name

#### forwardIp (boolean)

Enable|Disable forward ip to remote RPC server

#### forwardLocale (boolean)

Enable|Disable forward locale to remote RPC server