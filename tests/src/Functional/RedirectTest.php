<?php

namespace Drupal\Tests\turnstile_protect\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests redirection from /node/1 to /challenge.
 *
 * @group turnstile_protect
 */
class RedirectTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'node',
    'captcha',
    'turnstile',
    'turnstile_protect',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'claro';

  /**
   * Sets up the test environment.
   */
  protected function setUp(): void {
    parent::setUp();

    // Always pass the turnstile
    // https://developers.cloudflare.com/turnstile/troubleshooting/testing/
    $this->config('turnstile.settings')
      ->set('site_key', '1x00000000000000000000AA')
      ->set('secret_key', '1x0000000000000000000000000000000AA')
      ->save();

    $config = $this->config('turnstile_protect.settings')
      ->set('routes', ["entity.node.canonical"])
      ->set('bots', [])
      ->set('rate_limit', TRUE)
      ->set('window', 86400)
      ->set('threshold', 1)
      ->set('history_enabled', FALSE)
      ->save();

    $this->assertEquals("entity.node.canonical", $config->get('routes')[0], 'Routes configuration is set to node.entity.canonical.');
    \Drupal::service('cache.config')->deleteAll();

  }

  /**
   * Tests redirection from node to /challenge.
   */
  public function testNodeRedirect() {
    $node = $this->drupalCreateNode(['type' => 'page']);
    $nodeUrl = $node->toUrl()->toString();
    $this->drupalGet($nodeUrl);

    $url = $this->getSession()->getCurrentUrl();
    $components = parse_url($url);
    $this->assertEquals($nodeUrl, $components['path'], 'User is not redirected to /challenge.');

    $this->drupalGet($nodeUrl);
    $url = $this->getSession()->getCurrentUrl();
    $components = parse_url($url);
    $this->assertEquals('/challenge', $components['path'], 'User is redirected to /challenge.');
  }

}
