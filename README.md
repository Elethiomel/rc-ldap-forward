README
------

Sometimes, when using an MTA such as Postfix you may want to allow a user to
forward their mail directly to another address without passing through your
filters. We maintain a forwardingAddress attirbute in LDAP for users who
wish to do just that. See below for example Postfix settings

This plugin was written to allow users using Roundcube Webmail to set the
ldap attribute themselves, therefore enabling or disabling forwarding.

This plugin was developed for Roundcube 0.7.2 on Debian Squeeze using
squeeze-backports.



Example main.cf settting
------------------------

alias_maps = $alias_database,
	proxy:ldap:/etc/postfix/forward.ldap,



Example forward.ldap file 
-------------------------

# LDAP map for forwarding mail for current users,  rather than locally
# delivering and re-injecting it.
# See ldap_table(5) for details.
server_host         = ldaps://ldap.foo.bar.com
server_port         = 636
search_base         = ou=People,dc=cs,dc=tcd,dc=ie
query_filter        = (&(|(uid=%u)(primaryAlias=%u)(otherEmailAlias=%u))(forwardingAddress=*))
result_attribute    = forwardingAddress
bind_dn             = cn=bind,ou=People,dc=foo,dc=bar,dc=com
bind_pw             = xxxxxxxxx
version             = 3
tls_ca_cert_file    = /etc/certs/certificate_chain.pem
tls_require_cert    = yes

