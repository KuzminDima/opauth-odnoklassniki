<?php
/**
 * Disqus strategy for Opauth
 *
 * More information on Opauth: http://opauth.org
 *
 * @copyright Copyright (c) 2014 Kuzmin Dima
 * @link http://opauth.org
 * @license MIT License
 */

class OdnoklassnikiStrategy extends OpauthStrategy
{
    /**
     * Compulsory config keys, listed as unassociative arrays
     */
    public $expects = array('client_id');

    /**
     * Optional config keys with respective default values, listed as associative arrays
     */
    public $defaults = array(
        'redirect_uri' => '{complete_url_to_strategy}int_callback'
    );

    /**
     * Auth request
     */
    public function request() {
        $url = 'http://www.odnoklassniki.ru/oauth/authorize';
        $params = array(
            'client_id' => $this->strategy['client_id'],
            'redirect_uri' => $this->strategy['redirect_uri'],
            'response_type' => 'code',
        );

        $this->clientGet($url, $params);
    }

    /**
     * Internal callback, after Odnoklassniki OAuth
     */
    public function int_callback() {
        if (array_key_exists('code', $_GET) && ! empty($_GET['code'])){
            $url = 'http://api.odnoklassniki.ru/oauth/token.do';
            $params = array(
                'client_id' =>$this->strategy['client_id'],
                'client_secret' => $this->strategy['client_secret'],
                'redirect_uri'=> $this->strategy['redirect_uri'],
                'code' => trim($_GET['code']),
                'grant_type' => 'authorization_code'
            );

            $response = $this->serverPost($url, $params, null, $headers);
            $results = json_decode($response, true);

            if ( ! empty($results) && ! empty($results['access_token'])){
                $user = $this->getUser($results['access_token']);

                $this->auth = array(
                    'provider' => 'Odnoklassniki',
                    'uid' => $user->uid,
                    'info' => array(
                        'name' => $user->name,
                    ),
                    'credentials' => array(
                        'token' => $results['access_token'],
                        'refresh_token' => $results['refresh_token']
                    ),
                    'raw' => $user
                );

                if ( ! empty($user->birthday)) $this->auth['info']['birthday'] = $user->birthday;
                if ( ! empty($user->age)) $this->auth['info']['age'] = $user->age;
                if ( ! empty($user->first_name)) $this->auth['info']['first_name'] = $user->first_name;
                if ( ! empty($user->last_name)) $this->auth['info']['last_name'] = $user->last_name;
                if ( ! empty($user->locale)) $this->auth['info']['locale'] = $user->locale;
                if ( ! empty($user->gender)) $this->auth['info']['gender'] = $user->gender;
                if ( ! empty($user->location)) $this->auth['info']['location'] = $user->location;
                if ( ! empty($user->link)) $this->auth['info']['urls']['facebook'] = $user->link;
                if ( ! empty($user->website)) $this->auth['info']['urls']['website'] = $user->website;

                $this->callback();
            } else {
                $error = array(
                    'provider' => 'Odnoklassniki',
                    'code' => 'access_token_error',
                    'message' => 'Failed when attempting to obtain access token',
                    'raw' => $headers
                );

                $this->errorCallback($error);
            }
        } else {
            $error = array(
                'provider' => 'Odnoklassniki',
                'code' => $_GET['error'],
                'message' => $_GET['error_description'],
                'raw' => $_GET
            );

            $this->errorCallback($error);
        }
    }

    private function getUser($access_token) {
        $sig = md5("application_key={$this->strategy['client_public']}format=jsonmethod=users.getCurrentUser" . md5("{$access_token}{$this->strategy['client_secret']}"));

        $user = $this->serverGet('http://api.odnoklassniki.ru/fb.do', array(
            'method' => 'users.getCurrentUser',
            'access_token' => $access_token,
            'application_key' => $this->strategy['client_public'],
            'format' => 'json',
            'sig'=> $sig
        ));

        if ( ! empty($user)) {
            return json_decode($user);
        } else {
            $error = array(
                'code' => 'Get User error',
                'message' => 'Failed when attempting to query for user information',
                'raw' => array(
                    'access_token' => $access_token,
                )
            );
            $this->errorCallback($error);
        }
    }
}
