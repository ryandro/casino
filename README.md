# Casino & API Setup Method of Demo



### NGINX Proxy Pass

Below you can find nginx proxy blocks, used in reverse proxy. In case of the PPgames this is required to function, it's is adviseable to set this up on a proper server and with CLOUDFLARE. 
To break cache, google for cache busting setup/config - this may be needed if games would suddenly change (unlikely). Does not affect possible outcome/manipulation in any case. 

This does make sure that all games are updated accordingly and totally automatic, without hosting a single frontend/static file in your own server.

```
	location /static_pragmatic/ {
	    add_header 'Access-Control-Allow-Origin' '*' always;
		add_header	'Access-Control-Allow-Methods' 'GET, POST, OPTIONS, HEAD' always;
		add_header 'Access-Control-Allow-Credentials' 'true' always;
		add_header 'Access-Control-Allow-Headers' 'Accept,Accept-Encoding,Authorization,Cache-Control,Content-Type,DNT,If-Modified-Since,Keep-Alive,Origin,User-Agent,X-Mx-ReqToken,X-Requested-With' always;
	    add_header Cache-Control "public";
	    proxy_pass http://demogamesfree.pragmaticplay.net/gs2c/common/games-html5/games/vs/;
	    proxy_http_version 1.1;
	    proxy_set_header Upgrade $http_upgrade;
	    proxy_set_header Connection "upgrade";
	    gzip_static on;
	    access_log off;
	    expires 1y;
	}

	# assets, media
	location ~* (.+)\.(?:\d+)\.(js|css|png|jpg|svg|jpeg|gif|webp)$ {
	    etag off;
	    expires 1M;
	    access_log off;
	    add_header Cache-Control "public";
	    try_files $uri $1.$2;
	}

	# svg, fonts
	location ~* \.(?:svgz?|ttf|ttc|otf|eot|woff2?)$     {
		add_header Access-Control-Allow-Origin "*";
		expires 1y;
		access_log off;
	}

	location ~ /\.(?!well-known).* {
	    deny all;
	}

```

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Cubet Techno Labs](https://cubettech.com)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[Many](https://www.many.co.uk)**
- **[Webdock, Fast VPS Hosting](https://www.webdock.io/en)**
- **[DevSquad](https://devsquad.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[OP.GG](https://op.gg)**
- **[WebReinvent](https://webreinvent.com/?utm_source=laravel&utm_medium=github&utm_campaign=patreon-sponsors)**
- **[Lendio](https://lendio.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
