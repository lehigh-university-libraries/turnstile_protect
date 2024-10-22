<?php

namespace Drupal\turnstile_protect\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Redirect protected routes to challenge page.
 */
class Challenge implements EventSubscriberInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs the event subscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AccountProxyInterface $current_user) {
    $this->configFactory = $config_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST] = ['protect'];

    return $events;
  }

  /**
   * Helper function to see if the given response needs handled by this logic.
   */
  protected function applies(Request $request): bool {
    $session = $request->getSession();
    if ($session->get('turnstile_protect_pass')) {
      return FALSE;
    }

    $config = $this->configFactory->get('turnstile_protect.settings');

    $route_name = $request->attributes->get('_route');
    if (!in_array($route_name, $config->get('routes'))) {
      return FALSE;
    }

    // Do not challenge logged in users.
    if ($this->currentUser->isAuthenticated()) {
      return FALSE;
    }

    // Do not challenge IPs whitelisted by captcha module.
    $clientIp = $request->getClientIp();
    if (captcha_whitelist_ip_whitelisted($clientIp)) {
      return FALSE;
    }

    // See if the client IP resolves to a good bot.
    $hostname = gethostbyaddr($clientIp);
    // Being sure to lookup the domain to avoid spoofing.
    $resolved_ip = gethostbyname($hostname);
    if ($clientIp !== $resolved_ip) {
      return TRUE;
    }
    $parts = explode(".", $hostname);
    if (count($parts) < 2) {
      return TRUE;
    }
    $tld = array_pop($parts);
    $hostname = array_pop($parts) . '.' . $tld;
    if (in_array($hostname, $config->get('bots'))) {
      return $config->get('protect_parameters') ? count($_GET) > 0 : FALSE;
    }

    return TRUE;
  }

  /**
   * Redirect to challenge page for protected routes.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function protect(RequestEvent $event) {
    $request = $event->getRequest();
    if (!$this->applies($request)) {
      return;
    }

    $challenge_url = Url::fromRoute('turnstile_protect.challenge', [], [
      'query' => [
        'destination' => $request->getRequestUri(),
      ],
    ])->toString();
    $response = new RedirectResponse($challenge_url);
    $event->setResponse($response);
  }

}
