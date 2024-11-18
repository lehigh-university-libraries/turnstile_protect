<?php

namespace Drupal\Tests\turnstile_protect\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests redirection from /node/1 to /challenge.
 *
 * @group turnstile_protect
 */
class NoReferrerTest extends BrowserTestBase {

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
  }

  /**
   * Tests referrerpolicy is set on cloudflare <script>.
   */
  public function testReferrer() {
    $cloudFlareSrc = \Drupal::config('turnstile.settings')->get('turnstile_src');
    $this->drupalGet('/challenge');
    $page = $this->getSession()->getPage();
    $script = $page->find('css', "script[src='$cloudFlareSrc']");
    $this->assertNotNull($script, "Cloudflare script could not be found on page");
    $attribute = 'referrerpolicy';
    $this->assertTrue($script->hasAttribute($attribute) && $script->getAttribute($attribute) === 'no-referrer', "referrerpolicy not correct");
  }

}
