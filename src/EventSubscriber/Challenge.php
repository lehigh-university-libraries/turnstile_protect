<?php

namespace Drupal\turnstile_protect\EventSubscriber;

use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;


/**
 * Class Challenge.
 */
class Challenge implements EventSubscriberInterface {

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs the event subscriber.
   *
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(AccountProxyInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST] = ['protect'];

    return $events;
  }

  /**
   * Helper function to see if the given response needs handled by this rate limiter
   */
  protected function applies(Request $request): bool {
    if (isset($_COOKIE['turnstile_protect_pass'])) {
      return FALSE;
    }

    // do not challenge logged in users
    if ($this->currentUser->isAuthenticated()) {
      return FALSE;
    }

    // do not challenge IPs whitelisted by captcha module
    $clientIp = $request->getClientIp();
    if (captcha_whitelist_ip_whitelisted($clientIp)) {
      return FALSE;
    }

    // TODO: allow specifying bots
    $hostname = gethostbyaddr($clientIp);
    $resolved_ip = gethostbyname($hostname);
    if ($clientIp !== $resolved_ip) {
      return TRUE;
    }
    $parts = explode(".", $hostname);
    if (count($parts) < 2)  {
      return TRUE;
    }
    $tld = array_pop($parts);
    $hostname = array_pop($parts) . '.' . $tld;
    if (in_array($hostname, [
      "duckduckgo.com",
      "kagibot.org",
      "googleusercontent.com", "google.com", "googlebot.com",
      "linkedin.com",
      "archive.org",
      "facebook.com",
      "instagram.com",
      "twitter.com", "x.com",
      "apple.com",
    ])) {
      return count($_GET) == 0;
    }

    // TODO - turn protected routes into config
    $route_name = $request->attributes->get('_route');
    return in_array($route_name, [
      "flysystem.files",
      "flysystem.serve",
      "view.browse.main",
      "view.advanced_search.page_1",
    ]);
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
        'destination' => $request->getRequestUri()
      ],
    ])->toString();
    $response = new RedirectResponse($challenge_url);    
    $event->setResponse($response);
  }

}
