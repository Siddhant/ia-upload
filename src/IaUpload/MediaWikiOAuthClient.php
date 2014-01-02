<?php

namespace IaUpload;

use Guzzle\Common\Collection;
use Guzzle\Http\Client;
use Guzzle\Http\Exception\ClientErrorResponseException;
use Guzzle\Plugin\Oauth\OauthPlugin;

/**
 * Client for the OAuth authorization
 *
 * @file
 * @ingroup IaUpload
 *
 * @licence GNU GPL v2+
 */
class MediaWikiOAuthClient extends Client {

	public static function factory( $config = array() ) {
		$required = array(
			'base_url',
			'consumer_key',
			'consumer_secret'
		);
		$config = Collection::fromConfig( $config, array(), $required );
		$config['request_method'] = OauthPlugin::REQUEST_METHOD_QUERY;

		$client = new self( $config['base_url'], $config );
		$client->addSubscriber( new OauthPlugin( $config->toArray() ) );

		return $client;
	}

	/**
	 * Get the token needed to request authorization
	 *
	 * @return array
	 * @throws ClientErrorResponseException
	 */
	public function getInitiationToken() {
		$token = $this->get( '', null, array(
			'query' => array(
				'title' => 'Special:OAuth/initiate',
				'format' => 'json',
				'oauth_callback' => 'oob'
			)
		) )->send()->json();
		if ( array_key_exists( 'error', $token ) ) {
			throw new ClientErrorResponseException( 'Error retrieving OAuth token:' . $token['error'] );
		}
		return $token;
	}

	/**
	 * Get the final token
	 *
	 * @return array
	 * @throws ClientErrorResponseException
	 */
	public function getFinalToken( $verifier ) {
		$token = $this->get( '', null, array(
			'query' => array(
				'title' => 'Special:OAuth/token',
				'format' => 'json',
				'oauth_verifier' => $verifier
			)
		) )->send()->json();
		if ( array_key_exists( 'error', $token ) ) {
			throw new ClientErrorResponseException( 'Error retrieving OAuth token:' . $token['error'] );
		}
		return $token;
	}
} 