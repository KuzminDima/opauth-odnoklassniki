Opauth-Odnoklassniki
=============
Opauth strategy for Odnoklassniki authentication.

Based on Opauth's Facebook Oauth2 Strategy

Getting started
----------------
0. Make sure your cake installation supports UTF8

1. Install Opauth-Odnoklassniki:
   ```bash
   cd path_to_opauth/Strategy
   git clone git://github.com/dgrabla/opauth-odnoklassniki.git Odnoklassniki
   ```
2. Create Odnoklassniki application at http://vk.com/developers.php

3. Configure Opauth-Odnoklassniki strategy with `client_id`, `client_secret`, `client_public`.

4. Direct user to `http://path_to_opauth/odnoklassniki` to authenticate

Strategy configuration
----------------------

Required parameters:

```php
<?php
'Odnoklassniki' => array(
	'client_id' => 'YOUR APP ID',
	'client_secret' => 'YOUR APP SECRET',
	'client_public' => 'YOUR APP PUBLIC'
)
```

License
---------
Opauth-Odnoklassniki is MIT Licensed  
