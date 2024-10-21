## INTRODUCTION

The Turnstile Protect module is a DESCRIBE_THE_MODULE_HERE.

The primary use case for this module is:

- Use case #1
- Use case #2
- Use case #3

```mermaid.js
flowchart TD
    Client(Client accesses path on website) --> Cookie{Has client passed turnstile before?}
    Cookie -- Yes --> Continue(Go to original destination)
    Cookie -- No --> Authenticated{Is client authenticated?}
    Authenticated -- Yes --> Continue(Go to original destination)
    Authenticated -- No --> IP_BYPASS{Is client IP whitelisted by captcha module?}
    IP_BYPASS -- Yes --> Continue(Go to original destination)
    IP_BYPASS -- No --> GOOD_BOT{Is client IP hostname in allowed bot list?}
    GOOD_BOT -- No --> PROTECTED_ROUTE(Is this route protected by Turnstile?)
    GOOD_BOT -- Yes --> CANONICAL_URL_BOT{Are there URL parameters?}
    CANONICAL_URL_BOT -- Yes --> PROTECTED_ROUTE(Is this route protected by Turnstile?)
    CANONICAL_URL_BOT -- No --> Continue(Go to original destination)
    PROTECTED_ROUTE -- Yes --> REDIRECT(Redirect to /challenge)
    PROTECTED_ROUTE -- No --> Continue(Go to original destination)
    REDIRECT --> CHALLENGE(Cloudflare turnstile challenge)
    CHALLENGE -- Pass --> Continue(Go to original destination)
    CHALLENGE -- Fail --> Stuck
```

## REQUIREMENTS

- Turnstile
- Captcha

## INSTALLATION

Install as you would normally install a contributed Drupal module.
See: https://www.drupal.org/node/895232 for further information.

## CONFIGURATION
- Configuration step #1
- Configuration step #2
- Configuration step #3

## MAINTAINERS

Current maintainers for Drupal 10:

- Joe Corall (joecorall) - https://www.drupal.org/u/joecorall
