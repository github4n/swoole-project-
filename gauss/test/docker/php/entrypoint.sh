#!/bin/sh
for f in /entrypoint.d/*; do
	case "$f" in
		*.sh)  echo "$0: running $f"; . "$f" ;;
		*.php) echo "$0: running $f"; /usr/bin/php -f "$f" ;;
		*)     echo "$0; ignoring $f" ;;
	esac
	echo
done
exec supervisord --nodaemon --configuration="/etc/supervisord.conf" --loglevel=info
